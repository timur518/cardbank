<?php

namespace App\Filament\Admin\Pages;

use App\Models\CardProduct;
use App\Models\Merchant;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use UnitEnum;

/**
 * Матрица «мерчант × активный карточный продукт»: рейтинг успешных платежей от
 * CardsPro (см. App\Console\Commands\Providers\SyncMerchantProductRates) и, второй
 * строкой под ним, собственная статистика по фактическим транзакциям клиентов —
 * успешно/отказ/собственный рейтинг (см. App\Models\MerchantProductRate::recordOutcome()).
 * Неактивные карточные продукты в колонки не попадают, но их данные по-прежнему
 * хранятся в базе.
 */
class MerchantProductRatesPage extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static string|UnitEnum|null $navigationGroup = 'Карты';

    protected static ?string $navigationLabel = 'Рейтинг платежей по мерчантам';

    protected static ?string $title = 'Рейтинг платежей по мерчантам';

    protected static ?int $navigationSort = 3;

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            EmbeddedTable::make(),
        ]);
    }

    public function table(Table $table): Table
    {
        $products = CardProduct::where('active', true)->orderBy('name')->get();
        $productIds = $products->pluck('id');

        return $table
            ->query(
                Merchant::query()->with([
                    'productRates' => fn ($query) => $query->whereIn('card_product_id', $productIds),
                ])
            )
            ->defaultSort('name')
            ->emptyStateHeading('Мерчантов пока нет')
            ->emptyStateDescription('Добавьте мерчантов в разделе «Настройки» → «Мерчанты».')
            ->emptyStateIcon('heroicon-o-building-storefront')
            ->columns([
                ViewColumn::make('logo')
                    ->label('')
                    ->view('filament.tables.columns.merchant-logo'),
                TextColumn::make('name')
                    ->label('Мерчант')
                    ->searchable(),
                ...$products->map(
                    fn (CardProduct $product) => TextColumn::make("product_{$product->id}")
                        ->label($product->name)
                        ->alignEnd()
                        ->state(function (Merchant $record) use ($product) {
                            $stat = $record->productRates->firstWhere('card_product_id', $product->id);

                            return $stat?->rate !== null
                                ? number_format($stat->rate * 100, 0).'%'
                                : '—';
                        })
                        ->description(function (Merchant $record) use ($product) {
                            $stat = $record->productRates->firstWhere('card_product_id', $product->id);

                            if (! $stat || ($stat->success_count === 0 && $stat->decline_count === 0)) {
                                return null;
                            }

                            return "{$stat->success_count}/{$stat->decline_count}/"
                                .number_format($stat->own_rate * 100, 0).'%';
                        })
                )->all(),
            ]);
    }
}
