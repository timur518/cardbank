<?php

namespace App\Filament\Admin\Resources\PromoCodes\Tables;

use App\Enums\PromoCodeScope;
use App\Models\PromoCode;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PromoCodesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Промокодов пока нет')
            ->emptyStateDescription('Добавьте первый промокод для скидок на выпуск или пополнение карты.')
            ->emptyStateIcon('heroicon-o-ticket')
            ->emptyStateActions([
                CreateAction::make()->label('Добавить промокод'),
            ])
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('code')
                    ->label('Код')
                    ->searchable(),
                TextColumn::make('scope')
                    ->label('Область действия')
                    ->badge(),
                TextColumn::make('discount_label')
                    ->label('Скидка'),
                TextColumn::make('allowed_products_label')
                    ->label('Ограничение по продуктам')
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('single_use')
                    ->label('Единоразовое')
                    ->boolean(),
                TextColumn::make('validity_label')
                    ->label('Срок действия'),
                TextColumn::make('used_count')
                    ->label('Использовано')
                    ->state(fn (PromoCode $record) => $record->max_uses ? "{$record->used_count} из {$record->max_uses}" : (string) $record->used_count),
                ToggleColumn::make('active')
                    ->label('Активен'),
            ])
            ->filters([
                SelectFilter::make('scope')
                    ->label('Область действия')
                    ->options(PromoCodeScope::class),
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
