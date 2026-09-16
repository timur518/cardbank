<?php

namespace App\Filament\Admin\Resources\Broadcasts\Schemas;

use App\Enums\BroadcastStatus;
use App\Enums\NotificationChannel;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BroadcastForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Рассылка')
                    ->columns(2)
                    ->schema([
                        Select::make('template_id')
                            ->label('Используемый шаблон')
                            ->relationship('template', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('status')
                            ->label('Статус')
                            ->options(BroadcastStatus::class)
                            ->default(BroadcastStatus::Draft)
                            ->required(),
                        TextInput::make('segment_filter')
                            ->label('Кому отправить')
                            ->datalist([
                                'Всем',
                                'Только тем, у кого есть активная карта',
                                'Только тем, у кого истекла проверка личности',
                            ])
                            ->helperText('Выберите готовый вариант из подсказки или опишите своё условие отбора получателей.')
                            ->columnSpanFull(),
                        CheckboxList::make('channels')
                            ->label('Каналы отправки')
                            ->options(NotificationChannel::class)
                            ->required()
                            ->columnSpanFull(),
                    ]),

                Section::make('Результаты рассылки')
                    ->columns(4)
                    ->schema([
                        TextInput::make('recipients_count')
                            ->label('Количество получателей')
                            ->numeric()
                            ->default(0),
                        TextInput::make('delivered_count')
                            ->label('Количество доставленных')
                            ->numeric()
                            ->default(0),
                        DateTimePicker::make('started_at')
                            ->label('Дата запуска')
                            ->native(false)
                            ->displayFormat('d.m.Y H:i'),
                        DateTimePicker::make('finished_at')
                            ->label('Дата завершения')
                            ->native(false)
                            ->displayFormat('d.m.Y H:i'),
                    ]),
            ]);
    }
}
