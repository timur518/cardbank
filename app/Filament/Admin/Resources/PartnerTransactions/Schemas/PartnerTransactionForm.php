<?php

namespace App\Filament\Admin\Resources\PartnerTransactions\Schemas;

use App\Enums\PartnerTransactionType;
use App\Models\Income;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PartnerTransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Начисление')
                    ->columns(2)
                    ->schema([
                        Select::make('partner_user_id')
                            ->label('Партнёр')
                            ->relationship('partner', 'email')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('buyer_user_id')
                            ->label('Покупатель')
                            ->relationship('buyer', 'email')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('type')
                            ->label('За что')
                            ->options(PartnerTransactionType::class)
                            ->required()
                            ->live(),
                        Select::make('income_id')
                            ->label('Операция в поступлениях')
                            ->relationship('income', 'id')
                            ->getOptionLabelFromRecordUsing(fn (Income $record) => "#{$record->id} — {$record->user?->email} — {$record->type->getLabel()} — \${$record->amount_usd}")
                            ->searchable()
                            ->preload()
                            ->visible(fn ($get) => $get('type') !== PartnerTransactionType::Registration->value),
                        TextInput::make('rate')
                            ->label('Ставка')
                            ->numeric()
                            ->required()
                            ->suffix(fn ($get) => $get('type') === PartnerTransactionType::Registration->value ? '$' : '%'),
                        TextInput::make('commission_amount')
                            ->label('Сумма вознаграждения, $')
                            ->numeric()
                            ->prefix('$')
                            ->required(),
                    ]),
            ]);
    }
}
