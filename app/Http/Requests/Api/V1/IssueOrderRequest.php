<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\ActiveStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IssueOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Защита от оплаты в обход списка на фронте (подстановка `payment_method_id` напрямую) —
     * та же логика фильтрации, что и в PaymentMethodController::index() — см.
     * BITBANKER_INTEGRATION_PLAN.md раздел 6.5.
     */
    protected function allowedToUser(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail) {
            $user = $this->user();

            if ($user && $user->allowedPaymentMethods()->exists() && ! $user->allowedPaymentMethods()->where('payment_methods.id', $value)->exists()) {
                $fail('Этот способ оплаты вам недоступен.');
            }
        };
    }

    public function rules(): array
    {
        return [
            'card_product_id' => [
                'required',
                'integer',
                Rule::exists('card_products', 'id')->where('active', true),
            ],
            'topup_amount' => ['required', 'numeric', 'min:0.01'],
            'topup_currency' => ['required', Rule::in(['USD', 'RUB'])],
            'payment_method_id' => [
                'required',
                'integer',
                Rule::exists('payment_methods', 'id')->where('status', ActiveStatus::Active->value),
                $this->allowedToUser(),
            ],
            'idempotency_key' => ['nullable', 'string', 'max:255'],
        ];
    }
}
