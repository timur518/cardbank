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
                Section::make('Партнёр')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('name')->label('Имя'),
                        TextEntry::make('email')->label('Email'),
                        TextEntry::make('invite_code')->label('Код приглашения')->copyable(),
                        TextEntry::make('created_at')->label('Дата регистрации')->dateTime('d.m.Y H:i'),
                    ]),

                Section::make('Статистика')
                    ->columns(4)
                    ->schema([
                        TextEntry::make('referred_users_count')
                            ->label('Приглашено')
                            ->state(fn (Partner $record) => $record->referredUsers()->count()),
                        TextEntry::make('active_referred_users_count')
                            ->label('Активных')
                            ->state(fn (Partner $record) => $record->activeReferredUsersCount()),
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
