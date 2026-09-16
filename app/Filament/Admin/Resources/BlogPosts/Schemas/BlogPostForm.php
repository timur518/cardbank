<?php

namespace App\Filament\Admin\Resources\BlogPosts\Schemas;

use App\Enums\BlogPostStatus;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BlogPostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Статья')
                    ->columns(2)
                    ->schema([
                        FileUpload::make('cover_image_url')
                            ->label('Обложка статьи')
                            ->image()
                            ->directory('blog')
                            ->columnSpanFull(),
                        TextInput::make('title')
                            ->label('Заголовок')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        RichEditor::make('body')
                            ->label('Текст статьи')
                            ->columnSpanFull(),
                    ]),

                Section::make('SEO')
                    ->columns(2)
                    ->collapsed()
                    ->schema([
                        TextInput::make('seo_title')
                            ->label('SEO-заголовок')
                            ->maxLength(255),
                        Textarea::make('seo_description')
                            ->label('SEO-описание')
                            ->columnSpanFull(),
                    ]),

                Section::make('Публикация')
                    ->schema([
                        Select::make('status')
                            ->label('Статус')
                            ->options(BlogPostStatus::class)
                            ->default(BlogPostStatus::Draft)
                            ->required(),
                    ]),
            ]);
    }
}
