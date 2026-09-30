<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\NotificationEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\SetPinRequest;
use App\Http\Requests\Api\V1\UpdatePasswordRequest;
use App\Http\Requests\Api\V1\UpdateProfileRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Mail\PasswordChangedMail;
use App\Models\Notification;
use App\Services\Mail\SafeMailer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    /**
     * Отдаёт профиль текущего авторизованного клиента.
     */
    public function show(Request $request): JsonResponse
    {
        return (new UserResource($request->user()->loadMissing('latestKycVerification')))->response();
    }

    /**
     * Обновляет редактируемые клиентом поля профиля (ФИО, телефон, дата рождения);
     * email и пароль через этот эндпоинт не меняются.
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $user->update($request->safe()->only(['first_name', 'last_name', 'middle_name', 'phone', 'date_of_birth']));

        return (new UserResource($user->loadMissing('latestKycVerification')))->response();
    }

    /**
     * Смена пароля клиентом в личном кабинете — требует подтверждения текущим паролем,
     * в отличие от восстановления через email (AuthController::forgotPassword).
     */
    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        $user = $request->user();

        if (! Hash::check($request->validated('current_password'), $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'Текущий пароль указан неверно.',
            ]);
        }

        $user->update(['password' => $request->validated('password')]);

        // Отправка уведомления о смене пароля
        $changedAt = now();
        Notification::notify($user, NotificationEvent::PasswordChanged, ['datetime' => $changedAt->format('d.m.Y H:i')], '/profile');
        SafeMailer::send($user->email, new PasswordChangedMail($user, $changedAt));

        return response()->json(['message' => 'Пароль изменён']);
    }

    /**
     * Отмечает, что клиент установил ЛК как PWA (на рабочий стол/экран «Домой») — вызывается
     * с фронта при событии appinstalled и при каждом запуске в режиме standalone (см. PwaInstallPrompt.tsx) —
     * идемпотентно, повторные вызовы ничего не ломают.
     */
    public function markPwaInstalled(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->pwa_installed) {
            $user->update(['pwa_installed' => true]);
        }

        return (new UserResource($user->loadMissing('latestKycVerification')))->response();
    }

    /**
     * Устанавливает/меняет 4-значный ПИН-код для быстрого входа в ЛК (PinSetupModal.tsx) — если ПИН
     * уже был установлен ранее, требуется подтверждение текущим (current_pin).
     */
    public function setPin(SetPinRequest $request): JsonResponse
    {
        $user = $request->user();

        if ($user->hasPin() && ! Hash::check($request->validated('current_pin'), $user->pin_hash)) {
            throw ValidationException::withMessages([
                'current_pin' => 'Текущий ПИН-код указан неверно.',
            ]);
        }

        $user->update([
            'pin_hash' => Hash::make($request->validated('pin')),
            'pin_set_at' => now(),
        ]);

        return (new UserResource($user))->response();
    }
}
