<?php

namespace App\Console\Commands\Providers;

use App\Console\Commands\Providers\Concerns\DescribesSyncErrors;
use App\Enums\CardProviderOperationStatus;
use App\Enums\DiscrepancyStatus;
use App\Enums\DiscrepancyType;
use App\Models\CardProviderOperation;
use App\Models\ProviderDiscrepancy;
use App\Services\CardProviderOperationResolver;
use App\Services\Integrations\ProviderIntegrationResolver;
use Illuminate\Console\Command;
use Throwable;

/**
 * php artisan providers:sync-pending-operations [--stuck-minutes=30]
 *
 * Опрашивает статус всех незавершённых {@see CardProviderOperation} (выпуск,
 * пополнение, вывод, блокировка — независимо от провайдера) и, если провайдер уже
 * определился с результатом, применяет его через {@see CardProviderOperationResolver}
 * — ровно так же, как это сделал бы вебхук, если бы дошёл. Операции, которые провайдер
 * дольше `--stuck-minutes` держит в подвешенном статусе, попадают в
 * {@see ProviderDiscrepancy} (тип `stuck_operation`) — это уже не наша забывчивость,
 * а повод человеку разбираться с провайдером напрямую.
 */
class SyncPendingOperations extends Command
{
    use DescribesSyncErrors;

    protected $signature = 'providers:sync-pending-operations {--stuck-minutes=30 : Через сколько минут без ответа провайдера заводить расхождение}';

    protected $description = 'Опросить статус незавершённых операций провайдера и применить результат, если вебхук потерялся';

    public function handle(CardProviderOperationResolver $resolver): int
    {
        $stuckBefore = now()->subMinutes((int) $this->option('stuck-minutes'));
        $resolved = 0;
        $stuck = 0;
        $failed = 0;

        $operations = CardProviderOperation::with('provider')
            ->where('status', CardProviderOperationStatus::Pending)
            ->get();

        foreach ($operations as $operation) {
            try {
                $result = ProviderIntegrationResolver::for($operation->provider)->fetchOperationStatus($operation->request_id);
            } catch (Throwable $e) {
                $failed++;
                $this->warn("Операция #{$operation->id} ({$operation->request_id}): {$this->describeError($e)}");

                continue;
            }

            if ($result['status'] === 'pending') {
                if ($operation->created_at <= $stuckBefore && ! $this->hasOpenStuckDiscrepancy($operation)) {
                    ProviderDiscrepancy::create([
                        'provider_id' => $operation->provider_id,
                        'type' => DiscrepancyType::StuckOperation,
                        'note' => "Операция «{$operation->type->getLabel()}» #{$operation->id} (request_id={$operation->request_id}) больше {$this->option('stuck-minutes')} минут не получает финального статуса от провайдера.",
                        'card_id' => $operation->card_id,
                        'status' => DiscrepancyStatus::Open,
                    ]);
                    $stuck++;
                }

                continue;
            }

            try {
                $resolver->resolve($operation, $result['status'], $result['raw']);
            } catch (Throwable $e) {
                $failed++;
                $this->warn("Операция #{$operation->id} ({$operation->request_id}): {$this->describeError($e)}");

                continue;
            }

            $resolved++;
        }

        $this->info("Подтверждено операций: {$resolved}. Зависло: {$stuck}." . ($failed > 0 ? " Ошибок: {$failed}." : ''));

        return self::SUCCESS;
    }

    protected function hasOpenStuckDiscrepancy(CardProviderOperation $operation): bool
    {
        return ProviderDiscrepancy::where('provider_id', $operation->provider_id)
            ->where('type', DiscrepancyType::StuckOperation)
            ->where('status', DiscrepancyStatus::Open)
            ->where('note', 'like', "%#{$operation->id} (request_id=%")
            ->exists();
    }
}
