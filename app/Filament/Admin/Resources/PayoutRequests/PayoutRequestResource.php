<?php

namespace App\Filament\Admin\Resources\PayoutRequests;

use App\Filament\Admin\Resources\PayoutRequests\Pages\CreatePayoutRequest;
use App\Filament\Admin\Resources\PayoutRequests\Pages\EditPayoutRequest;
use App\Filament\Admin\Resources\PayoutRequests\Pages\ListPayoutRequests;
use App\Filament\Admin\Resources\PayoutRequests\Pages\ViewPayoutRequest;
use App\Filament\Admin\Resources\PayoutRequests\Schemas\PayoutRequestForm;
use App\Filament\Admin\Resources\PayoutRequests\Schemas\PayoutRequestInfolist;
use App\Filament\Admin\Resources\PayoutRequests\Tables\PayoutRequestsTable;
use App\Models\PayoutRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PayoutRequestResource extends Resource
{
    protected static ?string $model = PayoutRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static string|\UnitEnum|null $navigationGroup = 'Маркетинг';

    protected static ?string $navigationLabel = 'Заявки на выплату';

    protected static ?string $modelLabel = 'Заявка на выплату';

    protected static ?string $pluralModelLabel = 'Заявки на выплату';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return PayoutRequestForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PayoutRequestInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PayoutRequestsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPayoutRequests::route('/'),
            'create' => CreatePayoutRequest::route('/create'),
            'view' => ViewPayoutRequest::route('/{record}'),
            'edit' => EditPayoutRequest::route('/{record}/edit'),
        ];
    }
}
