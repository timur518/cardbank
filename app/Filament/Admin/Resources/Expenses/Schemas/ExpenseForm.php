<?php

namespace App\Filament\Admin\Resources\Expenses\Schemas;

use App\Enums\ExpenseCategory;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ExpenseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Расход')
                    ->columns(2)
                    ->schema([
                        DatePicker::make('date')
                            ->label('Дата расхода')
                            ->native(false)
                            ->displayFormat('d.m.Y')
                            ->required(),
                        Select::make('category')
                            ->label('Статья расходов')
                            ->options(ExpenseCategory::class)
                            ->live()
                            ->required(),
                        TextInput::make('amount')
                            ->label('Сумма')
                            ->prefix('₽')
                            ->numeric()
                            ->required(),
                        TextInput::make('amount_usd')
                            ->label('Сумма в $')
                            ->helperText('Долларовый эквивалент расхода на момент операции — используется для расчёта прибыли.')
                            ->prefix('$')
                            ->numeric(),
                        Select::make('card_id')
                            ->label('Карта')
                            ->relationship('card', 'id')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->masked_number)
                            ->searchable(),
                        Select::make('provider_id')
                            ->label('Провайдер')
                            ->relationship('provider', 'name')
                            ->searchable()
                            ->preload(),
                        Select::make('ad_placement_id')
                            ->label('Рекламное размещение')
                            ->relationship('adPlacement', 'name')
                            ->searchable()
                            ->preload()
                            ->visible(fn ($get) => $get('category') === ExpenseCategory::AdPlacement->value),
                        Textarea::make('comment')
                            ->label('Комментарий')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
