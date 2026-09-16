<?php

namespace App\Filament\Admin\Resources\LegalDocuments\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LegalDocumentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Документ')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('type')->label('Тип документа')->badge(),
                        TextEntry::make('version')->label('Версия'),
                        TextEntry::make('effective_at')->label('Дата вступления в силу')->dateTime('d.m.Y H:i'),
                        TextEntry::make('body')->label('Текст документа')->html()->columnSpanFull(),
                        TextEntry::make('created_at')->label('Дата создания')->dateTime('d.m.Y H:i'),
                    ]),
            ]);
    }
}
