<?php

namespace App\Filament\Admin\Resources\AdPlacements\Tables;

use App\Models\AdPlacement;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AdPlacementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Рекламных размещений пока нет')
            ->emptyStateDescription('Добавьте первую покупку рекламы, чтобы отслеживать её окупаемость.')
            ->emptyStateIcon('heroicon-o-speaker-wave')
            ->emptyStateActions([
                CreateAction::make()->label('Добавить размещение'),
            ])
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Название')
                    ->searchable(),
                TextColumn::make('channel')
                    ->label('Площадка')
                    ->searchable(),
                TextColumn::make('cost_amount')
                    ->label('Стоимость')
                    ->money('RUB')
                    ->sortable(),
                TextColumn::make('clicks_count')
                    ->label('Переходов')
                    ->sortable(),
                TextColumn::make('registrations_count')
                    ->label('Регистраций')
                    ->sortable(),
                TextColumn::make('revenue_amount')
                    ->label('Заработано')
                    ->money('RUB')
                    ->sortable(),
                TextColumn::make('roi_label')
                    ->label('Окупаемость')
                    ->color(fn (AdPlacement $record) => (float) $record->revenue_amount >= (float) $record->cost_amount ? 'success' : 'danger'),
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
