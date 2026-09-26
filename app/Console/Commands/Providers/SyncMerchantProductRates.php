<?php

namespace App\Console\Commands\Providers;

use App\Console\Commands\Providers\Concerns\DescribesSyncErrors;
use App\Enums\ActiveStatus;
use App\Models\CardProduct;
use App\Models\CardProvider;
use App\Models\Merchant;
use App\Models\MerchantProductRate;
use App\Services\Integrations\CardsPro\CardsProService;
use App\Services\Integrations\Contracts\CardProviderIntegration;
use Illuminate\Console\Command;
use Throwable;

/**
 * php artisan providers:sync-merchant-rates
 *
 * Раз в сутки опрашивает CardsPro `GET /products/search-by-merchant` (см.
 * https://docs.cardspro.com/api/cards/card-products-rate-by-merchant) по каждому
 * мерчанту из справочника {@see Merchant} и сохраняет рейтинг успешных платежей
 * (0..1) каждого карточного продукта у этого мерчанта в {@see MerchantProductRate}.
 *
 * Метод специфичен для CardsPro (не входит в общий контракт
 * {@see CardProviderIntegration}) — как и
 * везде в проекте, единственный подключённый провайдер сегодня — CardsPro, поэтому
 * {@see CardsProService} используется напрямую, а не через ProviderIntegrationResolver.
 *
 * Рейтинг сохраняется по всем продуктам провайдера (даже тем, для которых у нас нет
 * активного «Карточного продукта», и даже неактивным) — что показывать, решает уже
 * админ-таблица «Рейтинг платежей по мерчантам», а не сама синхронизация.
 */
class SyncMerchantProductRates extends Command
{
    use DescribesSyncErrors;

    protected $signature = 'providers:sync-merchant-rates';

    protected $description = 'Синхронизировать рейтинг успешных платежей карточных продуктов по мерчантам (CardsPro)';

    public function handle(): int
    {
        $providers = CardProvider::where('status', ActiveStatus::Active)->get();

        if ($providers->isEmpty()) {
            $this->warn('Нет активных провайдеров карт — синхронизация пропущена.');

            return self::SUCCESS;
        }

        $merchants = Merchant::all();

        if ($merchants->isEmpty()) {
            $this->warn('Справочник мерчантов пуст — синхронизация пропущена.');

            return self::SUCCESS;
        }

        $synced = 0;
        $failed = 0;

        foreach ($providers as $provider) {
            $service = CardsProService::for($provider);

            $productsByCode = CardProduct::where('provider_id', $provider->id)
                ->whereNotNull('provider_product_code')
                ->get()
                ->keyBy('provider_product_code');

            foreach ($merchants as $merchant) {
                try {
                    $rates = $service->getCardProductRatesByMerchant($merchant->providerSearchKey());
                } catch (Throwable $e) {
                    $failed++;
                    $this->warn("Мерчант «{$merchant->name}» (провайдер «{$provider->name}»): {$this->describeError($e)}");

                    continue;
                }

                foreach ($rates as $item) {
                    $productCode = (string) ($item['productCode'] ?? '');
                    $product = $productsByCode->get($productCode);

                    if ($product === null) {
                        continue;
                    }

                    MerchantProductRate::updateOrCreate(
                        ['merchant_id' => $merchant->id, 'card_product_id' => $product->id],
                        ['rate' => (float) ($item['rate'] ?? 0), 'synced_at' => now()],
                    );
                    $synced++;
                }
            }
        }

        $this->info("Обновлено ставок: {$synced}.".($failed > 0 ? " Ошибок: {$failed}." : ''));

        return self::SUCCESS;
    }
}
