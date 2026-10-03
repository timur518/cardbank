<?php

namespace App\Filament\Admin\Resources\PayoutRequests\Schemas;

use App\Models\PayoutRequest;
use App\Enums\PayoutDestination;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PayoutRequestInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Идентичность заявки — 1 чистая строка из 3 колонок.
                Section::make('Заявка на выплату')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('id')->label('ID'),
                        TextEntry::make('user.email')->label('Партнёр'),
                        TextEntry::make('status')->label('Статус')->badge(),
                    ]),

                // Сумма и способ выплаты — 1 чистая строка из 3 колонок.
                Section::make('Сумма и способ выплаты')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('amount_usd')->label('Списывается с баланса')->money('USD'),
                        TextEntry::make('amount_rub')->label('Сумма перевода')->money('RUB'),
                        TextEntry::make('destination')->label('Куда вывести')->badge(),
                    ]),

                // Реквизиты показываются только для вывода на карту — 1 чистая строка из 3 колонок.
                Section::make('Реквизиты карты')
                    ->columns(3)
                    ->visible(fn (PayoutRequest $record) => $record->destination === PayoutDestination::BankCard)
                    ->schema([
                        TextEntry::make('bank_name')->label('Банк')->placeholder('—'),
                        TextEntry::make('bank_card_number')->label('Номер карты')->placeholder('—'),
                        TextEntry::make('bank_card_holder')->label('Держатель карты')->placeholder('—'),
                    ]),

                // Служебная информация — даты в одну строку, комментарий во всю ширину ниже.
                Section::make('Служебная информация')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('created_at')->label('Дата создания')->dateTime('d.m.Y H:i'),
                        TextEntry::make('resolved_at')->label('Дата решения')->dateTime('d.m.Y H:i')->placeholder('—'),
                        TextEntry::make('admin_note')->label('Комментарий администратора')->placeholder('—')->columnSpanFull(),
                    ]),
            ]);
    }
}
