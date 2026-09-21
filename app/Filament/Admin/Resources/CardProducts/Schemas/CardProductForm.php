<?php

namespace App\Filament\Admin\Resources\CardProducts\Schemas;

use App\Enums\CardNetwork;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CardProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Изображение карты')
                    ->schema([
                        FileUpload::make('skin')
                            ->label('Оформление карты (изображение)')
                            ->image()
                            ->disk('public')
                            ->visibility('public')
                            ->directory('card-skins'),
                    ]),

                Section::make('Основное')
                    ->columns(2)
                    ->schema([
                        TextInput::make('key')
                            ->label('Ключ продукта')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        TextInput::make('name')
                            ->label('Название')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('currency')
                            ->label('Валюта')
                            ->required()
                            ->maxLength(10),
                        Textarea::make('description')
                            ->label('Описание')
                            ->columnSpanFull(),
                    ]),

                Section::make('Карта и сеть')
                    ->columns(3)
                    ->schema([
                        Select::make('network')
                            ->label('Тип карты')
                            ->options(CardNetwork::class),
                        TextInput::make('card_country')
                            ->label('Страна карты')
                            ->maxLength(255),
                        TextInput::make('bin')
                            ->label('BIN карты')
                            ->numeric(),
                    ]),

                Section::make('Провайдер и стоимость')
                    ->columns(2)
                    ->schema([
                        Select::make('provider_id')
                            ->label('Провайдер')
                            ->relationship('provider', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('provider_product_code')
                            ->label('Код продукта у провайдера')
                            ->maxLength(255),
                        Toggle::make('provider_kyc_required')
                            ->label('Требуется проверка личности у провайдера'),
                        TextInput::make('provider_issue_cost_usd')
                            ->label('Стоимость выпуска карты, $')
                            ->numeric()
                            ->required()
                            ->prefix('$'),
                        TextInput::make('provider_topup_fee_percent')
                            ->label('Комиссия провайдера за пополнение, %')
                            ->numeric()
                            ->default(0)
                            ->suffix('%'),
                        TextInput::make('price_rub')
                            ->label('Цена продажи, ₽')
                            ->numeric()
                            ->required()
                            ->prefix('₽'),
                    ]),

                Section::make('Комиссии и тарифы')
                    ->columns(3)
                    ->schema([
                        TextInput::make('successful_payment_fee_usd')
                            ->label('Успешная оплата, $')
                            ->numeric()
                            ->prefix('$'),
                        TextInput::make('decline_fee_usd')
                            ->label('Отказ в оплате, $')
                            ->numeric()
                            ->prefix('$'),
                        TextInput::make('risk_operation_fee_usd')
                            ->label('Рисковая операция, $')
                            ->numeric()
                            ->prefix('$'),
                        TextInput::make('non_usd_payment_fee')
                            ->label('Оплата не в $ (FX)')
                            ->maxLength(255),
                        Toggle::make('three_ds_supported')
                            ->label('3DS коды'),
                    ]),

                Section::make('Лимиты у провайдера')
                    ->description('Подтягиваются из каталога провайдера командой providers:sync-card-catalog --sync')
                    ->columns(4)
                    ->schema([
                        TextInput::make('issue_min_amount')
                            ->label('Мин. сумма выпуска')
                            ->numeric(),
                        TextInput::make('issue_max_amount')
                            ->label('Макс. сумма выпуска')
                            ->numeric(),
                        TextInput::make('topup_min_amount')
                            ->label('Мин. сумма пополнения')
                            ->numeric(),
                        TextInput::make('topup_max_amount')
                            ->label('Макс. сумма пополнения')
                            ->numeric(),
                    ]),

                Section::make('Кошелёк')
                    ->columns(3)
                    ->schema([
                        Toggle::make('apple_pay_enabled')
                            ->label('Apple Pay')
                            ->live(),
                        Toggle::make('google_pay_enabled')
                            ->label('Google Pay')
                            ->live(),
                        TextInput::make('wallet_activation')
                            ->label('Способ подключения кошелька')
                            ->visible(fn ($get) => (bool) $get('apple_pay_enabled') || (bool) $get('google_pay_enabled'))
                            ->maxLength(255),
                    ]),

                Section::make('Платёжный адрес продукта')
                    ->columns(2)
                    ->schema([
                        TextInput::make('billing_country')->label('Страна'),
                        TextInput::make('billing_city')->label('Город'),
                        TextInput::make('billing_region')->label('Регион'),
                        TextInput::make('billing_address')->label('Улица'),
                        TextInput::make('billing_post_code')->label('Индекс'),
                    ]),

                Section::make('Публикация')
                    ->columns(3)
                    ->schema([
                        Toggle::make('active')
                            ->label('Продукт активен')
                            ->default(true),
                        Toggle::make('coming_soon')
                            ->label('Скоро появится'),
                        TextInput::make('sort')
                            ->label('Порядок отображения')
                            ->numeric()
                            ->default(0),
                    ]),

                Section::make('Запрещённые мерчанты')
                    ->schema([
                        RichEditor::make('restricted_merchants')
                            ->label('')
                            ->columnSpanFull(),
                    ]),

                Section::make('Все условия карты')
                    ->schema([
                        RichEditor::make('full_terms')
                            ->label('')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
