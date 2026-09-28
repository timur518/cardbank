<?php

namespace App\Http\Requests\Api\V1;

use App\Support\PhoneNumber;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Throwable;

/**
 * Регистрация с лендинга (блок #apply) — в отличие от RegisterRequest (страница
 * /register в ЛК) не запрашивает пароль: аккаунт создаётся с автосгенерированным
 * паролем, который клиент получает письмом, см. AuthController::registerLanding().
 */
class LandingRegisterRequest extends FormRequest
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
            'personal_data_consent' => ['required', 'accepted'],
            'utm_source' => ['nullable', 'string', 'max:255'],
            'utm_medium' => ['nullable', 'string', 'max:255'],
            'utm_campaign' => ['nullable', 'string', 'max:255'],
            'utm_content' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Русские сообщения об ошибках валидации — как и у RegisterRequest.
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
            'personal_data_consent.required' => 'Нужно дать согласие на обработку персональных данных.',
            'personal_data_consent.accepted' => 'Нужно дать согласие на обработку персональных данных.',
        ];
    }
}
