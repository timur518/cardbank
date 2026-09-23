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
            // Клиенту отдаём uuid, а не сквозной cards.id — чтобы не раскрывать номер карты в базе через URL ЛК (аналогично UserResource).
            'id' => $this->uuid,
            'card_product' => [
                'key' => $this->cardProduct->key,
                'name' => $this->cardProduct->name,
                'skin' => $this->cardProduct->skin ? Storage::disk('public')->url($this->cardProduct->skin) : null,
                'network' => $this->cardProduct->network?->value,
                'card_country' => $this->cardProduct->card_country?->value,
                'card_country_label' => $this->cardProduct->card_country?->getLabel(),
                'card_country_flag' => $this->cardProduct->card_country?->flag(),
            ],
            'status' => $this->status->value,
            'currency' => $this->currency,
            'balance' => number_format((float) $this->balance, 2, '.', ''),
            // Полный номер и CVV клиенту в личном кабинете не показываются — только
            // последние 4 цифры для идентификации.
            'card_last4' => $this->card_last4,
            'expiry' => $this->expiry,
            'issued_at' => optional($this->issued_at)->toIso8601String(),
            // Платёжный адрес (AVS), скопированный с CardProduct при выпуске — нужен в списке карт
            // на странице «Мои карты» (краткая строка адреса на карточке карты).
            'billing_address' => [
                'country' => $this->billing_country,
                'city' => $this->billing_city,
                'region' => $this->billing_region,
                'address' => $this->billing_address,
                'post_code' => $this->billing_post_code,
            ],
        ];
    }
}
