<?php

namespace App\Console\Commands\Providers;

use App\Enums\ActiveStatus;
use App\Enums\CardStatus;
use App\Models\Card;
use App\Models\CardProvider;
use App\Models\CardTransaction;
use App\Services\Integrations\ProviderIntegrationResolver;
use Illuminate\Console\Command;
use Throwable;

/**
 * php artisan providers:sync-card-transactions
 *
 * Универсальная подстраховка на случай потерянных вебхуков CARD_TRANSACTION: по каждой
 * активной/замороженной карте у каждого активного провайдера докачивает операции с
 * отметки `history_checked_at` (или с даты выпуска карты, если ещё ни разу не
 * проверяли), создаёт недостающие {@see CardTransaction} (идемпотентно по
 * `provider_tx_id` — как и обработчик вебхука) и двигает баланс карты только для
 * впервые увиденных операций.
 */
class SyncCardTransactions extends Command
{
    protected $signature = 'providers:sync-card-transactions';

    protected $description = 'Докачать историю операций по картам у всех активных провайдеров';

    public function handle(): int
    {
        $createdTotal = 0;
        $failed = 0;

        foreach (CardProvider::where('status', ActiveStatus::Active)->get() as $provider) {
            $integration = ProviderIntegrationResolver::for($provider);

            Card::where('provider_id', $provider->id)
                ->whereIn('status', [CardStatus::Active, CardStatus::Frozen])
                ->whereNotNull('provider_card_id')
                ->chunkById(50, function ($cards) use ($integration, &$createdTotal, &$failed) {
                    foreach ($cards as $card) {
                        $since = $card->history_checked_at ?? $card->issued_at;

                        try {
                            $transactions = $integration->fetchCardTransactions($card->provider_card_id, $since);
                        } catch (Throwable $e) {
                            $failed++;
                            $this->warn("Карта #{$card->id} ({$card->provider_card_id}): {$e->getMessage()}");

                            continue;
                        }

                        foreach ($transactions as $tx) {
                            if ($tx['provider_tx_id'] === '') {
                                continue;
                            }

                            $isNew = ! CardTransaction::where('card_id', $card->id)
                                ->where('provider_tx_id', $tx['provider_tx_id'])
                                ->exists();

                            CardTransaction::updateOrCreate(
                                ['card_id' => $card->id, 'provider_tx_id' => $tx['provider_tx_id']],
                                [
                                    'type' => $tx['type'],
                                    'amount' => $tx['amount'],
                                    'commission_amount' => $tx['commission_amount'],
                                    'currency' => $tx['currency'] ?: $card->currency,
                                    'merchant' => $tx['merchant'],
                                    'status' => $tx['status'],
                                    'decline_reason' => $tx['decline_reason'],
                                    'occurred_at' => $tx['occurred_at'],
                                ]
                            );

                            if ($isNew) {
                                $createdTotal++;

                                if ($tx['balance_delta'] !== 0.0) {
                                    $card->increment('balance', $tx['balance_delta']);
                                }
                            }
                        }

                        $card->update(['history_checked_at' => now()]);
                    }
                });
        }

        $this->info("Новых операций: {$createdTotal}." . ($failed > 0 ? " Ошибок: {$failed}." : ''));

        return self::SUCCESS;
    }
}
