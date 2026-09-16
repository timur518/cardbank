<?php

namespace App\Filament\Admin\Resources\CardProducts\Tables;

use App\Models\CardProduct;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CardProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
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
                TextColumn::make('price_rub')
                    ->label('Цена для клиента')
                    ->money('RUB')
                    ->sortable(),
                TextColumn::make('provider_issue_cost_usd')
                    ->label('Себестоимость')
                    ->money('USD')
                    ->sortable(),
                TextColumn::make('estimated_profit')
                    ->label('Расчётная прибыль')
                    ->money('RUB')
                    ->color(fn (CardProduct $record) => (float) $record->estimated_profit >= 0 ? 'success' : 'danger'),
                ToggleColumn::make('active')
                    ->label('Активен'),
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
}
