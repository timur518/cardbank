<?php

namespace App\Filament\Admin\Resources\CardProducts;

use App\Filament\Admin\Resources\CardProducts\Pages\CreateCardProduct;
use App\Filament\Admin\Resources\CardProducts\Pages\EditCardProduct;
use App\Filament\Admin\Resources\CardProducts\Pages\ListCardProducts;
use App\Filament\Admin\Resources\CardProducts\Pages\ViewCardProduct;
use App\Filament\Admin\Resources\CardProducts\Schemas\CardProductForm;
use App\Filament\Admin\Resources\CardProducts\Schemas\CardProductInfolist;
use App\Filament\Admin\Resources\CardProducts\Tables\CardProductsTable;
use App\Models\CardProduct;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CardProductResource extends Resource
{
    protected static ?string $model = CardProduct::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|\UnitEnum|null $navigationGroup = 'Карты';

    protected static ?string $navigationLabel = 'Карточные продукты';

    protected static ?string $modelLabel = 'Карточный продукт';

    protected static ?string $pluralModelLabel = 'Карточные продукты';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return CardProductForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CardProductInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CardProductsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCardProducts::route('/'),
            'create' => CreateCardProduct::route('/create'),
            'view' => ViewCardProduct::route('/{record}'),
            'edit' => EditCardProduct::route('/{record}/edit'),
        ];
    }
}
