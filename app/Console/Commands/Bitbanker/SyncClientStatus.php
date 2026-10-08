<?php

namespace App\Console\Commands\Bitbanker;

use App\Models\BitbankerClient;
use App\Services\Integrations\Bitbanker\BitbankerClientService;
use Illuminate\Console\Command;

/**
 * php artisan bitbanker:sync-client-status [--minutes=5]
 *
 * Переопрашивает `GET /api/v2/partner-clients` для клиентов BitBanker, у которых
 * `last_synced_at` старше `--minutes` минут — подстраховка на случай, если событийный
 * вебхук (`sbp_client_permission_changed`) не дошёл: у него, в отличие от
 * инвойсного `invoices_webhook`, нет политики ретраев — отправка одной попыткой с
 * таймаутом 3 секунды, а GET /api/v2/partner-clients всегда отдаёт актуальный статус. Обрабатывает
 * все записи без исключения — отдельно частить опрос уже одобренных клиентов
 * (на случай отзыва доступа без вебхука) не нужно: обычный интервал
 * запуска команды уже покрывает оба случая.
 */
class SyncClientStatus extends Command
{
    protected $signature = 'bitbanker:sync-client-status {--minutes=5 : Переопрашивать клиентов, которых не синхронизировали дольше N минут}';

    protected $description = 'Переопросить статус клиентов BitBanker, которые давно не синхронизировались';

    public function handle(BitbankerClientService $service): int
    {
        $staleBefore = now()->subMinutes((int) $this->option('minutes'));

        $clients = BitbankerClient::query()
            ->where(function ($query) use ($staleBefore) {
                $query->whereNull('last_synced_at')->orWhere('last_synced_at', '<=', $staleBefore);
            })
            ->get();

        foreach ($clients as $client) {
            $service->refreshStatus($client);
        }

        $this->info("Переопрошено клиентов BitBanker: {$clients->count()}.");

        return self::SUCCESS;
    }
}
