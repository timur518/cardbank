<?php

namespace App\Filament\Admin\Resources\Merchants\Tables;

use App\Enums\MerchantCategory;
use App\Models\Merchant;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class MerchantsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->emptyStateHeading('Мерчантов пока нет')
            ->emptyStateDescription('Добавьте мерчанта, чтобы транзакции по нему определялись автоматически по коду в описании операции.')
            ->emptyStateIcon('heroicon-o-building-storefront')
            ->emptyStateActions([
                CreateAction::make()->label('Добавить мерчанта'),
            ])
            ->columns([
                ViewColumn::make('logo')
                    ->label('')
                    ->view('filament.tables.columns.merchant-logo'),
                TextColumn::make('name')
                    ->label('Название')
                    ->description(fn (Merchant $record) => $record->code)
                    ->searchable(['name', 'code']),
                TextColumn::make('category')
                    ->label('Категория')
                    ->badge(),
                IconColumn::make('is_active')
                    ->label('Активен')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('Добавлен')
                    ->dateTime('d.m.Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label('Категория')
                    ->options(MerchantCategory::class),
                TernaryFilter::make('is_active')
                    ->label('Активность'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
