<?php

namespace App\Filament\Admin\Resources\Incomes\Schemas;

use App\Enums\IncomePaymentStatus;
use App\Enums\IncomeType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class IncomeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Поступление')
                    ->columns(2)
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
                        Select::make('user_id')
                            ->label('Пользователь')
                            ->relationship('user', 'email')
                            ->searchable()
                            ->preload(),
                        Select::make('card_id')
                            ->label('Карта')
                            ->relationship('card', 'id')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->masked_number)
                            ->searchable(),
                        Select::make('payment_method_id')
                            ->label('Способ оплаты')
                            ->relationship('paymentMethod', 'name')
                            ->searchable()
                            ->preload(),
                        TextInput::make('payment_transaction_id')
                            ->label('ID транзакции в платёжной системе')
                            ->maxLength(255),
                        Select::make('payment_status')
                            ->label('Статус платежа')
                            ->options(IncomePaymentStatus::class)
                            ->default(IncomePaymentStatus::Pending)
                            ->required(),
                        Textarea::make('comment')
                            ->label('Комментарий')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
