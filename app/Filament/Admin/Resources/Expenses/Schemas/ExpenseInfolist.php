<?php

namespace App\Filament\Admin\Resources\Expenses\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ExpenseInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Расход')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('id')->label('ID'),
                        TextEntry::make('date')->label('Дата')->date('d.m.Y'),
                        TextEntry::make('category')
                            ->label('Статья расходов')
                            ->badge(),
                        TextEntry::make('amount')->label('Сумма')->numeric(2)->prefix('₽'),
                        TextEntry::make('amount_usd')->label('Сумма в $')->numeric(2)->prefix('$')->placeholder('—'),
                        TextEntry::make('card.masked_number')->label('Карта')->placeholder('—'),
                        TextEntry::make('provider.name')->label('Провайдер')->placeholder('—'),
                        TextEntry::make('comment')->label('Комментарий')->placeholder('—')->columnSpanFull(),
                        TextEntry::make('creator.name')->label('Кто внёс')->placeholder('—'),
                    ]),
            ]);
    }
}
