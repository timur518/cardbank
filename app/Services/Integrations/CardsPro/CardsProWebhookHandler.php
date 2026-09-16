<?php

namespace App\Services\Integrations\CardsPro;

use App\Enums\CardsProCallbackType;
use App\Enums\CardStatus;
use App\Enums\CardTransactionStatus;
use App\Enums\CardTransactionType;
use App\Models\Card;
use App\Models\CardProvider;
use App\Models\CardStatusHistory;
use App\Models\CardTransaction;

/**
 * Применяет к нашим моделям изменения из вебхуков CardsPro — но только там, где в
 * самом колбэке достаточно данных, чтобы сделать это безопасно и однозначно.
 * Сырое тело любого вебхука в любом случае сохраняется в ProviderMessage
 * контроллером до вызова этого класса — так что необработанные события не теряются,
 * их всегда можно разобрать вручную.
 *
 * Известное ограничение: колбэк CARD_ISSUE не содержит user_id/card_product_id,
 * поэтому создать карту по одному этому событию нельзя — событие только логируется.
 * Подробности — в docs/integrations/cardspro.md, раздел «Вебхуки».
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
            CardsProCallbackType::CardTopup => $this->applyBalanceChange($payload, 1),
            CardsProCallbackType::CardWithdrawal => $this->applyBalanceChange($payload, -1),
            CardsProCallbackType::CardBlock => $this->handleBlock($payload),
            CardsProCallbackType::CardFreeze => $this->handleStatusChange($payload, CardStatus::Frozen, 'Карта заморожена по данным CardsPro (CARD_FREEZE)'),
            CardsProCallbackType::CardUnfreeze => $this->handleStatusChange($payload, CardStatus::Active, 'Карта разморожена по данным CardsPro (CARD_UNFREEZE)'),
            CardsProCallbackType::CardTransaction => $this->handleTransaction($payload),
            // CARD_ISSUE, EXTRA_FEE_CARD, EXTRA_FEE_CAP, OTP_CODE, KYC_CHANGE:
            // осознанно не обрабатываются автоматически — см. docblock класса.
            default => null,
        };
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

    protected function applyBalanceChange(array $payload, int $sign): void
    {
        if (($payload['status'] ?? null) !== 'EXECUTED') {
            return;
        }

        $card = $this->findCard((string) ($payload['san'] ?? ''));
        $amount = (float) ($payload['params']['amount'] ?? 0);

        if (! $card || $amount <= 0) {
            return;
        }

        $card->increment('balance', $sign * $amount);
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
    }

    protected function handleTransaction(array $payload): void
    {
        $card = $this->findCard((string) ($payload['san'] ?? ''));
        $providerTxId = (string) ($payload['txId'] ?? '');

        if (! $card || $providerTxId === '') {
            return;
        }

        [$type, $status, $balanceSign] = $this->mapTransactionType((string) ($payload['txType'] ?? ''));
        $amount = (float) ($payload['billAmount'] ?? $payload['txAmount'] ?? 0);

        $isNewTransaction = ! CardTransaction::where('card_id', $card->id)
            ->where('provider_tx_id', $providerTxId)
            ->exists();

        CardTransaction::updateOrCreate(
            ['card_id' => $card->id, 'provider_tx_id' => $providerTxId],
            [
                'type' => $type,
                'amount' => $amount,
                'commission_amount' => $payload['fee'] ?? null,
                'currency' => $payload['billCurrency'] ?? $card->currency,
                'merchant' => $payload['merchantName'] ?? null,
                'status' => $status,
                'decline_reason' => $payload['declineReason'] ?? null,
                'occurred_at' => $payload['txDate'] ?? now(),
            ]
        );

        // Баланс двигаем только один раз, при первом получении события на этот
        // provider_tx_id — повторная доставка того же вебхука не должна списывать дважды.
        if ($isNewTransaction && $balanceSign !== 0 && $amount > 0) {
            $card->increment('balance', $balanceSign * $amount);
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
