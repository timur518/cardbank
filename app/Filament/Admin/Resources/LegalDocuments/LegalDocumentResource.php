<?php

namespace App\Filament\Admin\Resources\LegalDocuments;

use App\Filament\Admin\Resources\LegalDocuments\Pages\CreateLegalDocument;
use App\Filament\Admin\Resources\LegalDocuments\Pages\EditLegalDocument;
use App\Filament\Admin\Resources\LegalDocuments\Pages\ListLegalDocuments;
use App\Filament\Admin\Resources\LegalDocuments\Pages\ViewLegalDocument;
use App\Filament\Admin\Resources\LegalDocuments\Schemas\LegalDocumentForm;
use App\Filament\Admin\Resources\LegalDocuments\Schemas\LegalDocumentInfolist;
use App\Filament\Admin\Resources\LegalDocuments\Tables\LegalDocumentsTable;
use App\Models\LegalDocument;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LegalDocumentResource extends Resource
{
    protected static ?string $model = LegalDocument::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|\UnitEnum|null $navigationGroup = 'Настройки';

    protected static ?string $navigationLabel = 'Юридические документы';

    protected static ?string $modelLabel = 'Документ';

    protected static ?string $pluralModelLabel = 'Юридические документы';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return LegalDocumentForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return LegalDocumentInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LegalDocumentsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLegalDocuments::route('/'),
            'create' => CreateLegalDocument::route('/create'),
            'view' => ViewLegalDocument::route('/{record}'),
            'edit' => EditLegalDocument::route('/{record}/edit'),
        ];
    }
}
