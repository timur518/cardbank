<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @mixin \App\Models\CardProduct
 */
class CardProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'key' => $this->key,
            'name' => $this->name,
            'description' => $this->description,
            // Относительный путь на диске public -> полный URL, см. CABINET_API_SPEC.md, п. 11.
            'skin' => $this->skin ? Storage::disk('public')->url($this->skin) : null,
            'currency' => $this->currency,
            'price_rub' => number_format((float) $this->price_rub, 2, '.', ''),
            'provider_kyc_required' => (bool) $this->provider_kyc_required,
            'apple_pay_enabled' => (bool) $this->apple_pay_enabled,
            'google_pay_enabled' => (bool) $this->google_pay_enabled,
            'issue_min_amount' => $this->formatAmount($this->issue_min_amount),
            'issue_max_amount' => $this->formatAmount($this->issue_max_amount),
            'topup_min_amount' => $this->formatAmount($this->topup_min_amount),
            'topup_max_amount' => $this->formatAmount($this->topup_max_amount),
            'coming_soon' => (bool) $this->coming_soon,
        ];
    }

    private function formatAmount(mixed $amount): ?string
    {
        return $amount !== null ? number_format((float) $amount, 2, '.', '') : null;
    }
}
