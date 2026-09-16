<?php

namespace App\Console\Commands\Providers;

use App\Console\Commands\Providers\Concerns\DescribesSyncErrors;
use App\Enums\ActiveStatus;
use App\Enums\DiscrepancyStatus;
use App\Enums\DiscrepancyType;
use App\Models\CardProduct;
use App\Models\CardProvider;
use App\Models\ProviderDiscrepancy;
use App\Services\Integrations\ProviderIntegrationResolver;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
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
 * Без `--sync` ничего не создаёт и не меняет в самих «Карточных продуктах» автоматически —
 * цена там задаётся вручную и осознанно. С `--sync`:
 * - на каждый новый продукт провайдера заводится {@see CardProduct} — неактивным (`active = false`),
 *   с нулевой ценой/себестоимостью (их API не отдаёт) — остаётся проставить цену и включить вручную;
 * - у всех продуктов (и только что созданных, и уже существовавших) обновляются лимиты
 *   выпуска/пополнения (`issue_min_amount`, `issue_max_amount`, `topup_min_amount`, `topup_max_amount`)
 *   из `issueMinAmount`/`issueMaxAmount`/`topUpMinAmount`/`topUpMaxAmount` ответа провайдера —
 *   это факты провайдера, а не решение админа, поэтому обновляются безусловно (в отличие от цены/себестоимости).
 */
class SyncCardCatalog extends Command
{
    use DescribesSyncErrors;

    protected $signature = 'providers:sync-card-catalog {--sync : Создавать «Карточные продукты» для новых кодов провайдера и обновлять лимиты выпуска/пополнения у всех продуктов}';

    protected $description = 'Сверить каталог продуктов провайдеров с нашими «Карточными продуктами»';

    public function handle(): int
    {
        $sync = (bool) $this->option('sync');
        $flagged = 0;
        $created = 0;
        $updated = 0;
        $failed = 0;

        foreach (CardProvider::where('status', ActiveStatus::Active)->get() as $provider) {
            try {
                $catalog = ProviderIntegrationResolver::for($provider)->fetchProductCatalog();
            } catch (Throwable $e) {
                $failed++;
                $this->warn("Провайдер «{$provider->name}»: {$this->describeError($e)}");

                continue;
            }

            $catalogByCode = collect($catalog)->filter(fn (array $item) => $item['code'] !== '')->keyBy('code');
            $providerCodes = $catalogByCode->keys()->all();

            $allOurCodes = CardProduct::where('provider_id', $provider->id)->pluck('provider_product_code')->filter()->all();
            $activeOurCodes = CardProduct::where('provider_id', $provider->id)->where('active', true)->pluck('provider_product_code')->filter()->all();

            foreach (array_diff($providerCodes, $allOurCodes) as $newCode) {
                $wasCreated = false;

                if ($sync) {
                    $this->syncProductFromCatalog($provider, $catalogByCode[$newCode]);
                    $wasCreated = true;
                    $created++;
                }

                if ($this->hasOpenDiscrepancy($provider, DiscrepancyType::NewProviderProduct, $newCode)) {
                    continue;
                }

                $note = $wasCreated
                    ? "У провайдера «{$provider->name}» появился новый продукт «{$newCode}» — автоматически создан «Карточный продукт» (неактивен, нужно проставить цену и активировать)."
                    : "У провайдера «{$provider->name}» появился продукт «{$newCode}», которого нет в «Карточных продуктах».";

                ProviderDiscrepancy::create([
                    'provider_id' => $provider->id,
                    'type' => DiscrepancyType::NewProviderProduct,
                    'note' => $note,
                    'status' => DiscrepancyStatus::Open,
                ]);
                $flagged++;
            }

            if ($sync) {
                foreach (array_intersect($providerCodes, $allOurCodes) as $existingCode) {
                    if ($this->syncProductFromCatalog($provider, $catalogByCode[$existingCode], updateOnly: true)) {
                        $updated++;
                    }
                }
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

        $this->info(
            "Заведено расхождений: {$flagged}."
            . ($sync ? " Создано «Карточных продуктов»: {$created}. Обновлены лимиты у: {$updated}." : '')
            . ($failed > 0 ? " Ошибок: {$failed}." : '')
        );

        return self::SUCCESS;
    }

    /**
     * Создаёт неактивный «Карточный продукт» по данным из каталога провайдера, либо — если продукт с таким
     * `provider_product_code` у этого провайдера уже есть — обновляет только его лимиты выпуска/пополнения,
     * не трогая цену, активность и другие поля, которые задаются вручную. Цена (`price_rub`) и
     * себестоимость (`provider_issue_cost_usd`) нового продукта остаются 0 — их надо проставить
     * вручную перед активацией (их CardsPro не отдаёт).
     *
     * @param  array{code: string, name: ?string, currency: ?string, issue_min_amount: ?float, issue_max_amount: ?float, topup_min_amount: ?float, topup_max_amount: ?float, raw: array<string, mixed>}  $catalogItem
     * @return bool обновились ли лимиты уже существующего продукта (игнорируется, если $updateOnly = false)
     */
    protected function syncProductFromCatalog(CardProvider $provider, array $catalogItem, bool $updateOnly = false): bool
    {
        $limits = [
            'issue_min_amount' => $catalogItem['issue_min_amount'],
            'issue_max_amount' => $catalogItem['issue_max_amount'],
            'topup_min_amount' => $catalogItem['topup_min_amount'],
            'topup_max_amount' => $catalogItem['topup_max_amount'],
        ];

        $product = CardProduct::where('provider_id', $provider->id)
            ->where('provider_product_code', $catalogItem['code'])
            ->first();

        if ($product) {
            $product->update($limits);

            return true;
        }

        if ($updateOnly) {
            return false;
        }

        CardProduct::create($limits + [
            'provider_id' => $provider->id,
            'provider_product_code' => $catalogItem['code'],
            'key' => Str::slug("{$provider->code}-{$catalogItem['code']}"),
            'name' => $catalogItem['name'] ?: "{$provider->name} {$catalogItem['code']}",
            'currency' => $catalogItem['currency'] ?: 'USD',
            'active' => false,
        ]);

        return false;
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
