<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\PaymentMethod
 */
class PaymentMethodResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type->value,
            // Чтобы фронт мог надёжно отличить BitBanker среди пришедших способов
            // оплаты (бейдж «Без комиссии!» и первое место в списке), не полагаясь
            // на строку в `name`, как сейчас сделано для СБП в PaymentMethodOption.tsx (isSbp()).
            'gateway_code' => $this->gateway_code?->value,
            'currency' => $this->currency,
            'min_amount' => number_format((float) $this->min_amount, 2, '.', ''),
            'max_amount' => number_format((float) $this->max_amount, 2, '.', ''),
        ];
    }
}
