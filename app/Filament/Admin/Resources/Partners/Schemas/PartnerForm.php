<?php

namespace App\Filament\Admin\Resources\Partners\Schemas;

use App\Enums\PartnerStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PartnerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Партнёр')
                    ->columns(2)
                    ->schema([
                        Select::make('user_id')
                            ->label('Пользователь')
                            ->relationship('user', 'email')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('status')
                            ->label('Статус')
                            ->options(PartnerStatus::class)
                            ->default(PartnerStatus::Active)
                            ->required(),
                        TextInput::make('code')
                            ->label('Код приглашения')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        TextInput::make('invite_link')
                            ->label('Ссылка приглашения')
                            ->url()
                            ->maxLength(255),
                    ]),

                Section::make('Статистика')
                    ->columns(3)
                    ->schema([
                        TextInput::make('referrals_count')
                            ->label('Приглашено всего')
                            ->numeric()
                            ->default(0),
                        TextInput::make('paying_count')
                            ->label('Из них платит')
                            ->numeric()
                            ->default(0),
                        TextInput::make('lifetime_usd')
                            ->label('Заработано за всё время, $')
                            ->numeric()
                            ->prefix('$')
                            ->default(0),
                        TextInput::make('available_usd')
                            ->label('Доступно к выводу, $')
                            ->numeric()
                            ->prefix('$')
                            ->default(0),
                        TextInput::make('hold_usd')
                            ->label('В ожидании, $')
                            ->numeric()
                            ->prefix('$')
                            ->default(0),
                        TextInput::make('requested_usd')
                            ->label('Запрошено к выводу, $')
                            ->numeric()
                            ->prefix('$')
                            ->default(0),
                        TextInput::make('paid_usd')
                            ->label('Выплачено всего, $')
                            ->numeric()
                            ->prefix('$')
                            ->default(0),
                    ]),
            ]);
    }
}
