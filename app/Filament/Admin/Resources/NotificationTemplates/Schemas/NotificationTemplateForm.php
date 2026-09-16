<?php

namespace App\Filament\Admin\Resources\NotificationTemplates\Schemas;

use App\Enums\NotificationChannel;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class NotificationTemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Шаблон')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Название шаблона')
                            ->required()
                            ->maxLength(255),
                        Select::make('channel')
                            ->label('Канал отправки')
                            ->options(NotificationChannel::class)
                            ->required()
                            ->live(),
                        TextInput::make('subject')
                            ->label('Тема письма')
                            ->maxLength(255)
                            ->visible(fn ($get) => $get('channel') === NotificationChannel::Email->value)
                            ->columnSpanFull(),
                        Textarea::make('body')
                            ->label('Текст сообщения')
                            ->required()
                            ->rows(8)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
