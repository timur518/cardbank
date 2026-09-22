<?php

namespace App\Filament\Admin\Resources\CardTransactions;

use App\Filament\Admin\Resources\CardTransactions\Pages\ListCardTransactions;
use App\Filament\Admin\Resources\CardTransactions\Pages\ViewCardTransaction;
use App\Filament\Admin\Resources\CardTransactions\Schemas\CardTransactionInfolist;
use App\Filament\Admin\Resources\CardTransactions\Tables\CardTransactionsTable;
use App\Models\CardTransaction;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CardTransactionResource extends Resource
{
    protected static ?string $model = CardTransaction::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsRightLeft;

    protected static string|\UnitEnum|null $navigationGroup = 'Карты';

    protected static ?string $navigationLabel = 'Транзакции по картам';

    protected static ?string $modelLabel = 'Транзакция по карте';

    protected static ?string $pluralModelLabel = 'Транзакции по картам';

    protected static ?int $navigationSort = 2;

    public static function infolist(Schema $schema): Schema
    {
        return CardTransactionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CardTransactionsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCardTransactions::route('/'),
            'view' => ViewCardTransaction::route('/{record}'),
        ];
    }
}
