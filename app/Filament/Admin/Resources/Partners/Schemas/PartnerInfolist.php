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
                        TextEntry::make('id')->label('ID'),
                        TextEntry::make('user.email')->label('Пользователь'),
                        TextEntry::make('code')->label('Код приглашения'),
                        TextEntry::make('invite_link')->label('Ссылка приглашения')->placeholder('—')->copyable(),
                        TextEntry::make('status')->label('Статус')->badge(),
                        TextEntry::make('created_at')->label('Дата создания')->dateTime('d.m.Y H:i'),
                    ]),

                Section::make('Статистика')
                    ->columns(4)
                    ->schema([
                        TextEntry::make('referrals_count')->label('Приглашено всего'),
                        TextEntry::make('paying_count')->label('Из них платит'),
                        TextEntry::make('lifetime_usd')->label('Заработано всего')->money(fn (Partner $record) => 'USD'),
                        TextEntry::make('available_usd')->label('Доступно к выводу')->money(fn (Partner $record) => 'USD'),
                        TextEntry::make('hold_usd')->label('В ожидании')->money(fn (Partner $record) => 'USD'),
                        TextEntry::make('requested_usd')->label('Запрошено к выводу')->money(fn (Partner $record) => 'USD'),
                        TextEntry::make('paid_usd')->label('Выплачено всего')->money(fn (Partner $record) => 'USD'),
                    ]),
            ]);
    }
}
