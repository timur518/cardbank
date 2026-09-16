<?php

namespace App\Filament\Admin\Resources\Refunds;

use App\Filament\Admin\Resources\Refunds\Pages\CreateRefund;
use App\Filament\Admin\Resources\Refunds\Pages\EditRefund;
use App\Filament\Admin\Resources\Refunds\Pages\ListRefunds;
use App\Filament\Admin\Resources\Refunds\Pages\ViewRefund;
use App\Filament\Admin\Resources\Refunds\Schemas\RefundForm;
use App\Filament\Admin\Resources\Refunds\Schemas\RefundInfolist;
use App\Filament\Admin\Resources\Refunds\Tables\RefundsTable;
use App\Models\Refund;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class RefundResource extends Resource
{
    protected static ?string $model = Refund::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedReceiptRefund;

    protected static string|\UnitEnum|null $navigationGroup = 'Финансы';

    protected static ?string $navigationLabel = 'Возвраты';

    protected static ?string $modelLabel = 'Возврат';

    protected static ?string $pluralModelLabel = 'Возвраты';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return RefundForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return RefundInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RefundsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRefunds::route('/'),
            'create' => CreateRefund::route('/create'),
            'view' => ViewRefund::route('/{record}'),
            'edit' => EditRefund::route('/{record}/edit'),
        ];
    }
}
