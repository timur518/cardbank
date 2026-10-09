<?php

namespace App\Filament\Admin\Resources\Refunds\Schemas;

use App\Filament\Admin\Forms\Components\CardSelect;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RefundForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Заявка на возврат')
                    ->columns(2)
                    ->schema([
                        Select::make('user_id')
                            ->label('Пользователь')
                            ->relationship('user', 'email')
                            ->searchable()
                            ->preload()
                            ->required(),
                        CardSelect::make('card_id')
                            ->required(),
                        TextInput::make('amount')
                            ->label('Сумма возврата')
                            ->numeric()
                            ->required(),
                        TextInput::make('currency')
                            ->label('Валюта')
                            ->required()
                            ->maxLength(10),
                        Textarea::make('reason')
                            ->label('Причина возврата')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
