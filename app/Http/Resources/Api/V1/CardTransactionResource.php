<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\CardTransaction
 *
 * cost_amount/commission_amount намеренно не отдаются — внутренние P&L-поля,
 * см. CABINET_API_SPEC.md, п. 17.
 */
class CardTransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'card_id' => $this->card_id,
            'type' => $this->type->value,
            'amount' => number_format((float) $this->amount, 2, '.', ''),
            'currency' => $this->currency,
            'merchant' => $this->merchant,
            'status' => $this->status->value,
            'occurred_at' => optional($this->occurred_at)->toIso8601String(),
        ];
    }
}
