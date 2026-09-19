<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdatePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ];
    }

    /**
     * Русские сообщения об ошибках валидации — по той же причине, что и в RegisterRequest.
     */
    public function messages(): array
    {
        return [
            'current_password.required' => 'Введите текущий пароль.',
            'password.required' => 'Введите новый пароль.',
            'password.confirmed' => 'Пароли не совпадают.',
            'password.min' => 'Пароль слишком короткий.',
            'password.letters' => 'Пароль должен содержать буквы.',
            'password.mixed' => 'Пароль должен содержать заглавные и строчные буквы.',
            'password.numbers' => 'Пароль должен содержать цифры.',
            'password.symbols' => 'Пароль должен содержать спецсимволы.',
            'password.uncompromised' => 'Этот пароль слишком распространён — используйте другой.',
        ];
    }
}
