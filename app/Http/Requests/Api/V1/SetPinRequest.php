<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Установка/смена 4-значного ПИН-кода (PinSetupModal.tsx). Поле current_pin обязательно
 * только если у пользователя уже есть ПИН — сама корректность current_pin проверяется
 * отдельно в ProfileController::setPin() (сравнение хэша), не через правила валидации.
 */
class SetPinRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'current_pin' => [Rule::requiredIf(fn () => $this->user()?->hasPin()), 'digits:4'],
            'pin' => ['required', 'digits:4', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'current_pin.required' => 'Введите текущий ПИН-код.',
            'current_pin.digits' => 'ПИН-код должен состоять из 4 цифр.',
            'pin.required' => 'Введите новый ПИН-код.',
            'pin.digits' => 'ПИН-код должен состоять из 4 цифр.',
            'pin.confirmed' => 'ПИН-коды не совпадают.',
        ];
    }
}
