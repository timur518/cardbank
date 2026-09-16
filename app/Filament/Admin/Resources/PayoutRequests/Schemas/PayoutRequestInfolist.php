<?php

namespace App\Filament\Admin\Resources\PayoutRequests\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PayoutRequestInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Заявка на выплату')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('id')->label('ID'),
                        TextEntry::make('partner.user.email')->label('Партнёр'),
                        TextEntry::make('amount_usd')->label('Сумма')->money('USD'),
                        TextEntry::make('destination')->label('Куда вывести')->badge(),
                        TextEntry::make('bank_name')->label('Банк')->placeholder('—'),
                        TextEntry::make('bank_card_number')->label('Номер карты')->placeholder('—'),
                        TextEntry::make('bank_card_holder')->label('Держатель карты')->placeholder('—'),
                        TextEntry::make('status')->label('Статус')->badge(),
                        TextEntry::make('created_at')->label('Дата создания')->dateTime('d.m.Y H:i'),
                        TextEntry::make('resolved_at')->label('Дата решения')->dateTime('d.m.Y H:i')->placeholder('—'),
                        TextEntry::make('admin_note')->label('Комментарий администратора')->placeholder('—')->columnSpanFull(),
                    ]),
            ]);
    }
}
