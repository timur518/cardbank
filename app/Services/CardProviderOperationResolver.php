<?php

namespace App\Services;

use App\Enums\CardProviderOperationStatus;
use App\Enums\CardProviderOperationType;
use App\Enums\CardStatus;
use App\Enums\ExpenseCategory;
use App\Models\Card;
use App\Models\CardProvider;
use App\Models\CardProviderOperation;
use App\Models\CardStatusHistory;
use App\Models\Expense;
use App\Models\Setting;
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
     * Активирует карту по результату выпуска. `Card` к этому моменту уже
     * существует (заводится до оплаты, в шаге 0 оформления заказа, со статусом
     * `CardStatus::Waiting`/`Pending`), `operation->card_id` заполнен с самого начала — здесь
     * мы только проставляем реальные данные от провайдера (`san`, номер, баланс) и заводим
     * расходы на выпуск/пополнение.
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

        if ($san === '' || ! $operation->card_id) {
            return;
        }

        $card = Card::find($operation->card_id);

        if (! $card) {
            return;
        }

        $snapshot = ProviderIntegrationResolver::for($operation->provider)->fetchCardSnapshot($san);

        $card->update([
            'provider_card_id' => $san,
            'card_number' => $snapshot['card_number'],
            'expiry' => $snapshot['expiry'],
            'currency' => $snapshot['currency'] ?: $card->currency, // не затирать снепшот с продукта, если провайдер не прислал
            'balance' => $snapshot['balance'],
            'status' => $snapshot['status'], // обычно сразу Active
            'issued_at' => now(),
        ]);

        $this->recordIssueExpenses($card, $operation);
    }

    /**
     * Себестоимость выпуска (`CardIssue`) и сумма начального пополнения с комиссией
     * провайдера (`CardTopup`) — отдельными `Expense` на каждую карту, чтобы «Валовая
     * прибыль» (см. `ProfitStatsWidget::grossProfitStat()`) не показывала выручку без
     * вычета реальных затрат. `topup_usd` берём из `payload` операции — это ровно та
     * сумма, что ушла в `issueCard()` на шаге инициации выпуска.
     */
    protected function recordIssueExpenses(Card $card, CardProviderOperation $operation): void
    {
        $issueCostUsd = (float) $card->issue_cost_usd;

        if ($issueCostUsd > 0) {
            $this->createExpense(
                $card,
                $operation,
                ExpenseCategory::CardIssue,
                $issueCostUsd,
                "Себестоимость выпуска у провайдера (авто, операция #{$operation->id})"
            );
        }

        $topupUsd = (float) ($operation->payload['topup_usd'] ?? 0);

        $this->recordTopupExpense(
            $card,
            $operation,
            $topupUsd,
            "Пополнение карты с комиссией провайдера при выпуске (авто, операция #{$operation->id})"
        );
    }

    /**
     * Сумма пополнения + комиссия провайдера за пополнение (`CardProduct.provider_topup_fee_percent`) —
     * общей записью `Expense`, категория `CardTopup`. Используется и при первом пополнении
     * (внутри `recordIssueExpenses()`), и при последующих пополнениях уже активной карты
     * (`applyTopup()` ниже) — в обоих случаях деньги реально уходят с мастер-счёта у провайдера
     * той же формулой.
     */
    protected function recordTopupExpense(Card $card, CardProviderOperation $operation, float $topupUsd, string $comment): void
    {
        if ($topupUsd <= 0) {
            return;
        }

        $feePercent = (float) ($card->cardProduct?->provider_topup_fee_percent ?? 0);
        $feeUsd = round($topupUsd * $feePercent / 100, 2);

        $this->createExpense($card, $operation, ExpenseCategory::CardTopup, $topupUsd + $feeUsd, $comment);
    }

    /**
     * `amount_usd` — то, что видит `grossProfitStat()`; `amount` (в ₽ по курсу ЦБ на
     * момент создания расхода) — для единообразия с остальными `Expense`, где `amount`
     * всегда в ₽ по курсу ЦБ РФ на момент операции.
     */
    protected function createExpense(Card $card, CardProviderOperation $operation, ExpenseCategory $category, float $amountUsd, string $comment): void
    {
        $rate = (float) Setting::get('currency_rate_usd', 0);

        Expense::create([
            'date' => now(),
            'category' => $category,
            'amount' => round($amountUsd * $rate, 2),
            'amount_usd' => $amountUsd,
            'card_id' => $card->id,
            'provider_id' => $operation->provider_id,
            'comment' => $comment,
        ]);
    }

    protected function applyTopup(CardProviderOperation $operation): void
    {
        $amount = (float) ($operation->payload['amount'] ?? 0);

        if (! $operation->card_id || $amount <= 0) {
            return;
        }

        $card = Card::find($operation->card_id);

        if (! $card) {
            return;
        }

        $card->increment('balance', $amount);
        $this->recordTopupExpense($card, $operation, $amount, "Пополнение карты с комиссией провайдера (авто, операция #{$operation->id})");
    }

    /**
     * Синхронный отказ CardsPro сразу в ответе на `issueCard()` (`DECLINED`, ещё до
     * какого-либо вебхука) — в отличие от `resolve()`/`claim()`, сюда не попадает
     * асинхронный результат по вебхуку или `providers:sync-pending-operations`.
     * Заводит операцию сразу в статусе `Failed` (не `Pending`, ждать здесь нечего) и
     * переводит карту в `CardStatus::Failed`. Вызывается из
     * `CardsProOrderProcessor::initiateIssue()` сразу по синхронному ответу `issueCard()`,
     * если провайдер сразу вернул `DECLINED`.
     *
     * @param  array<string, mixed>  $raw
     */
    public function recordDeclinedIssue(Card $card, CardProvider $provider, string $requestId, ?string $docid, array $raw): CardProviderOperation
    {
        $operation = CardProviderOperation::create([
            'provider_id' => $provider->id,
            'card_id' => $card->id,
            'type' => CardProviderOperationType::Issue,
            'request_id' => $requestId,
            'docid' => $docid,
            'status' => CardProviderOperationStatus::Failed,
            'result' => $raw,
            'error' => (string) ($raw['declineReason'] ?? $raw['message'] ?? 'Провайдер отклонил выпуск карты'),
            'resolved_at' => now(),
        ]);

        CardStatusHistory::create([
            'card_id' => $card->id,
            'old_status' => $card->status,
            'new_status' => CardStatus::Failed,
            'reason' => "Провайдер отклонил выпуск карты (операция #{$operation->id})",
        ]);

        $card->update(['status' => CardStatus::Failed]);

        return $operation;
    }

    /**
     * Синхронный отказ CardsPro сразу в ответе на `topUpCard()` (`DECLINED`) — пополнение
     * заказом (`orders/topup`, не начальное пополнение при выпуске) уже существующей
     * активной карты. В отличие от `recordDeclinedIssue()`, карта тут ни при чём: она
     * уже выпущена и активна независимо от исхода этого конкретного пополнения, поэтому
     * её статус не трогаем — только фиксируем неудачную операцию.
     *
     * @param  array<string, mixed>  $raw
     */
    public function recordDeclinedTopup(Card $card, CardProvider $provider, string $requestId, ?string $docid, array $raw): CardProviderOperation
    {
        return CardProviderOperation::create([
            'provider_id' => $provider->id,
            'card_id' => $card->id,
            'type' => CardProviderOperationType::Topup,
            'request_id' => $requestId,
            'docid' => $docid,
            'status' => CardProviderOperationStatus::Failed,
            'result' => $raw,
            'error' => (string) ($raw['declineReason'] ?? $raw['message'] ?? 'Провайдер отклонил пополнение карты'),
            'resolved_at' => now(),
        ]);
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
