<?php

namespace App\Filament\Admin\Resources\PromoCodes\Schemas;

use App\Enums\PromoCodeScope;
use App\Models\CardProduct;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PromoCodeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Промокод')
                    ->columns(2)
                    ->schema([
                        TextInput::make('code')
                            ->label('Код промокода')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Select::make('scope')
                            ->label('Область действия')
                            ->options(PromoCodeScope::class)
                            ->required()
                            ->live(),
                    ]),

                Section::make('Скидка')
                    ->columns(2)
                    ->schema([
                        TextInput::make('fixed_discount_rub')
                            ->label('Фиксированная скидка на выпуск карты, ₽')
                            ->numeric()
                            ->prefix('₽')
                            ->visible(fn ($get) => $get('scope') === PromoCodeScope::CardIssue->value),
                        TextInput::make('topup_discount_percent')
                            ->label('Скидка на пополнение, %')
                            ->numeric()
                            ->suffix('%')
                            ->visible(fn ($get) => $get('scope') === PromoCodeScope::CardTopup->value),
                    ]),

                Section::make('Ограничения')
                    ->columns(2)
                    ->schema([
                        Select::make('allowed_product_ids')
                            ->label('Ограничение по карточным продуктам')
                            ->helperText('Пусто — промокод действует на все продукты.')
                            ->options(fn () => CardProduct::query()->pluck('name', 'id'))
                            ->multiple()
                            ->searchable()
                            ->columnSpanFull(),
                        Toggle::make('single_use')
                            ->label('Единоразовое использование')
                            ->default(false),
                        TextInput::make('max_uses')
                            ->label('Максимальное количество использований')
                            ->numeric(),
                    ]),

                Section::make('Срок и активность')
                    ->columns(3)
                    ->schema([
                        DateTimePicker::make('valid_from')
                            ->label('Дата начала действия')
                            ->native(false)
                            ->displayFormat('d.m.Y H:i'),
                        DateTimePicker::make('valid_until')
                            ->label('Дата окончания действия')
                            ->native(false)
                            ->displayFormat('d.m.Y H:i'),
                        Toggle::make('active')
                            ->label('Активен')
                            ->default(true),
                    ]),
            ]);
    }
}
