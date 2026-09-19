<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\KycStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ForgotPasswordRequest;
use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Requests\Api\V1\RegisterRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use App\Notifications\NewPasswordNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * POST /api/v1/auth/login — см. CABINET_API_SPEC.md, п. 1.
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

        return (new UserResource($user))->response()->setStatusCode(200);
    }

    /**
     * POST /api/v1/auth/register — см. CABINET_API_SPEC.md, п. 2.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = User::create([
            'name' => trim($data['first_name'] . ' ' . $data['last_name']),
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

        // Единственная точка входа для клиентских аккаунтов — см. CABINET_API_SPEC.md, п. 2.
        $user->assignRole('customer');

        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        return (new UserResource($user))->response()->setStatusCode(201);
    }

    /**
     * POST /api/v1/auth/password/forgot — см. CABINET_API_SPEC.md, п. 5.
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
            $user->notify(new NewPasswordNotification($newPassword));
        }

        // Одинаковый ответ независимо от того, найден аккаунт или нет.
        return response()->json(['message' => 'Новый пароль отправлен на почту']);
    }

    /**
     * POST /api/v1/auth/logout — см. CABINET_API_SPEC.md, п. 6.
     */
    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(null, 204);
    }
}
