<?php

namespace App\Console\Commands\Providers;

use App\Enums\ActiveStatus;
use App\Enums\CardStatus;
use App\Models\Card;
use App\Models\CardProvider;
use App\Models\CardStatusHistory;
use App\Services\Integrations\ProviderIntegrationResolver;
use Illuminate\Console\Command;
use Throwable;

/**
 * php artisan providers:sync-card-balances [--minutes=10]
 *
 * Универсальная (не привязанная к конкретному провайдеру) подстраховка на случай
 * потерянных вебхуков CARD_TOPUP/CARD_WITHDRAWAL/CARD_FREEZE/CARD_UNFREEZE/CARD_BLOCK:
 * проходит по активным/замороженным картам ВСЕХ активных провайдеров и подтягивает их
 * реальный баланс и статус через {@see ProviderIntegrationResolver}. Карты, у которых
 * `balance_checked_at` свежее `--minutes`, пропускаются, чтобы не дёргать API впустую
 * при частом запуске.
 */
class SyncCardBalances extends Command
{
    protected $signature = 'providers:sync-card-balances {--minutes=10 : Не проверять карты, обновлённые менее N минут назад}';

    protected $description = 'Обновить баланс и статус карт у всех активных провайдеров';

    public function handle(): int
    {
        $staleBefore = now()->subMinutes((int) $this->option('minutes'));
        $updated = 0;
        $failed = 0;

        foreach (CardProvider::where('status', ActiveStatus::Active)->get() as $provider) {
            $integration = ProviderIntegrationResolver::for($provider);

            Card::where('provider_id', $provider->id)
                ->whereIn('status', [CardStatus::Active, CardStatus::Frozen])
                ->whereNotNull('provider_card_id')
                ->where(fn ($query) => $query->whereNull('balance_checked_at')->orWhere('balance_checked_at', '<', $staleBefore))
                ->chunkById(50, function ($cards) use ($integration, &$updated, &$failed) {
                    foreach ($cards as $card) {
                        try {
                            $snapshot = $integration->fetchCardSnapshot($card->provider_card_id);
                        } catch (Throwable $e) {
                            $failed++;
                            $this->warn("Карта #{$card->id} ({$card->provider_card_id}): {$e->getMessage()}");

                            continue;
                        }

                        if ($snapshot['status'] !== $card->status) {
                            CardStatusHistory::create([
                                'card_id' => $card->id,
                                'old_status' => $card->status,
                                'new_status' => $snapshot['status'],
                                'reason' => 'Статус обновлён фоновой синхронизацией (providers:sync-card-balances)',
                            ]);
                        }

                        $card->update([
                            'balance' => $snapshot['balance'],
                            'status' => $snapshot['status'],
                            'balance_checked_at' => now(),
                        ]);
                        $updated++;
                    }
                });
        }

        $this->info("Обновлено карт: {$updated}." . ($failed > 0 ? " Ошибок: {$failed}." : ''));

        return self::SUCCESS;
    }
}
