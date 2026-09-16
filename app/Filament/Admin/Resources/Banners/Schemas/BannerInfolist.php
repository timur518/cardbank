<?php

namespace App\Filament\Admin\Resources\Banners\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BannerInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Баннер')
                    ->columns(3)
                    ->schema([
                        ImageEntry::make('image_url')
                            ->label('Изображение')
                            ->columnSpanFull(),
                        TextEntry::make('title')->label('Заголовок'),
                        TextEntry::make('link_url')->label('Ссылка')->placeholder('—'),
                        TextEntry::make('status')->label('Статус')->badge(),
                        TextEntry::make('start_date')->label('Начало показа')->date('d.m.Y')->placeholder('—'),
                        TextEntry::make('end_date')->label('Окончание показа')->date('d.m.Y')->placeholder('—'),
                        TextEntry::make('sort')->label('Порядок'),
                    ]),
            ]);
    }
}
