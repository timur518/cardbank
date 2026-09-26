<?php

namespace App\Filament\Admin\Resources\Merchants\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MerchantInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Мерчант')
                    ->columns(2)
                    ->schema([
                        ViewEntry::make('logo_preview')
                            ->label('Логотип')
                            ->view('filament.infolists.entries.merchant-logo')
                            ->columnSpanFull(),
                        TextEntry::make('code')->label('Код мерчанта'),
                        TextEntry::make('name')->label('Название мерчанта'),
                        TextEntry::make('provider_merchant_key')
                            ->label('Ключ для CardsPro')
                            ->state(fn ($record) => $record->providerSearchKey())
                            ->helperText('Фактическое значение параметра merchant при запросе рейтинга платежей.'),
                        TextEntry::make('category')->label('Категория')->badge(),
                        TextEntry::make('color')->label('Цвет')->placeholder('—'),
                        TextEntry::make('is_active')->label('Активен')->formatStateUsing(fn (bool $state) => $state ? 'Да' : 'Нет'),
                        TextEntry::make('created_at')->label('Дата создания')->dateTime('d.m.Y H:i'),
                    ]),
            ]);
    }
}
