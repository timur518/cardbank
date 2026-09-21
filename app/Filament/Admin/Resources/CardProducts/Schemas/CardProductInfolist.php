<?php

namespace App\Filament\Admin\Resources\CardProducts\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class CardProductInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Карточный продукт')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Карта')
                            ->schema([
                                Section::make('Изображение карты')
                                    ->columnSpanFull()
                                    ->schema([
                                        ImageEntry::make('skin')
                                            ->label('')
                                            ->disk('public')
                                            ->height(180),
                                    ]),

                                Section::make('Основное')
                                    ->columnSpanFull()
                                    ->columns(4)
                                    ->schema([
                                        TextEntry::make('name')->label('Название'),
                                        TextEntry::make('key')->label('Ключ продукта'),
                                        TextEntry::make('currency')->label('Валюта'),
                                        IconEntry::make('active')->label('Статус')->boolean(),
                                        TextEntry::make('description')->label('Описание')->placeholder('—')->columnSpanFull(),
                                    ]),

                                Section::make('Карта и сеть')
                                    ->columnSpanFull()
                                    ->columns(3)
                                    ->schema([
                                        TextEntry::make('network')->label('Тип карты')->badge()->placeholder('—'),
                                        TextEntry::make('card_country')->label('Страна карты')->placeholder('—'),
                                        TextEntry::make('bin')->label('BIN карты')->placeholder('—'),
                                    ]),

                                Section::make('Публикация')
                                    ->columnSpanFull()
                                    ->columns(3)
                                    ->schema([
                                        IconEntry::make('active')->label('Продукт активен')->boolean(),
                                        IconEntry::make('coming_soon')->label('Скоро появится')->boolean(),
                                        TextEntry::make('sort')->label('Порядок отображения'),
                                    ]),
                            ]),

                        Tab::make('Стоимость и тарифы')
                            ->schema([
                                Section::make('Провайдер и стоимость')
                                    ->columnSpanFull()
                                    ->columns(3)
                                    ->schema([
                                        TextEntry::make('provider.name')->label('Провайдер'),
                                        TextEntry::make('provider_product_code')->label('Код продукта у провайдера')->placeholder('—'),
                                        IconEntry::make('provider_kyc_required')->label('Требуется проверка личности')->boolean(),
                                        TextEntry::make('provider_issue_cost_usd')->label('Стоимость выпуска карты')->money('USD'),
                                        TextEntry::make('provider_topup_fee_percent')->label('Комиссия за пополнение')->suffix('%'),
                                        TextEntry::make('price_rub')->label('Цена продажи')->money('RUB'),
                                        TextEntry::make('estimated_profit')->label('Наша наценка')->money('RUB'),
                                    ]),

                                Section::make('Комиссии и тарифы')
                                    ->columnSpanFull()
                                    ->columns(3)
                                    ->schema([
                                        TextEntry::make('successful_payment_fee_usd')->label('Успешная оплата')->money('USD')->placeholder('—'),
                                        TextEntry::make('decline_fee_usd')->label('Отказ в оплате')->money('USD')->placeholder('—'),
                                        TextEntry::make('risk_operation_fee_usd')->label('Рисковая операция')->money('USD')->placeholder('—'),
                                        TextEntry::make('non_usd_payment_fee')->label('Оплата не в $ (FX)')->placeholder('—'),
                                        IconEntry::make('three_ds_supported')->label('3DS коды')->boolean(),
                                    ]),

                                Section::make('Лимиты у провайдера')
                                    ->columnSpanFull()
                                    ->columns(4)
                                    ->schema([
                                        TextEntry::make('issue_min_amount')->label('Мин. сумма при выпуске')->placeholder('—'),
                                        TextEntry::make('issue_max_amount')->label('Макс. сумма при выпуске')->placeholder('—'),
                                        TextEntry::make('topup_min_amount')->label('Мин. сумма пополнения')->placeholder('—'),
                                        TextEntry::make('topup_max_amount')->label('Макс. сумма пополнения')->placeholder('—'),
                                    ]),
                            ]),

                        Tab::make('Кошелёк и адрес')
                            ->schema([
                                Section::make('Кошелёк')
                                    ->columnSpanFull()
                                    ->columns(3)
                                    ->schema([
                                        IconEntry::make('apple_pay_enabled')->label('Apple Pay')->boolean(),
                                        IconEntry::make('google_pay_enabled')->label('Google Pay')->boolean(),
                                        TextEntry::make('wallet_activation')->label('Способ подключения кошелька')->placeholder('—'),
                                    ]),

                                Section::make('Платёжный адрес продукта')
                                    ->columnSpanFull()
                                    ->columns(2)
                                    ->schema([
                                        TextEntry::make('billing_country')->label('Страна')->placeholder('—'),
                                        TextEntry::make('billing_city')->label('Город')->placeholder('—'),
                                        TextEntry::make('billing_region')->label('Регион')->placeholder('—'),
                                        TextEntry::make('billing_address')->label('Улица')->placeholder('—'),
                                        TextEntry::make('billing_post_code')->label('Индекс')->placeholder('—'),
                                    ]),
                            ]),

                        Tab::make('Условия')
                            ->schema([
                                Section::make('Запрещённые мерчанты')
                                    ->columnSpanFull()
                                    ->schema([
                                        TextEntry::make('restricted_merchants')->label('')->html()->placeholder('—')->columnSpanFull(),
                                    ]),

                                Section::make('Все условия карты')
                                    ->columnSpanFull()
                                    ->schema([
                                        TextEntry::make('full_terms')->label('')->html()->placeholder('—')->columnSpanFull(),
                                    ]),
                            ]),
                    ]),
            ]);
    }
}
