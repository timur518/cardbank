<?php

namespace App\Services\Payments;

use App\Console\Commands\Payments\CancelExpiredPaymentOrders;
use App\Enums\AdminTelegramEvent;
use App\Enums\CardStatus;
use App\Enums\IncomePaymentStatus;
use App\Enums\IncomeType;
use App\Enums\NotificationEvent;
use App\Models\CardStatusHistory;
use App\Models\Income;
use App\Models\Notification;
use App\Services\Integrations\CardsPro\CardsProOrderProcessor;
use App\Services\Referral\ReferralService;
use App\Services\Telegram\AdminTelegramNotifier;

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
    public function __construct(
        protected CardsProOrderProcessor $orderProcessor,
        protected SandboxOrderProcessor $sandboxOrderProcessor,
    ) {
        //
    }

    /**
     * @param  array{transaction_id: string, status: 'paid'|'failed'|'unknown', amount_usd?: ?float}  $event
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
            'paid' => $this->handlePaid($income, $event['amount_usd'] ?? null),
            'failed' => $this->cancelUnpaidOrder($income, IncomePaymentStatus::Failed, 'Оплата заказа не прошла (вебхук платёжной системы)'),
        };
    }

    /**
     * $amountUsd — реальная сумма конвертации от платёжной системы (сейчас — только
     * у BitbankerGateway, см. PaymentGatewayContract::parseWebhookPayload()) — при наличии перезаписывает
     * `Income.amount_usd`, рассчитанный заранее по внутреннему курсу в OrderController. `topup_usd` (сумма,
     * которая реально начисляется на карту ниже) не трогается — клиент должен получить ровно ту
     * сумму, которая была показана ему до оплаты, независимо от реального курса конвертации BitBanker.
     */
    protected function handlePaid(Income $income, ?float $amountUsd = null): void
    {
        $income->update(array_filter([
            'payment_status' => IncomePaymentStatus::Paid,
            'amount_usd' => $amountUsd,
        ], fn ($value) => $value !== null));

        // Начисление партнёру, пригласившему платёжника этого Income (referral_code), если таковой есть.
        ReferralService::accrueForIncome($income);

        $card = $income->card;

        if (! $card) {
            return;
        }

        AdminTelegramNotifier::notify(AdminTelegramEvent::NewIncome, [
            'name' => $card->user->name,
            'email' => $card->user->email,
            'type' => $income->type->getLabel(),
            'amount' => NotificationEvent::money((float) $income->amount, $income->currency),
        ]);

        $topupUsd = (float) $income->topup_usd;

        // Режим песочницы (PaymentMethod.sandbox_mode) — оплата принята по-настоящему, но
        // сам выпуск/пополнение у провайдера имитируется SandboxOrderProcessor'ом без единого
        // реального запроса к CardsPro (см. его докблок).
        $processor = $income->paymentMethod?->sandbox_mode ? $this->sandboxOrderProcessor : $this->orderProcessor;

        match ($income->type) {
            IncomeType::CardIssue => $processor->initiateIssue($card, $topupUsd),
            IncomeType::CardTopup => $processor->initiateTopup($card, $topupUsd),
            default => null,
        };
    }

    /**
     * Отменяет неоплаченный заказ — общая функция для любой платёжной системы:
     * вызывается и как из этого хендлера (вебхук с итогом 'failed'), и из
     * {@see CancelExpiredPaymentOrders} (нет вебхука вовсе спустя
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
            ], '/cards/'.$card->uuid);

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
