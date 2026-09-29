<?php

namespace App\Filament\Admin\Resources\CardTransactions\Tables;

use App\Enums\CardTransactionStatus;
use App\Enums\CardTransactionType;
use App\Models\Card;
use App\Models\CardTransaction;
use App\Models\Merchant;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
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
                ViewColumn::make('merchantRecord.logo_svg')
                    ->label('')
                    ->view('filament.tables.columns.card-transaction-merchant-logo'),
                TextColumn::make('merchantRecord.name')
                    ->label('Мерчант')
                    ->description(fn (CardTransaction $record) => $record->merchant)
                    ->placeholder(fn (CardTransaction $record) => $record->merchant ?: '—')
                    ->searchable(['merchant']),
                TextColumn::make('type')
                    ->label('Тип операции')
                    ->badge(),
                ViewColumn::make('amount')
                    ->label('Сумма')
                    ->view('filament.tables.columns.card-transaction-amounts')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Статус')
                    ->badge(),
                TextColumn::make('card.user.email')
                    ->label('Пользователь')
                    ->description(fn (CardTransaction $record) => $record->card?->masked_number),
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
                SelectFilter::make('merchant_id')
                    ->label('Мерчант')
                    ->relationship('merchantRecord', 'name')
                    ->getOptionLabelFromRecordUsing(fn (Merchant $record) => $record->name)
                    ->searchable(),
            ])
            // Раздел заполняется автоматически по данным от карточного провайдера — ручное создание,
            // редактирование и удаление записей не предусмотрено.
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
