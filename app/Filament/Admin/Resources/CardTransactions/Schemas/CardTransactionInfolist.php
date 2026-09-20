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
                    ->label('Итоговая сумма (с комиссией)')
                    ->money(fn (CardTransaction $record) => $record->currency)
                    ->helperText('Списано с карты, видит клиент в истории платежей.'),
                TextEntry::make('cost_amount')
                    ->label('Сумма без комиссии')
                    ->money(fn (CardTransaction $record) => $record->currency)
                    ->placeholder('—')
                    ->helperText('Оригинальная сумма транзакции у эмитента, до комиссии CardsPro.'),
                TextEntry::make('commission_amount')
                    ->label('Комиссия CardsPro')
                    ->money(fn (CardTransaction $record) => $record->currency)
                    ->placeholder('—')
                    ->helperText('Комиссия провайдера за операцию: сумма без комиссии + комиссия = итоговая сумма.'),
                TextEntry::make('merchant')->label('Продавец (как прислал провайдер)')->placeholder('—'),
                TextEntry::make('merchantRecord.name')
                    ->label('Мерчант (из справочника)')
                    ->placeholder('Не определён')
                    ->helperText('Определяется автоматически по коду в поле "Продавец" — см. раздел "Мерчанты".'),
                TextEntry::make('status')->label('Статус')->badge(),
                TextEntry::make('decline_reason')->label('Причина отказа')->placeholder('—'),
                TextEntry::make('provider_tx_id')->label('ID операции у провайдера')->placeholder('—'),
                TextEntry::make('origin_tx_id')
                    ->label('ID исходного холда')
                    ->placeholder('—')
                    ->helperText('originTxId/originTxnId у CardsPro — если заполнен, расчёт был слит с этим холдом в одну запись.'),
                TextEntry::make('occurred_at')->label('Дата операции')->dateTime('d.m.Y H:i'),
            ]);
    }
}
