<?php

namespace App\Filament\Admin\Resources\ComplianceAlerts\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ComplianceAlertInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Предупреждение')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('id')->label('ID'),
                        TextEntry::make('rule.name')->label('Правило'),
                        TextEntry::make('status')->label('Статус')->badge(),
                        TextEntry::make('user.email')->label('Пользователь')->placeholder('—'),
                        TextEntry::make('card.masked_number')->label('Карта')->placeholder('—'),
                        TextEntry::make('created_at')->label('Дата срабатывания')->dateTime('d.m.Y H:i'),
                        TextEntry::make('resolver.name')->label('Кто разобрал')->placeholder('—'),
                        TextEntry::make('resolved_at')->label('Дата решения')->dateTime('d.m.Y H:i')->placeholder('—'),
                    ]),
            ]);
    }
}
