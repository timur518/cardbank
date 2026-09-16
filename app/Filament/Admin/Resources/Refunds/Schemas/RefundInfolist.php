<?php

namespace App\Filament\Admin\Resources\Refunds\Schemas;

use App\Models\Refund;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RefundInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Заявка на возврат')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('id')->label('ID'),
                        TextEntry::make('user.email')->label('Пользователь'),
                        TextEntry::make('card.masked_number')->label('Карта'),
                        TextEntry::make('amount')->label('Сумма')->money(fn (Refund $record) => $record->currency),
                        TextEntry::make('status')
                            ->label('Статус')
                            ->badge(),
                        TextEntry::make('created_at')->label('Дата создания')->dateTime('d.m.Y H:i'),
                        TextEntry::make('reason')->label('Причина')->placeholder('—')->columnSpanFull(),
                        TextEntry::make('resolver.name')->label('Кто принял решение')->placeholder('—'),
                        TextEntry::make('resolved_at')->label('Дата решения')->dateTime('d.m.Y H:i')->placeholder('—'),
                    ]),
            ]);
    }
}
