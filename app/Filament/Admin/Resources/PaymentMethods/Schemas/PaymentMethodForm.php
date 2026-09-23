<?php

namespace App\Filament\Admin\Resources\PaymentMethods\Schemas;

use App\Enums\ActiveStatus;
use App\Enums\PaymentGatewayCode;
use App\Enums\PaymentMethodType;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PaymentMethodForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Основное')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Название способа')
                            ->required()
                            ->maxLength(255),
                        Select::make('type')
                            ->label('Тип способа')
                            ->options(PaymentMethodType::class)
                            ->required(),
                        TextInput::make('currency')
                            ->label('Валюта расчётов')
                            ->required()
                            ->maxLength(10),
                        Select::make('status')
                            ->label('Статус')
                            ->options(ActiveStatus::class)
                            ->required(),
                    ]),

                Section::make('Комиссии и лимиты')
                    ->columns(2)
                    ->schema([
                        TextInput::make('fee_percent')
                            ->label('Комиссия способа оплаты, %')
                            ->numeric()
                            ->required()
                            ->suffix('%'),
                        TextInput::make('min_amount')
                            ->label('Минимальная сумма операции')
                            ->numeric(),
                        TextInput::make('max_amount')
                            ->label('Максимальная сумма операции')
                            ->numeric(),
                    ]),

                Section::make('Технические настройки подключения')
                    ->schema([
                        Select::make('gateway_code')
                            ->label('Интеграция')
                            ->helperText('Какая платёжная система обслуживает этот способ оплаты. Не выбрано — используется тестовая заглушка (StubPaymentGateway).')
                            ->options(PaymentGatewayCode::class)
                            ->native(false),
                        KeyValue::make('settlement_config')
                            ->label('Ключи и параметры подключения')
                            ->helperText('Для CardLink: api_token, shop_id (обязательны), success_url, fail_url, base_url (опционально).')
                            ->keyLabel('Параметр')
                            ->valueLabel('Значение')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
