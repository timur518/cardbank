<?php

namespace App\Console\Commands\Providers;

use App\Enums\ActiveStatus;
use App\Enums\DiscrepancyStatus;
use App\Enums\DiscrepancyType;
use App\Models\CardProduct;
use App\Models\CardProvider;
use App\Models\ProviderDiscrepancy;
use App\Services\Integrations\ProviderIntegrationResolver;
use Illuminate\Console\Command;
use Throwable;

/**
 * php artisan providers:sync-card-catalog
 *
 * Сверяет каталог продуктов провайдера (`GET /products`) с нашими «Карточными
 * продуктами» (по `provider_product_code`) у всех активных провайдеров и заводит
 * {@see ProviderDiscrepancy}, когда:
 * - у провайдера появился продукт, которого нет в нашем каталоге (`new_provider_product`);
 * - продукт, который используется в активном «Карточном продукте», пропал из каталога
 *   провайдера (`provider_product_missing`) — это может означать, что выпуск новых
 *   карт по нему перестанет работать.
 *
 * Ничего не создаёт и не меняет в самих «Карточных продуктах» автоматически — цена и
 * лимиты там задаются вручную и осознанно.
 */
class SyncCardCatalog extends Command
{
    protected $signature = 'providers:sync-card-catalog';

    protected $description = 'Сверить каталог продуктов провайдеров с нашими «Карточными продуктами»';

    public function handle(): int
    {
        $flagged = 0;
        $failed = 0;

        foreach (CardProvider::where('status', ActiveStatus::Active)->get() as $provider) {
            try {
                $catalog = ProviderIntegrationResolver::for($provider)->fetchProductCatalog();
            } catch (Throwable $e) {
                $failed++;
                $this->warn("Провайдер «{$provider->name}»: {$e->getMessage()}");

                continue;
            }

            $providerCodes = collect($catalog)->pluck('code')->filter()->all();

            $allOurCodes = CardProduct::where('provider_id', $provider->id)->pluck('provider_product_code')->filter()->all();
            $activeOurCodes = CardProduct::where('provider_id', $provider->id)->where('active', true)->pluck('provider_product_code')->filter()->all();

            foreach (array_diff($providerCodes, $allOurCodes) as $newCode) {
                if ($this->hasOpenDiscrepancy($provider, DiscrepancyType::NewProviderProduct, $newCode)) {
                    continue;
                }

                ProviderDiscrepancy::create([
                    'provider_id' => $provider->id,
                    'type' => DiscrepancyType::NewProviderProduct,
                    'note' => "У провайдера «{$provider->name}» появился продукт «{$newCode}», которого нет в «Карточных продуктах».",
                    'status' => DiscrepancyStatus::Open,
                ]);
                $flagged++;
            }

            foreach (array_diff($activeOurCodes, $providerCodes) as $missingCode) {
                if ($this->hasOpenDiscrepancy($provider, DiscrepancyType::ProviderProductMissing, $missingCode)) {
                    continue;
                }

                ProviderDiscrepancy::create([
                    'provider_id' => $provider->id,
                    'type' => DiscrepancyType::ProviderProductMissing,
                    'note' => "Продукт «{$missingCode}» используется в активном «Карточном продукте», но пропал из каталога провайдера «{$provider->name}».",
                    'status' => DiscrepancyStatus::Open,
                ]);
                $flagged++;
            }
        }

        $this->info("Заведено расхождений: {$flagged}." . ($failed > 0 ? " Ошибок: {$failed}." : ''));

        return self::SUCCESS;
    }

    protected function hasOpenDiscrepancy(CardProvider $provider, DiscrepancyType $type, string $code): bool
    {
        return ProviderDiscrepancy::where('provider_id', $provider->id)
            ->where('type', $type)
            ->where('status', DiscrepancyStatus::Open)
            ->where('note', 'like', "%«{$code}»%")
            ->exists();
    }
}
