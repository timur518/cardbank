<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\NotificationEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UpdatePasswordRequest;
use App\Http\Requests\Api\V1\UpdateProfileRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\Notification;
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
        return (new UserResource($request->user()))->response();
    }

    /**
     * Обновляет редактируемые клиентом поля профиля (ФИО, телефон, дата рождения);
     * email и пароль через этот эндпоинт не меняются.
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $user->update($request->safe()->only(['first_name', 'last_name', 'middle_name', 'phone', 'date_of_birth']));

        return (new UserResource($user))->response();
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

        //Отправка уведомления о смене пароля
        Notification::notify($user, NotificationEvent::PasswordChanged, ['datetime' => now()->format('d.m.Y H:i')], '/profile');

        return response()->json(['message' => 'Пароль изменён']);
    }
}
