<?php

namespace App\Filament\Admin\Resources\PaymentMethods\Schemas;

use App\Enums\ActiveStatus;
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
                        KeyValue::make('settlement_config')
                            ->label('Реквизиты / адрес кошелька')
                            ->keyLabel('Параметр')
                            ->valueLabel('Значение')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
