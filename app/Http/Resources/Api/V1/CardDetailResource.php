<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;

/**
 * Расширяет CardResource полями, нужными только на странице одной карты
 * (стоимость выпуска и платёжный адрес, привязанный к карте при выпуске —
 * см. OrderController::issue()). В списке карт (CardResource::collection())
 * они не нужны и не отдаются, чтобы не раздувать ответ.
 */
class CardDetailResource extends CardResource
{
    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request), [
            'price_rub' => number_format((float) $this->price_rub, 2, '.', ''),
            'billing_address' => [
                'country' => $this->billing_country,
                'city' => $this->billing_city,
                'region' => $this->billing_region,
                'address' => $this->billing_address,
                'post_code' => $this->billing_post_code,
            ],
        ]);
    }
}
