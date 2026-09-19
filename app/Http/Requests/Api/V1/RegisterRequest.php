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
}
