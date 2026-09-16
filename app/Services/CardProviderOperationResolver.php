<?php

namespace App\Services;

use App\Enums\CardProviderOperationStatus;
use App\Enums\CardProviderOperationType;
use App\Enums\CardStatus;
use App\Models\Card;
use App\Models\CardProduct;
use App\Models\CardProviderOperation;
use App\Models\CardStatusHistory;
use App\Services\Integrations\ProviderIntegrationResolver;

/**
 * Применяет финальный результат асинхронной операции провайдера (выпуск/пополнение/
 * вывод/блокировка) к нашим моделям — одинаково для обоих источников результата:
 * вебхука (пришёл раньше) и {@see \App\Console\Commands\Providers\SyncPendingOperations}
 * (опросил и обнаружил, что вебхук потерялся). Кто бы ни узнал результат первым,
 * {@see resolve()} атомарно «забирает» операцию (`UPDATE ... WHERE status = pending`),
 * так что повторный вызов из другого источника не применит эффект дважды.
 */
class CardProviderOperationResolver
{
    /**
     * @param  'completed'|'failed'  $outcome
     * @param  array<string, mixed>  $raw
     */
    public function resolve(CardProviderOperation $operation, string $outcome, array $raw): void
    {
        if (! $this->claim($operation, $outcome, $raw)) {
            return;
        }

        if ($outcome !== 'completed') {
            return;
        }

        match ($operation->type) {
            CardProviderOperationType::Issue => $this->applyIssue($operation->fresh(), $raw),
            CardProviderOperationType::Topup => $this->applyTopup($operation->fresh()),
            CardProviderOperationType::Withdraw => $this->applyWithdraw($operation->fresh()),
            CardProviderOperationType::Block => $this->applyBlock($operation->fresh()),
        };
    }

    /**
     * @param  array<string, mixed>  $raw
     */
    protected function claim(CardProviderOperation $operation, string $outcome, array $raw): bool
    {
        $claimed = CardProviderOperation::where('id', $operation->id)
            ->where('status', CardProviderOperationStatus::Pending)
            ->update([
                'status' => $outcome === 'completed' ? CardProviderOperationStatus::Completed : CardProviderOperationStatus::Failed,
                'result' => $raw,
                'resolved_at' => now(),
                'error' => $outcome === 'failed' ? (string) ($raw['declineReason'] ?? $raw['message'] ?? 'Провайдер отклонил операцию') : null,
            ]);

        return $claimed === 1;
    }

    /**
     * Создаёт карту по результату выпуска. `san` в ответе провайдера — единственная
     * связь с реальной картой, которую можно получить только сейчас (до этого момента
     * карты физически не существовало). Владелец и продукт берутся из `payload`,
     * сохранённого при инициации выпуска — сам провайдер об этом ничего не знает.
     *
     * ⚠️ Поле `san` в ответе `GET /request/status` для операции `issue` не
     * задокументировано явно (см. docs/integrations/cardspro.md) — предположение по
     * аналогии с вебхуком `CARD_ISSUE`. Проверить на реальном ответе песочницы.
     *
     * @param  array<string, mixed>  $raw
     */
    protected function applyIssue(CardProviderOperation $operation, array $raw): void
    {
        $san = (string) ($raw['san'] ?? '');
        $payload = $operation->payload ?? [];
        $userId = $payload['user_id'] ?? null;
        $cardProductId = $payload['card_product_id'] ?? null;

        if ($san === '' || ! $userId || ! $cardProductId) {
            return;
        }

        $product = CardProduct::find($cardProductId);
        $snapshot = ProviderIntegrationResolver::for($operation->provider)->fetchCardSnapshot($san);

        $card = Card::create([
            'user_id' => $userId,
            'card_product_id' => $cardProductId,
            'provider_id' => $operation->provider_id,
            'provider_card_id' => $san,
            'card_number' => $snapshot['card_number'],
            'expiry' => $snapshot['expiry'],
            'currency' => $snapshot['currency'] ?: $product?->currency,
            'balance' => $snapshot['balance'],
            'price_rub' => $product?->price_rub,
            'issue_cost_usd' => $product?->provider_issue_cost_usd,
            'status' => $snapshot['status'],
            'issued_at' => now(),
        ]);

        $operation->update(['card_id' => $card->id]);
    }

    protected function applyTopup(CardProviderOperation $operation): void
    {
        $amount = (float) ($operation->payload['amount'] ?? 0);

        if ($operation->card_id && $amount > 0) {
            Card::where('id', $operation->card_id)->increment('balance', $amount);
        }
    }

    protected function applyWithdraw(CardProviderOperation $operation): void
    {
        $amount = (float) ($operation->payload['amount'] ?? 0);

        if ($operation->card_id && $amount > 0) {
            Card::where('id', $operation->card_id)->decrement('balance', $amount);
        }
    }

    protected function applyBlock(CardProviderOperation $operation): void
    {
        $card = $operation->card_id ? Card::find($operation->card_id) : null;

        if (! $card || $card->status === CardStatus::Closed) {
            return;
        }

        CardStatusHistory::create([
            'card_id' => $card->id,
            'old_status' => $card->status,
            'new_status' => CardStatus::Closed,
            'reason' => "Заблокирована по результату операции провайдера #{$operation->id}",
        ]);

        $card->update(['status' => CardStatus::Closed, 'closed_at' => now()]);
    }
}
