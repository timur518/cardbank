<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\CardTransaction
 *
 * cost_amount/commission_amount намеренно не отдаются клиенту — это внутренние
 * поля себестоимости/комиссии для внутреннего P&L, а не для личного кабинета.
 */
class CardTransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            // Клиенту отдаём uuid транзакции и карты, а не сквозные id в базе — аналогично CardResource/UserResource.
            'id' => $this->uuid,
            'card_id' => $this->card->uuid,
            'type' => $this->type->value,
            'amount' => number_format((float) $this->amount, 2, '.', ''),
            'currency' => $this->currency,
            // Сырое описание операции от провайдера как есть (например "AUGMENT CODE
            // PALO ALTO USA") — используется в ЛК как запасной вариант, если мерчант
            // не найден в нашем справочнике (merchant_info ниже — null).
            'merchant' => $this->merchant,
            // Мерчант, определённый автоматически по 'merchant' через Merchant::matchByDescription()
            // (см. CardTransaction::upsertFromProvider()) — null, если совпадение не найдено.
            'merchant_info' => $this->merchantRecord ? [
                'id' => $this->merchantRecord->id,
                'name' => $this->merchantRecord->name,
                'category' => $this->merchantRecord->category->value,
                'logo_svg' => $this->merchantRecord->logo_svg,
                'color' => $this->merchantRecord->color,
            ] : null,
            'status' => $this->status->value,
            'occurred_at' => optional($this->occurred_at)->toIso8601String(),
        ];
    }
}
