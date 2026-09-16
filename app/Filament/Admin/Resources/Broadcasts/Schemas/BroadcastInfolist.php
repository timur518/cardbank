<?php

namespace App\Filament\Admin\Resources\Broadcasts\Schemas;

use App\Enums\NotificationChannel;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BroadcastInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Рассылка')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('template.name')->label('Шаблон'),
                        TextEntry::make('status')->label('Статус')->badge(),
                        TextEntry::make('segment_filter')->label('Кому отправлено')->placeholder('—')->columnSpanFull(),
                        TextEntry::make('channels')
                            ->label('Каналы отправки')
                            ->badge()
                            ->formatStateUsing(fn (string $state) => NotificationChannel::from($state)->getLabel()),
                    ]),

                Section::make('Результаты рассылки')
                    ->columns(4)
                    ->schema([
                        TextEntry::make('recipients_count')->label('Получателей'),
                        TextEntry::make('delivered_count')->label('Доставлено'),
                        TextEntry::make('started_at')->label('Дата запуска')->dateTime('d.m.Y H:i')->placeholder('—'),
                        TextEntry::make('finished_at')->label('Дата завершения')->dateTime('d.m.Y H:i')->placeholder('—'),
                    ]),
            ]);
    }
}
