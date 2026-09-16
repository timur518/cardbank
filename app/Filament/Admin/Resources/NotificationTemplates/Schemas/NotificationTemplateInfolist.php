<?php

namespace App\Filament\Admin\Resources\NotificationTemplates\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class NotificationTemplateInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Шаблон')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name')->label('Название'),
                        TextEntry::make('channel')->label('Канал')->badge(),
                        TextEntry::make('subject')->label('Тема письма')->placeholder('—')->columnSpanFull(),
                        TextEntry::make('body')->label('Текст сообщения')->columnSpanFull(),
                        TextEntry::make('updated_at')->label('Дата обновления')->dateTime('d.m.Y H:i'),
                    ]),
            ]);
    }
}
