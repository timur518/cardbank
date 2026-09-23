<?php

namespace App\Http\Resources\Api\V1;

use App\Enums\CardTransactionType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\CardTransaction
 *
 * cost_amount намеренно не отдаётся клиенту — это внутреннее поле себестоимости
 * для внутреннего P&L. commission_amount тоже внутреннее понятие, но для
 * type=decline оно и есть реальная комиссия за отклонённую операцию (единственная часть
 * `amount`, которая реально списывается с карты — сама покупка отклонена и денег не списывает),
 * поэтому отдаётся отдельно как `decline_fee` — Счётчик «Потрачено в этом месяце»
 * (utils/format.ts sumSuccessfulPurchases()) для decline-записей считает именно его, а не `amount`.
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
            // Только для type=decline и только если CardsPro прислал комиссию в payload.fee (CardsProWebhookHandler::handleTransaction())
            // — иначе null (отклонённая операция без комиссии не трогает счётчик «Потрачено» вообще).
            'decline_fee' => $this->type === CardTransactionType::Decline && $this->commission_amount !== null
                ? number_format((float) $this->commission_amount, 2, '.', '')
                : null,
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
