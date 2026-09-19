<?php

namespace App\Http\Requests\Api\V1;

use App\Support\PhoneNumber;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Throwable;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $merge = [];

        if ($this->has('phone')) {
            $merge['phone'] = PhoneNumber::normalize((string) $this->input('phone'));
        }

        if ($this->has('date_of_birth')) {
            try {
                $merge['date_of_birth'] = Carbon::createFromFormat('d.m.Y', (string) $this->input('date_of_birth'))
                    ->format('Y-m-d');
            } catch (Throwable) {
                // Оставляем как есть — правило date_format ниже вернёт 422.
            }
        }

        if ($merge !== []) {
            $this->merge($merge);
        }
    }

    public function rules(): array
    {
        return [
            'first_name' => ['sometimes', 'string', 'max:255'],
            'last_name' => ['sometimes', 'string', 'max:255'],
            'middle_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'phone' => ['sometimes', 'string', Rule::unique('users', 'phone')->ignore($this->user()->id)],
            'date_of_birth' => ['sometimes', 'date_format:Y-m-d', 'before:today'],
            'email' => ['prohibited'],
        ];
    }
}
