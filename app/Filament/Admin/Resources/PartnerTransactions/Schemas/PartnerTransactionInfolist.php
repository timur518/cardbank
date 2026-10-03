<?php

namespace App\Filament\Admin\Resources\PartnerTransactions\Schemas;

use App\Models\PartnerTransaction;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PartnerTransactionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Разбито на 3 смысловых блока, каждый — на всю ширину страницы (columnSpanFull), внутри — чистые ряды.
                Section::make('Участники')
                    ->columns(3)
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('id')->label('ID'),
                        TextEntry::make('partner.email')->label('Партнёр'),
                        TextEntry::make('buyer.email')->label('Покупатель'),
                    ]),

                Section::make('Начисление')
                    ->columns(3)
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('type')->label('За что')->badge(),
                        TextEntry::make('rate')
                            ->label('Ставка')
                            ->formatStateUsing(fn (PartnerTransaction $record) => $record->type->isPercentRate() ? "{$record->rate}%" : "\${$record->rate}"),
                        TextEntry::make('commission_amount')->label('Сумма вознаграждения')->money('USD'),
                    ]),

                Section::make('Служебная информация')
                    ->columns(3)
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('income_id')->label('ID операции в поступлениях')->placeholder('—'),
                        TextEntry::make('created_at')->label('Когда создано')->dateTime('d.m.Y H:i'),
                        TextEntry::make('updated_at')->label('Когда отредактировано')->dateTime('d.m.Y H:i'),
                    ]),
            ]);
    }
}
