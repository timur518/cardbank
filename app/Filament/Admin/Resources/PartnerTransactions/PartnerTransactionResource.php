<?php

namespace App\Filament\Admin\Resources\PartnerTransactions;

use App\Filament\Admin\Resources\PartnerTransactions\Pages\CreatePartnerTransaction;
use App\Filament\Admin\Resources\PartnerTransactions\Pages\EditPartnerTransaction;
use App\Filament\Admin\Resources\PartnerTransactions\Pages\ListPartnerTransactions;
use App\Filament\Admin\Resources\PartnerTransactions\Pages\ViewPartnerTransaction;
use App\Filament\Admin\Resources\PartnerTransactions\Schemas\PartnerTransactionForm;
use App\Filament\Admin\Resources\PartnerTransactions\Schemas\PartnerTransactionInfolist;
use App\Filament\Admin\Resources\PartnerTransactions\Tables\PartnerTransactionsTable;
use App\Models\PartnerTransaction;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PartnerTransactionResource extends Resource
{
    protected static ?string $model = PartnerTransaction::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedReceiptPercent;

    protected static string|\UnitEnum|null $navigationGroup = 'Партнерка';

    protected static ?string $navigationLabel = 'Партнёрские транзакции';

    protected static ?string $modelLabel = 'Партнёрская транзакция';

    protected static ?string $pluralModelLabel = 'Партнёрские транзакции';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return PartnerTransactionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PartnerTransactionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PartnerTransactionsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPartnerTransactions::route('/'),
            'create' => CreatePartnerTransaction::route('/create'),
            'view' => ViewPartnerTransaction::route('/{record}'),
            'edit' => EditPartnerTransaction::route('/{record}/edit'),
        ];
    }
}
