<?php

namespace App\Filament\Admin\Resources\Notifications\Schemas;

use App\Enums\NotificationType;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class NotificationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Уведомление')
                    ->columns(2)
                    ->schema([
                        Select::make('user_id')
                            ->label('Пользователь')
                            ->relationship('user', 'email')
                            ->searchable()
                            ->required(),
                        Select::make('type')
                            ->label('Категория')
                            ->options(NotificationType::class)
                            ->required(),
                        TextInput::make('title')
                            ->label('Заголовок')
                            ->required()
                            ->maxLength(191)
                            ->columnSpanFull(),
                        Textarea::make('body')
                            ->label('Текст')
                            ->rows(3)
                            ->columnSpanFull(),
                        TextInput::make('action_url')
                            ->label('Ссылка при клике')
                            ->helperText('Маршрут в ЛК (например /cards/{uuid}) или внешняя ссылка.')
                            ->columnSpanFull(),
                        KeyValue::make('data')
                            ->label('Доп. параметры')
                            ->keyLabel('Ключ')
                            ->valueLabel('Значение')
                            ->columnSpanFull(),
                        DateTimePicker::make('read_at')
                            ->label('Прочитано')
                            ->helperText('Оставьте пустым, если уведомление ещё не прочитано.')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
