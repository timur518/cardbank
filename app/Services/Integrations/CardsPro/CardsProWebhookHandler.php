<?php

namespace App\Services\Integrations\CardsPro;

use App\Enums\CardProviderOperationType;
use App\Enums\CardsProCallbackType;
use App\Enums\CardStatus;
use App\Enums\CardTransactionStatus;
use App\Enums\CardTransactionType;
use App\Enums\NotificationEvent;
use App\Models\Card;
use App\Models\CardProvider;
use App\Models\CardProviderOperation;
use App\Models\CardStatusHistory;
use App\Models\CardTransaction;
use App\Models\Notification;
use App\Services\CardProviderOperationResolver;
use Throwable;

/**
 * Применяет к нашим моделям изменения из вебхуков CardsPro — но только там, где в
 * самом колбэке достаточно данных, чтобы сделать это безопасно и однозначно.
 * Сырое тело любого вебхука в любом случае сохраняется в ProviderMessage
 * контроллером до вызова этого класса — так что необработанные события не теряются,
 * их всегда можно разобрать вручную.
 *
 * `CARD_ISSUE` обрабатывается, только если в `card_provider_operations` есть запись
 * с таким `request_id` (её должна создавать та часть админки/API, которая инициирует
 * выпуск через `issueCard()`, с уже известным `card_id` — `Card` заводится до оплаты,
 * ещё до вызова `issueCard()`). Без такой записи событие по-прежнему
 * только логируется. Подробности — в docs/integrations/cardspro.md, раздел «Вебхуки».
 */
class CardsProWebhookHandler
{
    public function __construct(protected CardProvider $provider)
    {
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function handle(CardsProCallbackType $type, array $payload): void
    {
        match ($type) {
            CardsProCallbackType::CardTopup => $this->handleTopup($payload),
            CardsProCallbackType::CardWithdrawal => $this->applyBalanceChange($payload),
            CardsProCallbackType::CardBlock => $this->handleBlock($payload),
            CardsProCallbackType::CardFreeze => $this->handleStatusChange($payload, CardStatus::Frozen, 'Карта заморожена по данным CardsPro (CARD_FREEZE)'),
            CardsProCallbackType::CardUnfreeze => $this->handleStatusChange($payload, CardStatus::Active, 'Карта разморожена по данным CardsPro (CARD_UNFREEZE)'),
            CardsProCallbackType::CardTransaction => $this->handleTransaction($payload),
            CardsProCallbackType::CardIssue => $this->handleIssue($payload),
            // EXTRA_FEE_CARD, EXTRA_FEE_CAP, OTP_CODE, KYC_CHANGE:
            // осознанно не обрабатываются автоматически — см. docblock класса.
            default => null,
        };
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function handleIssue(array $payload): void
    {
        $requestId = (string) ($payload['request_id'] ?? '');

        if ($requestId === '') {
            return;
        }

        $operation = CardProviderOperation::where('provider_id', $this->provider->id)
            ->where('request_id', $requestId)
            ->where('type', CardProviderOperationType::Issue)
            ->first();

        if (! $operation) {
            return;
        }

        $outcome = match ((string) ($payload['status'] ?? '')) {
            'EXECUTED' => 'completed',
            'DECLINED' => 'failed',
            default => null,
        };

        if ($outcome === null) {
            return;
        }

        app(CardProviderOperationResolver::class)->resolve($operation, $outcome, $payload);
    }

    protected function findCard(string $san): ?Card
    {
        if ($san === '') {
            return null;
        }

        return Card::where('provider_id', $this->provider->id)
            ->where('provider_card_id', $san)
            ->first();
    }

    protected function applyBalanceChange(array $payload): void
    {
        if (($payload['status'] ?? null) !== 'EXECUTED') {
            return;
        }

        $card = $this->findCard((string) ($payload['san'] ?? ''));
        $amount = (float) ($payload['params']['amount'] ?? 0);

        if (! $card || $amount <= 0) {
            return;
        }

        $this->refreshCardBalance($card);
    }

    /**
     * Неудача этого запроса (сеть/CardsPro недоступен) не должна валить весь вебхук —
     * сама транзакция/отклонение к этому моменту уже записаны и теряться не должны.
     * Ошибка только логируется — баланс подтянется либо повторной доставкой этого же вебхука
     * (если CardsPro его ретраит), либо в течение 15 минут — фоновым
     * providers:sync-card-balances.
     */
    protected function refreshCardBalance(Card $card): void
    {
        try {
            $card->refreshBalanceFromProvider();
        } catch (Throwable $e) {
            report($e);
        }
    }

    /**
     * CARD_TOPUP, в отличие от вывода средств, ещё и реальная оплатная операция клиента, поэтому
     * кроме баланса также записываем её в «Транзакции по картам» (тип Topup) — иначе она нигде
     * не видна и выпадает из оборота по картам.
     *
     * Строка чаще всего уже существует к этому моменту со status=Pending — её заводит
     * {@see \App\Services\Integrations\CardsPro\CardsProOrderProcessor::recordPendingTransaction()} сразу
     * после запроса `orders/topup` (чтобы клиент видел «в обработке» сразу после оплаты, а не
     * только после этого вебхука). Здесь она по тому же `docid`/`request_id` переводится в
     * Success (EXECUTED) или Declined (DECLINED), а не создаётся вторая. Если по какой-топричине
     * pending-строки не оказалось (старые данные до этой доработки/нет docid в ответе на `orders/topup`) —
     * EXECUTED всё равно создаёт строку сразу как Success (старое поведение), а DECLINED просто игнорируется
     * (связывать отказ нечем). Идемпотентно по `docid`/`request_id` (в теле CARD_TOPUP нет
     * своего `txId`, как у CARD_TRANSACTION).
     */
    protected function handleTopup(array $payload): void
    {
        $card = $this->findCard((string) ($payload['san'] ?? ''));
        $providerTxId = (string) ($payload['docid'] ?? $payload['request_id'] ?? '');
        $status = (string) ($payload['status'] ?? '');

        if (! $card || $providerTxId === '') {
            return;
        }

        if ($status === 'DECLINED') {
            // Читаем строку через first()+update() (а не массовым update() по query), чтобы
            // взять её amount/currency для уведомления — в самом payload отказа CardsPro сумма не
            // гарантирована.
            $pending = CardTransaction::where('card_id', $card->id)
                ->where('provider_tx_id', $providerTxId)
                ->where('status', CardTransactionStatus::Pending)
                ->first();

            if (! $pending) {
                return;
            }

            $pending->update([
                'type' => CardTransactionType::Decline,
                'status' => CardTransactionStatus::Declined,
                'decline_reason' => (string) ($payload['declineReason'] ?? $payload['message'] ?? 'Провайдер отклонил пополнение карты'),
            ]);

            Notification::notify($card->user, NotificationEvent::TopupFailed, [
                'last4' => $card->card_last4,
                'amount' => NotificationEvent::money($pending->amount, $pending->currency),
            ], '/cards/' . $card->uuid);

            return;
        }

        $amount = (float) ($payload['params']['amount'] ?? 0);

        if ($status !== 'EXECUTED' || $amount <= 0) {
            return;
        }

        // Считаем ДО обновления — иначе после updateOrCreate() она всегда будет Success.
        $wasAlreadySuccess = CardTransaction::where('card_id', $card->id)
            ->where('provider_tx_id', $providerTxId)
            ->where('status', CardTransactionStatus::Success)
            ->exists();

        CardTransaction::updateOrCreate(
            ['card_id' => $card->id, 'provider_tx_id' => $providerTxId],
            [
                'type' => CardTransactionType::Topup,
                'amount' => $amount,
                'currency' => $payload['params']['currency'] ?? $card->currency,
                'status' => CardTransactionStatus::Success,
                'occurred_at' => now(),
            ]
        );

        // Перезапрашиваем баланс у CardsPro только один раз, при первом переходе этой операции в
        // Success — повторная доставка того же вебхука не должна дёргать апи впустую; по той же
        // причине только там же шлём уведомление — чтобы клиент не получил его повторно на ретрай того же
        // вебхука.
        if (! $wasAlreadySuccess) {
            $this->refreshCardBalance($card);

            Notification::notify($card->user, NotificationEvent::TopupSuccess, [
                'last4' => $card->card_last4,
                'amount' => NotificationEvent::money($amount, $payload['params']['currency'] ?? $card->currency),
                'balance' => NotificationEvent::money($card->balance, $card->currency),
            ], '/cards/' . $card->uuid);
        }
    }

    protected function handleBlock(array $payload): void
    {
        if (($payload['status'] ?? null) !== 'EXECUTED') {
            return;
        }

        $card = $this->findCard((string) ($payload['san'] ?? ''));

        if (! $card) {
            return;
        }

        $this->changeStatus($card, CardStatus::Closed, 'Карта заблокирована по данным CardsPro (CARD_BLOCK)');
        $card->update(['closed_at' => now()]);
    }

    protected function handleStatusChange(array $payload, CardStatus $newStatus, string $reason): void
    {
        $card = $this->findCard((string) ($payload['san'] ?? ''));

        if ($card) {
            $this->changeStatus($card, $newStatus, $reason);
        }
    }

    protected function changeStatus(Card $card, CardStatus $newStatus, string $reason): void
    {
        if ($card->status === $newStatus) {
            return;
        }

        CardStatusHistory::create([
            'card_id' => $card->id,
            'old_status' => $card->status,
            'new_status' => $newStatus,
            'reason' => $reason,
        ]);

        $card->update(['status' => $newStatus]);

        $event = match ($newStatus) {
            CardStatus::Frozen => NotificationEvent::CardFrozen,
            CardStatus::Active => NotificationEvent::CardUnfrozen,
            CardStatus::Closed => NotificationEvent::CardClosed,
            default => null,
        };

        if ($event) {
            Notification::notify($card->user, $event, ['last4' => $card->card_last4]);
        }
    }

    protected function handleTransaction(array $payload): void
    {
        $card = $this->findCard((string) ($payload['san'] ?? ''));
        $providerTxId = (string) ($payload['txId'] ?? '');

        if (! $card || $providerTxId === '') {
            return;
        }

        [$type, $status, $balanceSign] = $this->mapTransactionType((string) ($payload['txType'] ?? ''));
        // billAmount — сумма до комиссии, fee — отдельная комиссия, списываемая с той
        // же карты. amount — итоговая списанная сумма (billAmount + fee), именно она
        // должна идти в оборот и в баланс карты (см. GET /{san}/transactions, где та же
        // связка задокументирована явно: transactionValue + transactionCommission = transactionSum).
        $fee = isset($payload['fee']) ? (float) $payload['fee'] : 0.0;
        $costAmount = isset($payload['billAmount']) ? (float) $payload['billAmount'] : null;
        $amount = ($costAmount ?? (float) ($payload['txAmount'] ?? 0)) + $fee;

        // originTxnId — id холда (authorization), который расчитывает эта операция (см.
        // docs.cardspro.com/api/operations-callbacks) — upsertFromProvider() сольёт расчёт
        // с его холдом в одну запись вместо второй строки на ту же покупку.
        $originTxId = ($payload['originTxnId'] ?? null) !== null ? (string) $payload['originTxnId'] : null;

        $result = CardTransaction::upsertFromProvider($card->id, [
            'provider_tx_id' => $providerTxId,
            'origin_tx_id' => $originTxId,
            'type' => $type,
            'amount' => $amount,
            'cost_amount' => $costAmount,
            'commission_amount' => isset($payload['fee']) ? $fee : null,
            'currency' => $payload['billCurrency'] ?? $card->currency,
            'merchant' => $payload['merchantName'] ?? null,
            'status' => $status,
            'decline_reason' => $payload['declineReason'] ?? null,
            'occurred_at' => $payload['txDate'] ?? now(),
        ]);

        // $balanceSign === 0 значит, что этот txType вообще не двигает деньги (холд/отклон) —
        // перезапрашивать баланс у провайдера тогда нечем. Иначе делаем это только один
        // раз, при первом получении этой операции — повторная доставка того же вебхука не
        // должна дёргать CardsPro впустую.
        if ($result['isNew'] && $balanceSign !== 0 && $amount > 0) {
            $this->refreshCardBalance($card);
        }

        // Тот же флаг isNew подстраховывает от повторного уведомления на ретрай того же вебхука.
        if ($result['isNew'] && $status === CardTransactionStatus::Declined) {
            Notification::notify($card->user, NotificationEvent::CardPurchaseDeclined, [
                'last4' => $card->card_last4,
                'amount' => NotificationEvent::money($amount, $payload['billCurrency'] ?? $card->currency),
                'merchant' => $payload['merchantName'] ?? null,
            ], '/cards/' . $card->uuid);
        }
    }

    /**
     * @return array{0: CardTransactionType, 1: CardTransactionStatus, 2: int}
     */
    protected function mapTransactionType(string $txType): array
    {
        return match ($txType) {
            'expense' => [CardTransactionType::Purchase, CardTransactionStatus::Success, -1],
            'authorization' => [CardTransactionType::Purchase, CardTransactionStatus::Pending, 0],
            'authorization_decline' => [CardTransactionType::Decline, CardTransactionStatus::Declined, 0],
            'reversal' => [CardTransactionType::Refund, CardTransactionStatus::Reversed, 1],
            'refund' => [CardTransactionType::Refund, CardTransactionStatus::Success, 1],
            'verification' => [CardTransactionType::Purchase, CardTransactionStatus::Pending, 0],
            'verification_decline' => [CardTransactionType::Decline, CardTransactionStatus::Declined, 0],
            'verification_expense' => [CardTransactionType::Purchase, CardTransactionStatus::Success, -1],
            'maintenance_fee' => [CardTransactionType::Fee, CardTransactionStatus::Success, -1],
            default => [CardTransactionType::Purchase, CardTransactionStatus::Pending, 0],
        };
    }
}
