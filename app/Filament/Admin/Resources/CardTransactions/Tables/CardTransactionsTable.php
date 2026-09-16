<?php

namespace App\Filament\Admin\Resources\CardTransactions\Tables;

use App\Enums\CardTransactionStatus;
use App\Enums\CardTransactionType;
use App\Models\Card;
use App\Models\CardTransaction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CardTransactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->emptyStateHeading('Транзакций пока нет')
            ->emptyStateDescription('Раздел заполняется автоматически по данным от карточного провайдера — как только появятся операции по картам, они будут показаны здесь.')
            ->emptyStateIcon('heroicon-o-arrows-right-left')
            ->defaultSort('occurred_at', 'desc')
            ->columns([
                TextColumn::make('id')->label('ID')->sortable(),
                TextColumn::make('card.masked_number')->label('Карта'),
                TextColumn::make('card.user.email')->label('Пользователь'),
                TextColumn::make('type')
                    ->label('Тип операции')
                    ->badge(),
                TextColumn::make('amount')
                    ->label('Сумма')
                    ->money(fn (CardTransaction $record) => $record->currency)
                    ->sortable(),
                TextColumn::make('cost_amount')
                    ->label('Себестоимость')
                    ->money(fn (CardTransaction $record) => $record->currency)
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('commission_amount')
                    ->label('Наша комиссия')
                    ->money(fn (CardTransaction $record) => $record->currency)
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('status')
                    ->label('Статус')
                    ->badge(),
                TextColumn::make('occurred_at')
                    ->label('Дата')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Тип операции')
                    ->options(CardTransactionType::class),
                SelectFilter::make('status')
                    ->label('Статус')
                    ->options(CardTransactionStatus::class),
                SelectFilter::make('card_id')
                    ->label('Карта')
                    ->relationship('card', 'id')
                    ->getOptionLabelFromRecordUsing(fn (Card $record) => $record->masked_number)
                    ->searchable(),
            ])
            // Раздел заполняется автоматически по данным от карточного провайдера — ручное создание,
            // редактирование и удаление записей не предусмотрено.
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
