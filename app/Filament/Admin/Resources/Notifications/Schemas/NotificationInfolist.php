<?php

namespace App\Filament\Admin\Resources\Notifications\Schemas;

use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class NotificationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Уведомление')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('user.email')->label('Пользователь'),
                        TextEntry::make('type')->label('Категория')->badge(),
                        TextEntry::make('title')->label('Заголовок')->columnSpanFull(),
                        TextEntry::make('body')->label('Текст')->placeholder('—')->columnSpanFull(),
                        TextEntry::make('action_url')->label('Ссылка при клике')->placeholder('—')->columnSpanFull(),
                        KeyValueEntry::make('data')->label('Доп. параметры')->columnSpanFull(),
                        TextEntry::make('read_at')->label('Прочитано')->dateTime('d.m.Y H:i')->placeholder('Нет'),
                        TextEntry::make('created_at')->label('Дата создания')->dateTime('d.m.Y H:i'),
                    ]),
            ]);
    }
}
