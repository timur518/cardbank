<?php

namespace App\Filament\Admin\Pages;

use App\Models\CardProduct;
use App\Models\Merchant;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use UnitEnum;

/**
 * Матрица «мерчант × активный карточный продукт» с рейтингом успешных платежей
 * (см. App\Console\Commands\Providers\SyncMerchantProductRates и
 * App\Models\MerchantProductRate). Неактивные карточные продукты в колонки не
 * попадают, но их рейтинг по-прежнему хранится в базе — при активации продукта
 * данные для него уже будут на месте.
 */
class MerchantProductRatesPage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static string|UnitEnum|null $navigationGroup = 'Настройки';

    protected static ?string $navigationLabel = 'Рейтинг платежей по мерчантам';

    protected static ?string $title = 'Рейтинг платежей по мерчантам';

    protected static ?int $navigationSort = 5;

    protected string $view = 'filament.admin.pages.merchant-product-rates';

    /**
     * @return Collection<int, CardProduct>
     */
    public function getActiveProducts(): Collection
    {
        return CardProduct::where('active', true)->orderBy('name')->get();
    }

    /**
     * @return Collection<int, array{merchant: Merchant, rates: array<int, float|null>}>
     */
    public function getRows(): Collection
    {
        $products = $this->getActiveProducts();
        $productIds = $products->pluck('id');

        return Merchant::query()
            ->with(['productRates' => fn ($query) => $query->whereIn('card_product_id', $productIds)])
            ->orderBy('name')
            ->get()
            ->map(fn (Merchant $merchant) => [
                'merchant' => $merchant,
                'rates' => $products->mapWithKeys(
                    fn (CardProduct $product) => [
                        $product->id => $merchant->productRates->firstWhere('card_product_id', $product->id)?->rate,
                    ]
                )->all(),
            ]);
    }
}
