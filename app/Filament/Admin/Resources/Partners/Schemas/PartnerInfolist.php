<?php

namespace App\Filament\Admin\Resources\Partners\Schemas;

use App\Models\Partner;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PartnerInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // 4 поля в 1 ряд из 4 колонок — без пустых ячеек и переносов на следующую строку.
                Section::make('Партнёр')
                    ->columns(4)
                    ->schema([
                        TextEntry::make('name')->label('Имя'),
                        TextEntry::make('email')->label('Email'),
                        TextEntry::make('invite_code')->label('Код приглашения')->copyable(),
                        TextEntry::make('created_at')->label('Дата регистрации')->dateTime('d.m.Y H:i'),
                    ]),

                // Отдельно от денег: статистика по приглашённым — ровно 1 чистая строка из 2 колонок.
                Section::make('Приглашённые')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('referred_users_count')
                            ->label('Приглашено')
                            ->state(fn (Partner $record) => $record->referredUsers()->count()),
                        TextEntry::make('active_referred_users_count')
                            ->label('Активных (есть оплата)')
                            ->state(fn (Partner $record) => $record->activeReferredUsersCount()),
                    ]),

                // Денежные показатели отдельно от счётчиков — 1 чистая строка из 4 колонок, в том же
                // порядке, что и в таблице списка партнёров.
                Section::make('Баланс')
                    ->columns(4)
                    ->schema([
                        TextEntry::make('earned_usd_sum')
                            ->label('Сумма вознаграждений')
                            ->state(fn (Partner $record) => $record->totalEarnedUsd())
                            ->money('USD'),
                        TextEntry::make('available_usd')
                            ->label('Доступно к выводу')
                            ->state(fn (Partner $record) => $record->availableBalanceUsd())
                            ->money('USD'),
                        TextEntry::make('pending_payout_usd')
                            ->label('Ожидает к выплате')
                            ->state(fn (Partner $record) => $record->pendingPayoutUsd())
                            ->money('USD'),
                        TextEntry::make('paid_payout_usd')
                            ->label('Выплачено всего')
                            ->state(fn (Partner $record) => $record->paidPayoutUsd())
                            ->money('USD'),
                    ]),
            ]);
    }
}
