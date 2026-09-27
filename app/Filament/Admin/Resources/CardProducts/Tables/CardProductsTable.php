<?php

namespace App\Filament\Admin\Resources\CardProducts\Tables;

use App\Models\CardProduct;
use App\Models\Setting;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Number;

class CardProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort(fn ($query, string $direction) => $query->orderByDesc('active')->orderBy('sort', $direction))
            ->reorderable('sort')
            ->emptyStateHeading('Карточных продуктов пока нет')
            ->emptyStateDescription('Добавьте первый продукт, который клиенты смогут выбрать при выпуске карты.')
            ->emptyStateIcon('heroicon-o-rectangle-stack')
            ->emptyStateActions([
                CreateAction::make()->label('Добавить продукт'),
            ])
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Название')
                    ->description(fn (CardProduct $record) => $record->key)
                    ->searchable(),
                TextColumn::make('provider.name')
                    ->label('Провайдер'),
                TextColumn::make('currency')
                    ->label('Валюта'),
                TextColumn::make('limits')
                    ->label('Лимиты')
                    // Сырое состояние должно быть непустым, иначе Filament считает ячейку пустой до
                    // вызова formatStateUsing() (атрибута 'limits' в базе нет, настоящее значение считается ниже).
                    ->state(fn (CardProduct $record) => (string) $record->getKey())
                    ->formatStateUsing(fn (CardProduct $record) => self::formatLimits($record))
                    ->html()
                    ->toggleable(),
                TextColumn::make('costs')
                    ->label('Стоимости')
                    ->state(fn (CardProduct $record) => (string) $record->getKey())
                    ->formatStateUsing(fn (CardProduct $record) => self::formatCosts($record))
                    ->html(),
                TextColumn::make('wallets')
                    ->label('Apple/GooglePay')
                    ->state(fn (CardProduct $record) => (string) $record->getKey())
                    ->formatStateUsing(fn (CardProduct $record) => self::formatWallets($record))
                    ->html(),
                IconColumn::make('active')
                    ->label('Статус')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('provider_id')
                    ->label('Провайдер')
                    ->relationship('provider', 'name'),
                TernaryFilter::make('active')
                    ->label('Активен'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * Лимиты выпуска и пополнения в две строки: «Выпуск: 1–100 000 $» / «Пополнение: 1–100 000 $».
     */
    protected static function formatLimits(CardProduct $record): string
    {
        $issue = self::formatRange($record->issue_min_amount, $record->issue_max_amount);
        $topup = self::formatRange($record->topup_min_amount, $record->topup_max_amount);

        if ($issue === null && $topup === null) {
            return '—';
        }

        return implode('<br>', [
            'Выпуск: '.($issue ?? '—'),
            'Пополнение: '.($topup ?? '—'),
        ]);
    }

    protected static function formatRange(?string $min, ?string $max): ?string
    {
        if ($min === null && $max === null) {
            return null;
        }

        $range = ($min !== null ? number_format((float) $min, 0, ',', ' ') : '—')
            .'–'
            .($max !== null ? number_format((float) $max, 0, ',', ' ') : '—');

        return $range.' $';
    }

    /**
     * Цена продажи, себестоимость и прибыль — тремя строками в одной ячейке, в рублях и в долларах через «/». Себестоимость и прибыль —
     * см. CardProduct::costUsd()/getCostRubAttribute()/getEstimatedProfitAttribute().
     */
    protected static function formatCosts(CardProduct $record): string
    {
        $rawRate = (float) (Setting::get('currency_rate_usd') ?? 0);

        $priceRub = (float) $record->price_rub;
        $priceUsd = $rawRate > 0 ? $priceRub / $rawRate : 0.0;

        $costRub = (float) $record->cost_rub;
        $costUsd = $record->costUsd();

        $profitRub = (float) $record->estimated_profit;
        $profitUsd = $priceUsd - $costUsd;

        return implode('<br>', [
            self::formatDualAmount($priceRub, $priceUsd),
            sprintf('<span class="text-danger-600">−%s</span>', self::formatDualAmount($costRub, $costUsd)),
            sprintf('<span class="text-success-600">%s</span>', self::formatDualAmount($profitRub, $profitUsd)),
        ]);
    }

    /**
     * «12 000 ₽ / 130 $» — рублёвая и долларовая суммы через «/» в одной строке.
     */
    protected static function formatDualAmount(float $rub, float $usd): string
    {
        return Number::currency($rub, 'RUB').' / '.Number::currency($usd, 'USD');
    }

    /**
     * Два индикатора: Apple Pay и Google Pay — галочка либо крестик за каждый.
     */
    protected static function formatWallets(CardProduct $record): string
    {
        return sprintf(
            '<span class="%s">%s</span> <span class="%s">%s</span>',
            $record->apple_pay_enabled ? 'text-success-600' : 'text-danger-600',
            $record->apple_pay_enabled ? '✓' : '✗',
            $record->google_pay_enabled ? 'text-success-600' : 'text-danger-600',
            $record->google_pay_enabled ? '✓' : '✗',
        );
    }
}
