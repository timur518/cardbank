<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\AdminTelegramEvent;
use App\Enums\KycStatus;
use App\Enums\NotificationEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ForgotPasswordRequest;
use App\Http\Requests\Api\V1\LandingRegisterRequest;
use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Requests\Api\V1\RegisterRequest;
use App\Http\Requests\Api\V1\UnlockPinRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Mail\PasswordResetMail;
use App\Mail\WelcomeMail;
use App\Models\Notification;
use App\Models\PinDeviceToken;
use App\Models\User;
use App\Services\Mail\SafeMailer;
use App\Services\Telegram\AdminTelegramNotifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Логин по телефону или email + паролю. Открывает сессионную (не токен-based)
     * аутентификацию Sanctum на guard'е web — именно её кука затем используется
     * SPA-фронтендом личного кабинета для всех последующих запросов к API.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $login = (string) $request->validated('login');

        $user = str_contains($login, '@')
            ? User::where('email', $login)->first()
            : User::where('phone', $login)->first();

        if (! $user || ! Hash::check($request->validated('password'), $user->password)) {
            throw ValidationException::withMessages(['login' => 'Неверный телефон/email или пароль.'])
                ->status(401);
        }

        if ($user->is_blocked) {
            abort(403, 'Аккаунт заблокирован.');
        }

        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        $user->update(['last_login_at' => now()]);

        return (new UserResource($user->loadMissing('latestKycVerification')))->response()->setStatusCode(200);
    }

    /**
     * Регистрация нового клиента: создаёт пользователя, назначает ему роль
     * customer (это единственная точка входа для клиентских аккаунтов) и сразу
     * логинит через сессионную аутентификацию Sanctum, как и login().
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = User::create([
            'name' => trim($data['first_name'].' '.$data['last_name']),
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'middle_name' => $data['middle_name'] ?? null,
            'phone' => $data['phone'],
            'email' => $data['email'],
            'date_of_birth' => $data['date_of_birth'],
            'password' => $data['password'],
            'personal_data_consent_at' => now(),
            'referral_code' => $data['referral_code'] ?? null,
            'utm_source' => $data['utm_source'] ?? null,
            'utm_medium' => $data['utm_medium'] ?? null,
            'utm_campaign' => $data['utm_campaign'] ?? null,
            'utm_content' => $data['utm_content'] ?? null,
            'kyc_status' => KycStatus::NotStarted,
            'is_blocked' => false,
        ]);

        // Роль customer явно блокирует доступ в /admin — см. User::canAccessPanel().
        $user->assignRole('customer');

        // Отправка приветственного уведомления
        Notification::notify($user, NotificationEvent::Welcome, [], '/cards/new');
        SafeMailer::send($user->email, new WelcomeMail($user));
        AdminTelegramNotifier::notify(AdminTelegramEvent::NewRegistration, [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
        ]);

        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        return (new UserResource($user))->response()->setStatusCode(201);
    }

    /**
     * Регистрация с лендинга (блок #apply в welcome.blade.php): в отличие от register()
     * пароль клиент не вводит сам — он генерируется автоматически и отправляется письмом
     * (WelcomeMail), как и при восстановлении пароля в forgotPassword(). Сразу после регистрации
     * логинит через ту же сессионную аутентификацию Sanctum, чтобы лендинг мог сразу
     * продолжить оформление карты (POST /api/v1/orders/issue) без отдельного шага входа.
     */
    public function registerLanding(LandingRegisterRequest $request): JsonResponse
    {
        $data = $request->validated();
        $generatedPassword = Str::password(12);

        $user = User::create([
            'name' => trim($data['first_name'].' '.$data['last_name']),
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'middle_name' => $data['middle_name'] ?? null,
            'phone' => $data['phone'],
            'email' => $data['email'],
            'date_of_birth' => $data['date_of_birth'],
            'password' => $generatedPassword,
            'personal_data_consent_at' => now(),
            'referral_code' => $data['referral_code'] ?? null,
            'utm_source' => $data['utm_source'] ?? null,
            'utm_medium' => $data['utm_medium'] ?? null,
            'utm_campaign' => $data['utm_campaign'] ?? null,
            'utm_content' => $data['utm_content'] ?? null,
            'kyc_status' => KycStatus::NotStarted,
            'is_blocked' => false,
        ]);

        $user->assignRole('customer');

        Notification::notify($user, NotificationEvent::Welcome, [], '/cards/new');
        SafeMailer::send($user->email, new WelcomeMail($user, $generatedPassword));
        AdminTelegramNotifier::notify(AdminTelegramEvent::NewRegistration, [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
        ]);

        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        return (new UserResource($user))->response()->setStatusCode(201);
    }

    /**
     * Восстановление пароля: генерирует новый случайный пароль и отправляет его
     * на email пользователя (если аккаунт с таким телефоном/email существует).
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $login = (string) $request->validated('login');

        $user = str_contains($login, '@')
            ? User::where('email', $login)->first()
            : User::where('phone', $login)->first();

        if ($user) {
            $newPassword = Str::password(12);
            $user->update(['password' => $newPassword]);
            SafeMailer::send($user->email, new PasswordResetMail($user, $newPassword));

            // Отправка уведомления о смене пароля
            Notification::notify($user, NotificationEvent::PasswordResetRequested, ['email' => $user->email], '/profile');
        }

        // Одинаковый ответ независимо от того, найден аккаунт или нет.
        return response()->json(['message' => 'Новый пароль отправлен на почту']);
    }

    /**
     * Завершает текущую сессию (guard web) и инвалидирует CSRF-токен сессии. Также отзывает
     * доверие этому устройству (mojno_pin_device) — после явного выхода быстрый вход по
     * ПИН-коду без пароля больше недоступен на нём — требуется заново войти паролем и заново
     * поставить ПИН.
     */
    public function logout(Request $request): JsonResponse
    {
        $this->revokeDeviceTrust($request);

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(null, 204);
    }

    /**
     * Публичный эндпоинт — вызывается до наличия сессии, чтобы фронт решил, показывать ли после
     * истечения обычной сессии экран ввода ПИН-кода (PinUnlockPage.tsx) вместо обычной формы
     * логина — никаких данных личного кабинета не отдаёт, только признак доверия и маскированный email.
     */
    public function deviceStatus(Request $request): JsonResponse
    {
        $token = PinDeviceToken::resolve($request->cookie(PinDeviceToken::COOKIE_NAME));
        $user = $token?->user;

        if (! $token || ! $user || $user->is_blocked || ! $user->hasPin()) {
            return response()->json(['trusted' => false]);
        }

        return response()->json([
            'trusted' => true,
            'masked_email' => self::maskEmail($user->email),
        ]);
    }

    /**
     * Разблокировка на доверенном устройстве — взамен обычной формы входа, когда deviceStatus()
     * вернул trusted=true. При успехе создаёт обычную полноценную Sanctum-сессию (как login())
     * и ротирует куку mojno_pin_device (sliding-продление). При неверном ПИНе — ошибка
     * 422; после PinDeviceToken::MAX_FAILED_ATTEMPTS подряд доверие отзывается (401,
     * фронт должен перевести на обычный /login).
     */
    public function unlockPin(UnlockPinRequest $request): JsonResponse
    {
        $token = PinDeviceToken::resolve($request->cookie(PinDeviceToken::COOKIE_NAME));
        $user = $token?->user;

        if (! $token || ! $user || ! $user->hasPin()) {
            Cookie::queue(PinDeviceToken::forgetCookie());
            abort(401, 'Сессия устройства истекла. Войдите с паролем.');
        }

        if ($user->is_blocked) {
            abort(403, 'Аккаунт заблокирован.');
        }

        if (! Hash::check($request->validated('pin'), $user->pin_hash)) {
            if ($token->registerFailedAttempt()) {
                Cookie::queue(PinDeviceToken::forgetCookie());
                abort(401, 'Слишком много неверных попыток. Войдите с паролем.');
            }

            throw ValidationException::withMessages(['pin' => 'Неверный ПИН-код']);
        }

        Auth::guard('web')->login($user);
        $request->session()->regenerate();
        $user->update(['last_login_at' => now()]);

        Cookie::queue(PinDeviceToken::makeCookie($token->rotate()));

        return (new UserResource($user->loadMissing('latestKycVerification')))->response();
    }

    /**
     * «Это не я» / «Войти по паролю» на экране ввода ПИН-кода (PinUnlockPage.tsx) — публичный
     * эндпоинт (сессии ещё нет), позволяющий выйти из экрана блокировки на обычный /login,
     * если ПИН забыт или нужен вход под другим аккаунтом на этом же устройстве.
     */
    public function forgetDevice(Request $request): JsonResponse
    {
        $this->revokeDeviceTrust($request);

        return response()->json(null, 204);
    }

    private function revokeDeviceTrust(Request $request): void
    {
        PinDeviceToken::resolve($request->cookie(PinDeviceToken::COOKIE_NAME))?->delete();
        Cookie::queue(PinDeviceToken::forgetCookie());
    }

    private static function maskEmail(string $email): string
    {
        [$name, $domain] = array_pad(explode('@', $email, 2), 2, '');
        $visible = mb_substr($name, 0, 1);

        return $visible.str_repeat('*', max(mb_strlen($name) - 1, 1)).'@'.$domain;
    }
}
