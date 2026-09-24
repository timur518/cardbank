<?php

namespace App\Filament\Admin\Resources\LegalDocuments\Schemas;

use App\Enums\LegalDocumentType;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LegalDocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Документ')
                    ->schema([
                        Select::make('type')
                            ->label('Тип документа')
                            ->options(LegalDocumentType::class)
                            ->required(),
                        TextInput::make('version')
                            ->label('Номер версии')
                            ->required()
                            ->maxLength(50),
                        DateTimePicker::make('effective_at')
                            ->label('Дата вступления в силу')
                            ->native(false)
                            ->displayFormat('d.m.Y H:i')
                            ->required(),
                        RichEditor::make('body')
                            ->label('Текст документа')
                            ->required()
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
