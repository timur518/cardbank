<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Проверка текущего ПИН-кода без его смены — используется на первом шаге попапа смены ПИН-кода
 * (PinSetupModal.tsx, mode="change"), чтобы сообщить об ошибке сразу, а не в самом конце флоу.
 */
class VerifyPinRequest extends FormRequest
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
