<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Живой предрасчёт суммы к оплате (комиссия провайдера за пополнение уже внутри) —
 * без создания заказа, для отображения итога в ЛК по мере ввода суммы
 * (см. OrderController::quote()). Ровно один из `card_id`/`card_product_id`:
 * `card_id` — пополнение уже выпущенной карты (TopupModal), `card_product_id` —
 * первое пополнение при выпуске новой карты (NewCardOrderPage).
 */
class TopupQuoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'card_id' => ['required_without:card_product_id', 'nullable', 'string', 'exists:cards,uuid'],
            'card_product_id' => [
                'required_without:card_id',
                'nullable',
                'integer',
                Rule::exists('card_products', 'id')->where('active', true),
            ],
            'amount' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', Rule::in(['USD', 'RUB'])],
        ];
    }
}
