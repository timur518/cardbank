<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;

/**
 * Расширяет CardResource полями, нужными только на странице одной карты — стоимостью
 * выпуска и лимитами пополнения продукта. Платёжный адрес отдаётся уже в базовом
 * CardResource — он нужен и в списке карт (краткая строка адреса на карточке карты на
 * странице «Мои карты»).
 */
class CardDetailResource extends CardResource
{
    public function toArray(Request $request): array
    {
        $parent = parent::toArray($request);

        // Лимиты пополнения, комиссии и текстовые условия продукта (вкладка «Лимиты» на странице
        // карты) — только здесь, в списке карт (CardResource) они не нужны.
        $parent['card_product']['topup_min_amount'] = number_format((float) $this->cardProduct->topup_min_amount, 2, '.', '');
        $parent['card_product']['topup_max_amount'] = number_format((float) $this->cardProduct->topup_max_amount, 2, '.', '');
        $parent['card_product']['successful_payment_fee_usd'] = $this->cardProduct->successful_payment_fee_usd !== null
            ? number_format((float) $this->cardProduct->successful_payment_fee_usd, 2, '.', '')
            : null;
        $parent['card_product']['decline_fee_usd'] = $this->cardProduct->decline_fee_usd !== null
            ? number_format((float) $this->cardProduct->decline_fee_usd, 2, '.', '')
            : null;
        $parent['card_product']['non_usd_payment_fee'] = $this->cardProduct->non_usd_payment_fee;
        $parent['card_product']['risk_operation_fee_usd'] = $this->cardProduct->risk_operation_fee_usd !== null
            ? number_format((float) $this->cardProduct->risk_operation_fee_usd, 2, '.', '')
            : null;
        $parent['card_product']['restricted_merchants'] = $this->cardProduct->restricted_merchants;
        $parent['card_product']['full_terms'] = $this->cardProduct->full_terms;

        return array_merge($parent, [
            'price_rub' => number_format((float) $this->price_rub, 2, '.', ''),
        ]);
    }
}
