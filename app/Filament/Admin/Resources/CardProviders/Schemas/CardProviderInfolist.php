<?php

namespace App\Filament\Admin\Resources\CardProviders\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CardProviderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Основное')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('name')->label('Название'),
                        TextEntry::make('code')->label('Код'),
                        TextEntry::make('status')
                            ->label('Статус')
                            ->badge(),
                        TextEntry::make('environment')
                            ->label('Окружение')
                            ->badge(),
                        TextEntry::make('reserve_balance_usd')
                            ->label('Остаток резерва')
                            ->money('USD'),
                        TextEntry::make('api_base_url')
                            ->label('Адрес подключения')
                            ->placeholder('—'),
                    ]),

                Section::make('Комиссии и стоимость выпуска')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('topup_fee_percent')
                            ->label('Комиссия за пополнение')
                            ->suffix('%'),
                        TextEntry::make('min_topup_usd')
                            ->label('Минимальная сумма пополнения')
                            ->money('USD'),
                        RepeatableEntry::make('issue_fee_tiers')
                            ->label('Уровни стоимости выпуска')
                            ->schema([
                                TextEntry::make('name')->label('Уровень'),
                                TextEntry::make('cost_usd')->label('Стоимость, $'),
                            ])
                            ->columns(2)
                            ->columnSpanFull(),
                        RepeatableEntry::make('extra_channel_fees')
                            ->label('Доп. комиссии по способам оплаты')
                            ->schema([
                                TextEntry::make('channel')->label('Способ оплаты'),
                                TextEntry::make('percent')->label('Комиссия, %'),
                            ])
                            ->columns(2)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
