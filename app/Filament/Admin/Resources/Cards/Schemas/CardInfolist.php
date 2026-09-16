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
                    ->columns(3)
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

                Section::make('Реквизиты')
                    ->description('Полный номер, срок действия и код видны только по отдельному праву доступа. Используйте кнопку «Показать реквизиты» вверху страницы — она потребует указать причину просмотра.')
                    ->visible(fn () => ! auth()->user()?->can('view_card_sensitive_data'))
                    ->schema([
                        TextEntry::make('placeholder')
                            ->label('')
                            ->state('Скрыто')
                            ->color('gray'),
                    ]),
            ]);
    }
}
