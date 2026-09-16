<?php

namespace App\Filament\Admin\Resources\Cards;

use App\Filament\Admin\Resources\Cards\Pages\CreateCard;
use App\Filament\Admin\Resources\Cards\Pages\EditCard;
use App\Filament\Admin\Resources\Cards\Pages\ListCards;
use App\Filament\Admin\Resources\Cards\Pages\ViewCard;
use App\Filament\Admin\Resources\Cards\RelationManagers\ExpensesRelationManager;
use App\Filament\Admin\Resources\Cards\RelationManagers\IncomesRelationManager;
use App\Filament\Admin\Resources\Cards\RelationManagers\StatusHistoriesRelationManager;
use App\Filament\Admin\Resources\Cards\RelationManagers\TransactionsRelationManager;
use App\Filament\Admin\Resources\Cards\Schemas\CardForm;
use App\Filament\Admin\Resources\Cards\Schemas\CardInfolist;
use App\Filament\Admin\Resources\Cards\Tables\CardsTable;
use App\Models\Card;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CardResource extends Resource
{
    protected static ?string $model = Card::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static string|\UnitEnum|null $navigationGroup = 'Карты';

    protected static ?string $navigationLabel = 'Карты';

    protected static ?string $modelLabel = 'Карта';

    protected static ?string $pluralModelLabel = 'Карты';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return CardForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CardInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CardsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            TransactionsRelationManager::class,
            IncomesRelationManager::class,
            ExpensesRelationManager::class,
            StatusHistoriesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCards::route('/'),
            'create' => CreateCard::route('/create'),
            'view' => ViewCard::route('/{record}'),
            'edit' => EditCard::route('/{record}/edit'),
        ];
    }
}
