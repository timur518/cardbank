<?php

namespace App\Console\Commands\Providers;

use App\Console\Commands\Providers\Concerns\DescribesSyncErrors;
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
 * проверяли), создаёт недостающие {@see CardTransaction} идемпотентно через
 * {@see CardTransaction::upsertFromProvider()} (как и обработчик вебхука), там же
 * сливающий расчёт с его холдом по origin_tx_id вместо второй строки на ту же покупку. Если
 * среди впервые увиденных операций была хотя бы одна, двигающая деньги, баланс карты не
 * досчитывается локально, а перезапрашивается у провайдера один раз за карту за прогон
 * (см. {@see \App\Models\Card::refreshBalanceFromProvider()}).
 */
class SyncCardTransactions extends Command
{
    use DescribesSyncErrors;

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
                            $this->warn("Карта #{$card->id} ({$card->provider_card_id}): {$this->describeError($e)}");

                            continue;
                        }

                        $balanceMightHaveChanged = false;

                        foreach ($transactions as $tx) {
                            if ($tx['provider_tx_id'] === '') {
                                continue;
                            }

                            $tx['currency'] = $tx['currency'] ?: $card->currency;

                            $result = CardTransaction::upsertFromProvider($card->id, $tx);

                            if ($result['isNew']) {
                                $createdTotal++;

                                if ($tx['balance_delta'] !== 0.0) {
                                    $balanceMightHaveChanged = true;
                                }
                            }
                        }

                        // Один перезапрос баланса на карту за прогон, а не на каждую новую операцию —
                        // итоговый результат всё равно сходится к реальному балансу у CardsPro. Одна
                        // неудачная карта не должна останавливать весь прогон.
                        if ($balanceMightHaveChanged) {
                            try {
                                $card->refreshBalanceFromProvider();
                            } catch (Throwable $e) {
                                $failed++;
                                $this->warn("Карта #{$card->id} ({$card->provider_card_id}): {$this->describeError($e)}");
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
