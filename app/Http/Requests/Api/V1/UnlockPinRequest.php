<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Разблокировка ЛК по ПИН-коду на доверенном устройстве (PinUnlockPage.tsx) — см.
 * AuthController::unlockPin(). Публичный запрос (без auth:sanctum): личность устройства
 * подтверждается httpOnly-кукой mojno_pin_device, не самим запросом.
 */
class UnlockPinRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pin' => ['required', 'digits:4'],
        ];
    }

    public function messages(): array
    {
        return [
            'pin.required' => 'Введите ПИН-код.',
            'pin.digits' => 'ПИН-код должен состоять из 4 цифр.',
        ];
    }
}
