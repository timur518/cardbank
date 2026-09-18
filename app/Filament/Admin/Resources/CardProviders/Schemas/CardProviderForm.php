<?php

namespace App\Filament\Admin\Resources\CardProviders\Schemas;

use App\Enums\ActiveStatus;
use App\Enums\ProviderEnvironment;
use App\Models\CardProvider;
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
                    ->columnSpanFull()
                    ->columns(5)
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
                        TextInput::make('reserve_balance_usd')
                            ->label('Текущий остаток резерва, $')
                            ->numeric()
                            ->required()
                            ->prefix('$')
                            ->disabled(fn (?CardProvider $record) => $record !== null)
                            ->dehydrated(fn (?CardProvider $record) => $record === null)
                            ->helperText(fn (?CardProvider $record) => $record !== null
                                ? 'Обновляется автоматически из реального баланса мастер-счёта (providers:sync-account-balances), вручную здесь уже не поменять.'
                                : 'Начальное значение — дальше будет обновляться автоматически.'),
                    ]),

                Section::make('Техническое подключение')
                    ->columnSpanFull()
                    ->columns(4)
                    ->schema([
                        TextInput::make('api_base_url')
                            ->label('Адрес технического подключения')
                            ->helperText('Только хост, например https://api.cardspro.com — путь /cards/v1 интеграция добавит сама.')
                            ->url()
                            ->columnSpanFull(),
                        TextInput::make('api_key')
                            ->label('API Key (CAP-TOKEN)')
                            ->password()
                            ->revealable(),
                        TextInput::make('api_secret')
                            ->label('Secret Key (для подписи CAP-SIGN)')
                            ->password()
                            ->revealable(),
                        TextInput::make('webhook_secret')
                            ->label('Токен для проверки входящих вебхуков')
                            ->password()
                            ->revealable()
                            ->helperText('Подставляется в URL вебхука как ?token=[redacted]] — CardsPro не подписывает колбэки, это наша дополнительная защита.'),
                    ]),
            ]);
    }
}
