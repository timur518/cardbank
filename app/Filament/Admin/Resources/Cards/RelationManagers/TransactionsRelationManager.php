<?php

namespace App\Filament\Admin\Resources\Cards\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'transactions';

    protected static ?string $title = 'Транзакции по карте';

    protected static ?string $modelLabel = 'транзакция';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('type')
            ->defaultSort('occurred_at', 'desc')
            ->emptyStateHeading('Операций по этой карте пока нет')
            ->emptyStateDescription('История покупок, пополнений, комиссий и возвратов появится здесь автоматически по данным от провайдера.')
            ->emptyStateIcon('heroicon-o-arrows-right-left')
            ->columns([
                TextColumn::make('type')
                    ->label('Тип')
                    ->badge(),
                TextColumn::make('amount')
                    ->label('Сумма')
                    ->money(fn ($record) => $record->currency),
                TextColumn::make('cost_amount')
                    ->label('Без комиссии')
                    ->money(fn ($record) => $record->currency)
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('commission_amount')
                    ->label('Комиссия CardsPro')
                    ->money(fn ($record) => $record->currency)
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('merchantRecord.name')
                    ->label('Мерчант')
                    ->description(fn ($record) => $record->merchant)
                    ->placeholder(fn ($record) => $record->merchant ?: '—'),
                TextColumn::make('status')
                    ->label('Статус')
                    ->badge(),
                TextColumn::make('occurred_at')
                    ->label('Дата')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->headerActions([
                // Раздел заполняется автоматически, ручное создание не предусмотрено.
            ])
            ->recordActions([]);
    }
}
