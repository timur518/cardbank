<?php

namespace App\Filament\Admin\Resources\PayoutRequests\Schemas;

use App\Enums\PayoutDestination;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PayoutRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Заявка на выплату')
                    ->columns(2)
                    ->schema([
                        Select::make('partner_id')
                            ->label('Партнёр')
                            ->relationship('partner', 'code')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('amount_usd')
                            ->label('Сумма выплаты, $')
                            ->numeric()
                            ->prefix('$')
                            ->required(),
                        Select::make('destination')
                            ->label('Куда вывести')
                            ->options(PayoutDestination::class)
                            ->required()
                            ->live(),
                        TextInput::make('bank_name')
                            ->label('Название банка')
                            ->maxLength(255)
                            ->visible(fn ($get) => $get('destination') === PayoutDestination::BankCard->value),
                        TextInput::make('bank_card_number')
                            ->label('Номер карты для вывода')
                            ->maxLength(255)
                            ->visible(fn ($get) => $get('destination') === PayoutDestination::BankCard->value),
                        TextInput::make('bank_card_holder')
                            ->label('Имя держателя карты')
                            ->maxLength(255)
                            ->visible(fn ($get) => $get('destination') === PayoutDestination::BankCard->value),
                        Textarea::make('admin_note')
                            ->label('Комментарий администратора')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
