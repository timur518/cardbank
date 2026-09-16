<?php

namespace App\Filament\Admin\Resources\Incomes\Schemas;

use App\Models\Income;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class IncomeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Поступление')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('id')->label('ID'),
                        TextEntry::make('created_at')->label('Дата')->dateTime('d.m.Y H:i'),
                        TextEntry::make('type')->label('Тип поступления')->badge(),
                        TextEntry::make('amount')->label('Сумма')->money(fn (Income $record) => $record->currency),
                        TextEntry::make('payment_status')->label('Статус платежа')->badge(),
                        TextEntry::make('payment_transaction_id')->label('ID транзакции')->placeholder('—'),
                        TextEntry::make('user.email')->label('Пользователь')->placeholder('—'),
                        TextEntry::make('card.masked_number')->label('Карта')->placeholder('—'),
                        TextEntry::make('paymentMethod.name')->label('Способ оплаты')->placeholder('—'),
                        TextEntry::make('comment')->label('Комментарий')->placeholder('—')->columnSpanFull(),
                        TextEntry::make('creator.name')->label('Кто внёс вручную')->placeholder('—'),
                    ]),
            ]);
    }
}
