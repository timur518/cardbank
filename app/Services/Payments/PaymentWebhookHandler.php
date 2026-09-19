<?php

namespace App\Services\Payments;

use App\Enums\CardStatus;
use App\Enums\IncomePaymentStatus;
use App\Enums\IncomeType;
use App\Models\Card;
use App\Models\CardStatusHistory;
use App\Models\Income;
use App\Services\Integrations\CardsPro\CardsProOrderProcessor;

/**
 * Реализует шаг 3 из CARD_ORDER_AND_ISSUANCE_FLOW.md: вебхук платёжной системы
 * подтверждает (или отклоняет) оплату заказа (`Income`, найден по
 * `payment_transaction_id`), и по успешной оплате передаёт заказ дальше — в шаг 4
 * ({@see CardsProOrderProcessor}).
 *
 * Идемпотентно: если `Income` уже не в статусе `Pending` (повторная доставка того же
 * вебхука или запоздавший дубль), событие тихо игнорируется.
 */
class PaymentWebhookHandler
{
    public function __construct(protected CardsProOrderProcessor $orderProcessor)
    {
        //
    }

    /**
     * @param  array{transaction_id: string, status: 'paid'|'failed'|'unknown'}  $event
     */
    public function handle(array $event): void
    {
        if ($event['transaction_id'] === '' || $event['status'] === 'unknown') {
            return;
        }

        $income = Income::where('payment_transaction_id', $event['transaction_id'])->first();

        if (! $income || $income->payment_status !== IncomePaymentStatus::Pending) {
            return;
        }

        match ($event['status']) {
            'paid' => $this->handlePaid($income),
            'failed' => $this->handleFailed($income),
        };
    }

    protected function handlePaid(Income $income): void
    {
        $income->update(['payment_status' => IncomePaymentStatus::Paid]);

        $card = $income->card;

        if (! $card) {
            return;
        }

        $topupUsd = (float) $income->topup_usd;

        match ($income->type) {
            IncomeType::CardIssue => $this->orderProcessor->initiateIssue($card, $topupUsd),
            IncomeType::CardTopup => $this->orderProcessor->initiateTopup($card, $topupUsd),
            default => null,
        };
    }

    /**
     * Оплата не прошла. Для выпуска карты (`CardIssue`) заказ дальше не двигается —
     * карта, заведённая ещё до оплаты (шаг 0), отменяется. Для пополнения уже активной
     * карты (`CardTopup`) карта существует независимо от этого заказа — её статус не
     * трогаем, отменять нечего.
     */
    protected function handleFailed(Income $income): void
    {
        $income->update(['payment_status' => IncomePaymentStatus::Failed]);

        if ($income->type !== IncomeType::CardIssue) {
            return;
        }

        $card = $income->card;

        if (! $card || $card->status === CardStatus::Cancelled) {
            return;
        }

        CardStatusHistory::create([
            'card_id' => $card->id,
            'old_status' => $card->status,
            'new_status' => CardStatus::Cancelled,
            'reason' => 'Оплата заказа не прошла (вебхук платёжной системы)',
        ]);

        $card->update(['status' => CardStatus::Cancelled]);
    }
}
