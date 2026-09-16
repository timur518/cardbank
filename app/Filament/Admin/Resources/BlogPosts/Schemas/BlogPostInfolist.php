<?php

namespace App\Filament\Admin\Resources\BlogPosts\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BlogPostInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Статья')
                    ->columns(2)
                    ->schema([
                        ImageEntry::make('cover_image_url')
                            ->label('Обложка')
                            ->columnSpanFull(),
                        TextEntry::make('title')->label('Заголовок')->columnSpanFull(),
                        TextEntry::make('status')->label('Статус')->badge(),
                        TextEntry::make('created_at')->label('Дата публикации')->dateTime('d.m.Y H:i'),
                        TextEntry::make('body')
                            ->label('Текст статьи')
                            ->html()
                            ->placeholder('—')
                            ->columnSpanFull(),
                    ]),

                Section::make('SEO')
                    ->columns(2)
                    ->collapsed()
                    ->schema([
                        TextEntry::make('seo_title')->label('SEO-заголовок')->placeholder('—'),
                        TextEntry::make('seo_description')->label('SEO-описание')->placeholder('—'),
                    ]),
            ]);
    }
}
