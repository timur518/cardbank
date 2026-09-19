<?php

namespace App\Http\Requests\Api\V1;

use App\Support\PhoneNumber;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Throwable;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $merge = [
            'phone' => PhoneNumber::normalize((string) $this->input('phone')),
        ];

        $dateOfBirth = (string) $this->input('date_of_birth');

        try {
            $merge['date_of_birth'] = Carbon::createFromFormat('d.m.Y', $dateOfBirth)->format('Y-m-d');
        } catch (Throwable) {
            // Оставляем как есть — правило date_format ниже вернёт 422.
        }

        $this->merge($merge);
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['required', 'string', 'unique:users,phone'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'date_of_birth' => ['required', 'date_format:Y-m-d', 'before:today'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'personal_data_consent' => ['required', 'accepted'],
            'referral_code' => ['nullable', 'string', 'max:255'],
            'utm_source' => ['nullable', 'string', 'max:255'],
            'utm_medium' => ['nullable', 'string', 'max:255'],
            'utm_campaign' => ['nullable', 'string', 'max:255'],
            'utm_content' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Русские сообщения об ошибках валидации — по умолчанию Laravel отдаёт их на
     * английском (APP_LOCALE=en, ru-перевод не публиковался), а весь остальной UI ЛК
     * на русском (см. resources/cabinet).
     */
    public function messages(): array
    {
        return [
            'first_name.required' => 'Введите имя.',
            'last_name.required' => 'Введите фамилию.',
            'phone.required' => 'Введите номер телефона.',
            'phone.unique' => 'Этот номер телефона уже зарегистрирован.',
            'email.required' => 'Введите email.',
            'email.email' => 'Введите корректный email.',
            'email.unique' => 'Этот email уже зарегистрирован.',
            'date_of_birth.required' => 'Введите дату рождения.',
            'date_of_birth.date_format' => 'Некорректная дата рождения. Формат: дд.мм.гггг.',
            'date_of_birth.before' => 'Дата рождения должна быть раньше сегодняшнего дня.',
            'password.required' => 'Введите пароль.',
            'password.confirmed' => 'Пароли не совпадают.',
            'password.min' => 'Пароль слишком короткий.',
            'password.letters' => 'Пароль должен содержать буквы.',
            'password.mixed' => 'Пароль должен содержать заглавные и строчные буквы.',
            'password.numbers' => 'Пароль должен содержать цифры.',
            'password.symbols' => 'Пароль должен содержать спецсимволы.',
            'password.uncompromised' => 'Этот пароль слишком распространён — используйте другой.',
            'personal_data_consent.required' => 'Нужно дать согласие на обработку персональных данных.',
            'personal_data_consent.accepted' => 'Нужно дать согласие на обработку персональных данных.',
        ];
    }
}
