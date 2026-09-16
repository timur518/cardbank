<?php

namespace App\Console\Commands\Providers;

use App\Enums\ActiveStatus;
use App\Enums\DiscrepancyStatus;
use App\Enums\DiscrepancyType;
use App\Models\CardProvider;
use App\Models\ProviderDiscrepancy;
use App\Services\Integrations\ProviderIntegrationResolver;
use Illuminate\Console\Command;
use Throwable;

/**
 * php artisan providers:sync-account-balances [--drop-threshold=20]
 *
 * `CardProvider::reserve_balance_usd` — это реальный баланс мастер-счёта у провайдера
 * (не ручная бухгалтерия), поэтому команда просто перезаписывает его результатом
 * `GET /account/balance`. Отдельно: если с прошлого запуска резерв просел больше чем
 * на `--drop-threshold` процентов без видимой причины, заводим
 * {@see ProviderDiscrepancy} (тип `balance_mismatch`) — это не сама синхронизация
 * ошиблась, а повод человеку проверить, куда делись деньги у провайдера.
 */
class SyncAccountBalances extends Command
{
    protected $signature = 'providers:sync-account-balances {--drop-threshold=20 : При просадке резерва на N% и больше заводить расхождение}';

    protected $description = 'Обновить остаток резерва провайдеров реальным балансом мастер-счёта из API';

    public function handle(): int
    {
        $threshold = ((float) $this->option('drop-threshold')) / 100;
        $updated = 0;
        $failed = 0;

        foreach (CardProvider::where('status', ActiveStatus::Active)->get() as $provider) {
            try {
                $balance = ProviderIntegrationResolver::for($provider)->fetchMasterBalanceUsd();
            } catch (Throwable $e) {
                $failed++;
                $this->warn("Провайдер «{$provider->name}»: {$e->getMessage()}");

                continue;
            }

            $previous = (float) $provider->reserve_balance_usd;
            $provider->update(['reserve_balance_usd' => $balance]);
            $updated++;

            $drop = $previous - $balance;

            if ($previous > 0 && $drop > 0 && ($drop / $previous) >= $threshold) {
                ProviderDiscrepancy::create([
                    'provider_id' => $provider->id,
                    'type' => DiscrepancyType::BalanceMismatch,
                    'note' => "Резерв провайдера «{$provider->name}» просел с $" . number_format($previous, 2) . ' до $' . number_format($balance, 2) . ' за один цикл синхронизации.',
                    'expected_amount' => $previous,
                    'actual_amount' => $balance,
                    'status' => DiscrepancyStatus::Open,
                ]);
            }
        }

        $this->info("Обновлено провайдеров: {$updated}." . ($failed > 0 ? " Ошибок: {$failed}." : ''));

        return self::SUCCESS;
    }
}
