<?php

namespace App\Filament\Admin\Resources\Incomes\Schemas;

use App\Enums\IncomePaymentStatus;
use App\Enums\IncomeType;
use App\Filament\Admin\Forms\Components\CardSelect;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class IncomeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Поступление')
                    ->columnSpanFull()
                    ->columns(4)
                    ->schema([
                        Select::make('type')
                            ->label('Тип поступления')
                            ->options(IncomeType::class)
                            ->required(),
                        TextInput::make('amount')
                            ->label('Сумма')
                            ->numeric()
                            ->required(),
                        TextInput::make('currency')
                            ->label('Валюта')
                            ->required()
                            ->maxLength(10),
                        TextInput::make('amount_usd')
                            ->label('Сумма в $')
                            ->numeric(),
                    ]),

                Section::make('Пользователь и карта')
                    ->columns(2)
                    ->schema([
                        Select::make('user_id')
                            ->label('Пользователь')
                            ->relationship('user', 'email')
                            ->searchable()
                            ->preload(),
                        CardSelect::make('card_id'),
                    ]),

                Section::make('Данные платежа')
                    ->columns(2)
                    ->schema([
                        Select::make('payment_method_id')
                            ->label('Способ оплаты')
                            ->relationship('paymentMethod', 'name')
                            ->searchable()
                            ->preload()
                            ->columnSpanFull(),
                        Select::make('payment_status')
                            ->label('Статус платежа')
                            ->options(IncomePaymentStatus::class)
                            ->default(IncomePaymentStatus::Pending)
                            ->required(),
                        TextInput::make('payment_transaction_id')
                            ->label('ID транзакции в платёжной системе')
                            ->maxLength(255),
                    ]),

                Section::make('Комментарий')
                    ->columnSpanFull()
                    ->schema([
                        Textarea::make('comment')
                            ->label('Комментарий')
                            ->hiddenLabel()
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
