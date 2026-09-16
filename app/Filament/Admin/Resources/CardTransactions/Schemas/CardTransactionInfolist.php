<?php

namespace App\Filament\Admin\Resources\CardTransactions\Schemas;

use App\Models\CardTransaction;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class CardTransactionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('card.masked_number')->label('Карта'),
                TextEntry::make('card.user.email')->label('Пользователь'),
                TextEntry::make('type')->label('Тип операции'),
                TextEntry::make('amount')
                    ->label('Сумма транзакции')
                    ->money(fn (CardTransaction $record) => $record->currency)
                    ->helperText('Видит клиент в истории платежей.'),
                TextEntry::make('cost_amount')
                    ->label('Себестоимость транзакции')
                    ->money(fn (CardTransaction $record) => $record->currency)
                    ->placeholder('—')
                    ->helperText('Реальная стоимость операции для компании.'),
                TextEntry::make('commission_amount')
                    ->label('Наша комиссия')
                    ->money(fn (CardTransaction $record) => $record->currency)
                    ->placeholder('—')
                    ->helperText('Снэпшот для подсчёта прибыли с операции.'),
                TextEntry::make('merchant')->label('Продавец')->placeholder('—'),
                TextEntry::make('status')->label('Статус')->badge(),
                TextEntry::make('decline_reason')->label('Причина отказа')->placeholder('—'),
                TextEntry::make('provider_tx_id')->label('ID операции у провайдера')->placeholder('—'),
                TextEntry::make('occurred_at')->label('Дата операции')->dateTime('d.m.Y H:i'),
            ]);
    }
}
