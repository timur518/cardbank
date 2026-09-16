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
                TextEntry::make('amount')->label('Сумма')->money(fn (CardTransaction $record) => $record->currency),
                TextEntry::make('merchant')->label('Продавец')->placeholder('—'),
                TextEntry::make('status')->label('Статус')->badge(),
                TextEntry::make('decline_reason')->label('Причина отказа')->placeholder('—'),
                TextEntry::make('provider_tx_id')->label('ID операции у провайдера')->placeholder('—'),
                TextEntry::make('occurred_at')->label('Дата операции')->dateTime('d.m.Y H:i'),
            ]);
    }
}
