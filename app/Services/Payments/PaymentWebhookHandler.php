<?php

namespace App\Services\Payments;

use App\Enums\CardStatus;
use App\Enums\IncomePaymentStatus;
use App\Enums\IncomeType;
use App\Enums\NotificationEvent;
use App\Models\Card;
use App\Models\CardStatusHistory;
use App\Models\Income;
use App\Models\Notification;
use App\Services\Integrations\CardsPro\CardsProOrderProcessor;

/**
 * Обрабатывает результат оплаты заказа от платёжной системы: находит `Income`
 * по `payment_transaction_id`, проставляет ему итоговый статус оплаты и при успехе
 * передаёт заказ дальше на фактический выпуск/пополнение карты у провайдера
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
            'failed' => $this->cancelUnpaidOrder($income, IncomePaymentStatus::Failed, 'Оплата заказа не прошла (вебхук платёжной системы)'),
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
     * Отменяет неоплаченный заказ — общая функция для любой платёжной системы:
     * вызывается и как из этого хендлера (вебхук с итогом 'failed'), и из
     * {@see \App\Console\Commands\Payments\CancelExpiredPaymentOrders} (нет вебхука вовсе спустя
     * 30 минут после создания заказа) — разница только в итоговом payment_status и тексте
     * причины в истории статусов карты.
     *
     * Для выпуска карты (`CardIssue`) карта, заведённая ещё до оплаты (шаг 0), отменяется, и
     * пользователю приходит уведомление CardIssueFailed. Для пополнения уже активной карты
     * (`CardTopup`) карта существует независимо от этого заказа — её статус не трогаем, только
     * отправляем TopupFailed.
     */
    public function cancelUnpaidOrder(Income $income, IncomePaymentStatus $status, string $reason): void
    {
        if ($income->payment_status !== IncomePaymentStatus::Pending) {
            return;
        }

        $income->update(['payment_status' => $status]);
        $card = $income->card;

        if (! $card) {
            return;
        }

        if ($income->type === IncomeType::CardTopup) {
            Notification::notify($card->user, NotificationEvent::TopupFailed, [
                'last4' => $card->card_last4,
                'amount' => NotificationEvent::money($income->amount, $income->currency),
            ], '/cards/' . $card->uuid);

            return;
        }

        if ($income->type !== IncomeType::CardIssue || $card->status === CardStatus::Cancelled) {
            return;
        }

        CardStatusHistory::create([
            'card_id' => $card->id,
            'old_status' => $card->status,
            'new_status' => CardStatus::Cancelled,
            'reason' => $reason,
        ]);

        $card->update(['status' => CardStatus::Cancelled]);
        Notification::notify($card->user, NotificationEvent::CardIssueFailed);
    }
}
