<?php

namespace App\Filament\Admin\Resources\Cards\Schemas;

use App\Models\Card;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CardInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Основное')
                    ->columnSpanFull()
                    ->columns(6)
                    ->schema([
                        TextEntry::make('user.email')->label('Владелец'),
                        TextEntry::make('provider.name')->label('Провайдер'),
                        TextEntry::make('cardProduct.name')->label('Продукт'),
                        TextEntry::make('masked_number')->label('Номер карты'),
                        TextEntry::make('status')
                            ->label('Статус')
                            ->badge(),
                        TextEntry::make('balance')->label('Баланс')->money(fn (Card $record) => $record->currency),
                        TextEntry::make('fee_debt')->label('Долг по комиссии')->money(fn (Card $record) => $record->currency),
                        TextEntry::make('issued_at')->label('Дата выпуска')->dateTime('d.m.Y H:i')->placeholder('—'),
                        TextEntry::make('closed_at')->label('Дата закрытия')->dateTime('d.m.Y H:i')->placeholder('—'),
                    ]),

                // TODO: временно реквизиты показываются в открытом виде без ограничения правами доступа —
                // кнопка «Показать реквизиты» в ViewCard закомментирована, вернуть проверку
                // права view_card_sensitive_data вместе с ней.
                Section::make('Реквизиты')
                    ->columnSpanFull()
                    ->columns(3)
                    ->schema([
                        TextEntry::make('card_number')->label('Номер карты')->placeholder('—'),
                        TextEntry::make('expiry')->label('Срок действия')->placeholder('—'),
                        TextEntry::make('cvv')->label('CVV')->placeholder('—'),
                    ]),
            ]);
    }
}
