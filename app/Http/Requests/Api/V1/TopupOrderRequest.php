<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\ActiveStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TopupOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'card_id' => ['required', 'string', 'exists:cards,uuid'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'currency' => ['required', Rule::in(['USD', 'RUB'])],
            'payment_method_id' => [
                'required',
                'integer',
                Rule::exists('payment_methods', 'id')->where('status', ActiveStatus::Active->value),
            ],
            'idempotency_key' => ['nullable', 'string', 'max:255'],
        ];
    }
}
