<?php

namespace App\Filament\Admin\Resources\Merchants\Schemas;

use App\Models\Merchant;
use Filament\Infolists\Components\TextEntry;
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
                        TextEntry::make('logo_preview')
                            ->label('Логотип')
                            ->state(fn (Merchant $record) => (string) $record->getKey())
                            ->formatStateUsing(fn (Merchant $record) => self::formatLogo($record))
                            ->html()
                            ->columnSpanFull(),
                        TextEntry::make('code')->label('Код мерчанта'),
                        TextEntry::make('name')->label('Название мерчанта'),
                        TextEntry::make('category')->label('Категория')->badge(),
                        TextEntry::make('color')->label('Цвет')->placeholder('—'),
                        TextEntry::make('is_active')->label('Активен')->formatStateUsing(fn (bool $state) => $state ? 'Да' : 'Нет'),
                        TextEntry::make('created_at')->label('Дата создания')->dateTime('d.m.Y H:i'),
                    ]),
            ]);
    }

    private static function formatLogo(Merchant $record): string
    {
        if (! $record->logo_svg) {
            return '<div style="width:56px;height:56px;border-radius:14px;background:#e5e7eb"></div>';
        }

        $color = $record->color ?: '#111827';

        return sprintf(
            '<div style="width:56px;height:56px;border-radius:14px;background:%s;display:flex;align-items:center;justify-content:center"><svg viewBox="0 0 24 24" width="30" height="30" fill="#fff">%s</svg></div>',
            e($color),
            $record->logo_svg
        );
    }
}
