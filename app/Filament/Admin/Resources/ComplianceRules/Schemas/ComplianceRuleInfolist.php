<?php

namespace App\Filament\Admin\Resources\ComplianceRules\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ComplianceRuleInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Правило')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name')->label('Название'),
                        TextEntry::make('action')->label('Действие при срабатывании')->badge(),
                        TextEntry::make('condition')->label('Условие срабатывания')->columnSpanFull(),
                        TextEntry::make('threshold')->label('Пороговое значение')->placeholder('—'),
                        IconEntry::make('active')->label('Активно')->boolean(),
                        TextEntry::make('created_at')->label('Дата создания')->dateTime('d.m.Y H:i'),
                    ]),
            ]);
    }
}
