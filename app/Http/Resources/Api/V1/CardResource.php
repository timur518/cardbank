<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @mixin \App\Models\Card
 */
class CardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'card_product' => [
                'key' => $this->cardProduct->key,
                'name' => $this->cardProduct->name,
                'skin' => $this->cardProduct->skin ? Storage::disk('public')->url($this->cardProduct->skin) : null,
            ],
            'status' => $this->status->value,
            'currency' => $this->currency,
            'balance' => number_format((float) $this->balance, 2, '.', ''),
            // Полный номер/CVV скрыты на модели, см. CABINET_API_SPEC.md, п. 15.
            'card_last4' => $this->card_last4,
            'expiry' => $this->expiry,
            'issued_at' => optional($this->issued_at)->toIso8601String(),
        ];
    }
}
