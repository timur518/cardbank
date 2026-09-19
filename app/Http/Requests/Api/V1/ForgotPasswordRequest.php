<?php

namespace App\Http\Requests\Api\V1;

use App\Support\PhoneNumber;
use Illuminate\Foundation\Http\FormRequest;

class ForgotPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $login = (string) $this->input('login');

        if (! str_contains($login, '@')) {
            $this->merge(['login' => PhoneNumber::normalize($login)]);
        }
    }

    public function messages(): array
    {
        return [
            'login.required' => 'Введите телефон или email.',
        ];
    }

    public function rules(): array
    {
        return [
            'login' => ['required', 'string'],
        ];
    }
}
