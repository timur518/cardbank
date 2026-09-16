<?php

namespace App\Filament\Admin\Resources\CardProviders\Tables;

use App\Enums\ActiveStatus;
use App\Enums\ProviderEnvironment;
use App\Models\CardProvider;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CardProvidersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->emptyStateHeading('Провайдеры пока не подключены')
            ->emptyStateDescription('Добавьте первого провайдера, через которого будут выпускаться карты.')
            ->emptyStateIcon('heroicon-o-building-library')
            ->emptyStateActions([
                CreateAction::make()->label('Добавить провайдера'),
            ])
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Название')
                    ->description(fn (CardProvider $record) => $record->code)
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Статус')
                    ->badge(),
                TextColumn::make('environment')
                    ->label('Окружение')
                    ->badge(),
                TextColumn::make('reserve_balance_usd')
                    ->label('Остаток резерва')
                    ->money('USD')
                    ->sortable(),
                IconColumn::make('connection_state')
                    ->label('Связь с провайдером')
                    // TODO: подключить реальную проверку связи (последние вебхуки/ошибки), пока считаем «в норме», если провайдер активен.
                    ->state(fn (CardProvider $record) => $record->status === ActiveStatus::Active)
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('gray'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Статус')
                    ->options(ActiveStatus::class),
                SelectFilter::make('environment')
                    ->label('Окружение')
                    ->options(ProviderEnvironment::class),
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
