<?php

namespace App\Filament\Admin\Resources\Cards\Schemas;

use App\Enums\CardStatus;
use App\Models\CardProduct;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CardForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Владелец и продукт')
                    ->columns(2)
                    ->schema([
                        Select::make('user_id')
                            ->label('Владелец карты')
                            ->relationship('user', 'email')
                            ->getOptionLabelFromRecordUsing(fn ($record) => trim("{$record->last_name} {$record->first_name}") ?: $record->email)
                            ->searchable(['first_name', 'last_name', 'email'])
                            ->preload()
                            ->required(),
                        Select::make('card_product_id')
                            ->label('Карточный продукт')
                            ->relationship('cardProduct', 'name')
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if (! $state) {
                                    return;
                                }

                                $product = CardProduct::find($state);

                                if ($product) {
                                    $set('provider_id', $product->provider_id);
                                    $set('currency', $product->currency);
                                    $set('price_rub', $product->price_rub);
                                    $set('issue_cost_usd', $product->provider_issue_cost_usd);
                                }
                            })
                            ->required(),
                        Select::make('provider_id')
                            ->label('Провайдер')
                            ->relationship('provider', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('currency')
                            ->label('Валюта')
                            ->required()
                            ->maxLength(10),
                        Select::make('status')
                            ->label('Статус')
                            ->options(CardStatus::class)
                            ->default(CardStatus::Pending)
                            ->required(),
                    ]),

                Section::make('Финансы')
                    ->columns(2)
                    ->schema([
                        TextInput::make('price_rub')
                            ->label('Цена выпуска для клиента, ₽')
                            ->numeric()
                            ->prefix('₽'),
                        TextInput::make('issue_cost_usd')
                            ->label('Себестоимость выпуска, $')
                            ->numeric()
                            ->prefix('$'),
                        TextInput::make('balance')
                            ->label('Текущий баланс')
                            ->numeric()
                            ->helperText('Меняется только через действие «Поправить баланс» в списке карт.')
                            ->dehydrated(),
                        TextInput::make('fee_debt')
                            ->label('Долг по комиссии')
                            ->numeric()
                            ->dehydrated(),
                    ]),

                //->visible(fn () => auth()->user()?->can('view_card_sensitive_data'))
                Section::make('Реквизиты карты')
                    ->columns(2)
                    ->schema([
                        TextInput::make('provider_card_id')
                            ->label('Идентификатор карты у провайдера'),
                        TextInput::make('card_number')
                            ->label('Номер карты'),
                        TextInput::make('expiry')
                            ->label('Срок действия')
                            ->placeholder('ММ/ГГ'),
                        TextInput::make('cvv')
                            ->label('Код проверки (CVV)'),
                    ]),

                Section::make('Платёжный адрес карты')
                    ->columns(2)
                    ->schema([
                        TextInput::make('billing_country')->label('Страна'),
                        TextInput::make('billing_city')->label('Город'),
                        TextInput::make('billing_region')->label('Регион'),
                        TextInput::make('billing_address')->label('Улица'),
                        TextInput::make('billing_post_code')->label('Индекс'),
                    ]),
            ]);
    }
}
