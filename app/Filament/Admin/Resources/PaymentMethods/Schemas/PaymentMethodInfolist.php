<?php

namespace App\Filament\Admin\Resources\PaymentMethods\Schemas;

use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PaymentMethodInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Основное')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('name')->label('Название'),
                        TextEntry::make('type')
                            ->label('Тип'),
                        TextEntry::make('currency')->label('Валюта'),
                        TextEntry::make('status')
                            ->label('Статус')
                            ->badge(),
                        TextEntry::make('fee_percent')->label('Комиссия способа')->suffix('%'),
                        TextEntry::make('gateway_code')
                            ->label('Интеграция')
                            ->placeholder('Тестовая заглушка'),
                    ]),

                Section::make('Технические настройки')
                    ->schema([
                        KeyValueEntry::make('settlement_config')
                            ->label('Ключи и параметры подключения')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
