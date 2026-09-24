<?php

namespace App\Filament\Admin\Resources\LegalDocuments\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LegalDocumentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Документ')
                    ->schema([
                        TextEntry::make('type')->label('Тип документа')->badge(),
                        TextEntry::make('version')->label('Версия'),
                        TextEntry::make('effective_at')->label('Дата вступления в силу')->dateTime('d.m.Y H:i'),
                        // Обычный TextEntry::make('body')->html() раньше давал “слипшийся” текст без
                        // отступов между абзацами: ->html() сам по себе не добавляет класс
                        // .fi-prose (его дают только ->prose()/->markdown()), а без него Tailwind Preflight
                        // админки обнуляет margin у <p>/<ul>/<h*> внутри dangerouslySetInnerHTML-блока.
                        // Вместо надежды на утилиту Filament/Tailwind рендерим через свой Blade-парциал с
                        // собственным <style> (абсолютно не зависит от Tailwind/prose-классов, как и в merchant-logo.blade.php) —
                        // гарантированно видные отступы между абзацами/списками/заголовками и на всю ширину секции (columnSpanFull).
                        ViewEntry::make('body')
                            ->label('Текст документа')
                            ->view('filament.infolists.entries.legal-document-body')
                            ->columnSpanFull(),
                        TextEntry::make('created_at')->label('Дата создания')->dateTime('d.m.Y H:i'),
                    ])
                ->columnSpanFull(),
            ]);
    }
}
