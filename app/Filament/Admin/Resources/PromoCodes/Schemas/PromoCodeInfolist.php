<?php

namespace App\Filament\Admin\Resources\PromoCodes\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PromoCodeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Промокод')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('id')->label('ID'),
                        TextEntry::make('code')->label('Код'),
                        TextEntry::make('scope')->label('Область действия')->badge(),
                        TextEntry::make('discount_label')->label('Скидка'),
                        TextEntry::make('allowed_products_label')->label('Ограничение по продуктам')->columnSpanFull(),
                        IconEntry::make('single_use')->label('Единоразовое использование')->boolean(),
                        TextEntry::make('validity_label')->label('Срок действия'),
                        TextEntry::make('used_count')->label('Использовано раз'),
                        TextEntry::make('max_uses')->label('Максимум использований')->placeholder('Без ограничения'),
                        IconEntry::make('active')->label('Активен')->boolean(),
                        TextEntry::make('created_at')->label('Дата создания')->dateTime('d.m.Y H:i'),
                    ]),
            ]);
    }
}
