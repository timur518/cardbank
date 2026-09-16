<?php

namespace App\Filament\Admin\Resources\CardProviders\Schemas;

use App\Enums\ActiveStatus;
use App\Enums\ProviderEnvironment;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CardProviderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Основное')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Название провайдера')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('code')
                            ->label('Код провайдера')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Select::make('status')
                            ->label('Статус')
                            ->options(ActiveStatus::class)
                            ->required(),
                        Select::make('environment')
                            ->label('Окружение')
                            ->options(ProviderEnvironment::class)
                            ->required(),
                    ]),

                Section::make('Техническое подключение')
                    ->columns(2)
                    ->schema([
                        TextInput::make('api_base_url')
                            ->label('Адрес технического подключения')
                            ->url()
                            ->columnSpanFull(),
                        TextInput::make('api_key')
                            ->label('Технический ключ доступа')
                            ->password()
                            ->revealable(),
                        TextInput::make('webhook_secret')
                            ->label('Секретный код проверки входящих сообщений')
                            ->password()
                            ->revealable(),
                    ]),

                Section::make('Комиссии и стоимость выпуска')
                    ->columns(2)
                    ->schema([
                        TextInput::make('topup_fee_percent')
                            ->label('Комиссия за пополнение, %')
                            ->numeric()
                            ->required()
                            ->suffix('%'),
                        TextInput::make('min_topup_usd')
                            ->label('Минимальная сумма пополнения, $')
                            ->numeric()
                            ->required()
                            ->prefix('$'),
                        TextInput::make('reserve_balance_usd')
                            ->label('Текущий остаток резерва, $')
                            ->numeric()
                            ->required()
                            ->prefix('$'),
                        Repeater::make('issue_fee_tiers')
                            ->label('Уровни стоимости выпуска карты')
                            ->schema([
                                TextInput::make('name')
                                    ->label('Название уровня')
                                    ->required(),
                                TextInput::make('cost_usd')
                                    ->label('Стоимость, $')
                                    ->numeric()
                                    ->required()
                                    ->prefix('$'),
                            ])
                            ->columns(2)
                            ->columnSpanFull()
                            ->addActionLabel('Добавить уровень'),
                        Repeater::make('extra_channel_fees')
                            ->label('Дополнительные комиссии по способам оплаты')
                            ->schema([
                                TextInput::make('channel')
                                    ->label('Способ оплаты')
                                    ->required(),
                                TextInput::make('percent')
                                    ->label('Комиссия, %')
                                    ->numeric()
                                    ->required()
                                    ->suffix('%'),
                            ])
                            ->columns(2)
                            ->columnSpanFull()
                            ->addActionLabel('Добавить комиссию'),
                    ]),
            ]);
    }
}
