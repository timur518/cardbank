<?php

namespace App\Filament\Admin\Resources\PaymentMethods\Tables;

use App\Enums\ActiveStatus;
use App\Enums\PaymentMethodType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PaymentMethodsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->emptyStateHeading('Способы приёма платежей не настроены')
            ->emptyStateDescription('Добавьте первый способ, которым клиенты смогут платить: перевод, криптовалюта, карта или кошелёк.')
            ->emptyStateIcon('heroicon-o-credit-card')
            ->emptyStateActions([
                CreateAction::make()->label('Добавить способ оплаты'),
            ])
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Название')
                    ->searchable(),
                TextColumn::make('type')
                    ->label('Тип')
                    ->badge(),
                TextColumn::make('currency')
                    ->label('Валюта'),
                TextColumn::make('status')
                    ->label('Статус')
                    ->badge(),
                TextColumn::make('fee_percent')
                    ->label('Комиссия')
                    ->suffix('%'),
                TextColumn::make('markup_percent')
                    ->label('Наша наценка')
                    ->suffix('%'),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Тип')
                    ->options(PaymentMethodType::class),
                SelectFilter::make('status')
                    ->label('Статус')
                    ->options(ActiveStatus::class),
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
