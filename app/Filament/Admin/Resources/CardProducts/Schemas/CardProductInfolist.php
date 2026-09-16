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
                Section::make('Основное')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('name')->label('Название'),
                        TextEntry::make('key')->label('Ключ продукта'),
                        TextEntry::make('provider.name')->label('Провайдер'),
                        TextEntry::make('currency')->label('Валюта'),
                        TextEntry::make('price_rub')->label('Цена для клиента')->money('RUB'),
                        TextEntry::make('provider_issue_cost_usd')->label('Себестоимость выпуска')->money('USD'),
                        TextEntry::make('estimated_profit')->label('Расчётная прибыль')->money('RUB'),
                        IconEntry::make('wallet_enabled')->label('Apple/Google Pay')->boolean(),
                        IconEntry::make('active')->label('Активен')->boolean(),
                    ]),
                Section::make('Лимиты у провайдера')
                    ->columns(4)
                    ->schema([
                        TextEntry::make('issue_min_amount')->label('Мин. сумма выпуска')->placeholder('—'),
                        TextEntry::make('issue_max_amount')->label('Макс. сумма выпуска')->placeholder('—'),
                        TextEntry::make('topup_min_amount')->label('Мин. сумма пополнения')->placeholder('—'),
                        TextEntry::make('topup_max_amount')->label('Макс. сумма пополнения')->placeholder('—'),
                    ]),
                Section::make('Описание')
                    ->schema([
                        TextEntry::make('description')->label('')->placeholder('—')->columnSpanFull(),
                    ]),
            ]);
    }
}
