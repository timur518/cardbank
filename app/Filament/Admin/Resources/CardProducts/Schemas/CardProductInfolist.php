<?php

namespace App\Filament\Admin\Resources\CardProducts\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CardProductInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columnSpanFull()
                    ->columns(4)
                    ->schema([
                        TextEntry::make('name')->label('Название'),
                        TextEntry::make('key')->label('Ключ продукта'),
                        TextEntry::make('provider.name')->label('Провайдер'),
                        TextEntry::make('currency')->label('Валюта'),
                    ]),

                Section::make()
                    ->columns(3)
                    ->schema([
                        TextEntry::make('price_rub')->label('Цена продажи')->money('RUB'),
                        TextEntry::make('provider_issue_cost_usd')->label('Стоимость выпуска карты')->money('USD'),
                        TextEntry::make('estimated_profit')->label('Наша наценка')->money('RUB'),
                    ]),

                Section::make()
                    ->columns(3)
                    ->schema([
                        IconEntry::make('apple_pay_enabled')->label('Apple Pay')->boolean(),
                        IconEntry::make('google_pay_enabled')->label('Google Pay')->boolean(),
                        IconEntry::make('active')->label('Статус')->boolean(),
                    ]),

                Section::make()
                    ->columnSpanFull()
                    ->columns(4)
                    ->schema([
                        TextEntry::make('issue_min_amount')->label('Мин. сумма при выпуске')->placeholder('—'),
                        TextEntry::make('issue_max_amount')->label('Макс. сумма при выпуске')->placeholder('—'),
                        TextEntry::make('topup_min_amount')->label('Мин. сумма пополнения')->placeholder('—'),
                        TextEntry::make('topup_max_amount')->label('Макс. сумма пополнения')->placeholder('—'),
                    ]),

                Section::make('Описание')
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('description')->label('')->placeholder('—')->columnSpanFull(),
                    ]),
            ]);
    }
}
