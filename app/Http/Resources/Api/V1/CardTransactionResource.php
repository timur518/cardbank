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
            // Краткая карточка карты, по которой прошла операция — нужна в попапе детализации на
            // сводной странице «Операции» (там одной лентой смешаны транзакции разных карт клиента).
            'card' => [
                'id' => $this->card->uuid,
                'last4' => $this->card->card_last4,
                'product_name' => $this->card->cardProduct->name,
            ],
            'type' => $this->type->value,
            'amount' => number_format((float) $this->amount, 2, '.', ''),
            // Только для type=decline и только если CardsPro прислал комиссию в payload.fee (CardsProWebhookHandler::handleTransaction())
            // — иначе null (отклонённая операция без комиссии не трогает счётчик «Потрачено» вообще).
            'decline_fee' => $this->type === CardTransactionType::Decline && $this->commission_amount !== null
                ? number_format((float) $this->commission_amount, 2, '.', '')
                : null,
            // Комиссия провайдера за операцию — реальная часть `amount`, списанная с карты сверх
            // стоимости у эмитента (см. докблок модели). Показывается только там, где действительно
            // уменьшает баланс карты (purchase/decline) — для topup комиссия провайдера уже учтена и
            // показана клиенту отдельно на шаге оплаты (см. OrderController::convertTopup()), здесь
            // её повторный показ только запутает (деньги за неё с карты не списываются).
            'commission_amount' => in_array($this->type, [CardTransactionType::Purchase, CardTransactionType::Decline], true)
                && $this->commission_amount !== null
                ? number_format((float) $this->commission_amount, 2, '.', '')
                : null,
            // Причина отказа от провайдера (например, недостаточно средств, заблокирован мерчант) —
            // только для status=declined, иначе null.
            'decline_reason' => $this->decline_reason,
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
