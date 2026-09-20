<?php

namespace App\Filament\Admin\Resources\Merchants;

use App\Filament\Admin\Resources\Merchants\Pages\CreateMerchant;
use App\Filament\Admin\Resources\Merchants\Pages\EditMerchant;
use App\Filament\Admin\Resources\Merchants\Pages\ListMerchants;
use App\Filament\Admin\Resources\Merchants\Pages\ViewMerchant;
use App\Filament\Admin\Resources\Merchants\Schemas\MerchantForm;
use App\Filament\Admin\Resources\Merchants\Schemas\MerchantInfolist;
use App\Filament\Admin\Resources\Merchants\Tables\MerchantsTable;
use App\Models\Merchant;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MerchantResource extends Resource
{
    protected static ?string $model = Merchant::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingStorefront;

    protected static string|\UnitEnum|null $navigationGroup = 'Настройки';

    protected static ?string $navigationLabel = 'Мерчанты';

    protected static ?string $modelLabel = 'Мерчант';

    protected static ?string $pluralModelLabel = 'Мерчанты';

    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return MerchantForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MerchantInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MerchantsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMerchants::route('/'),
            'create' => CreateMerchant::route('/create'),
            'view' => ViewMerchant::route('/{record}'),
            'edit' => EditMerchant::route('/{record}/edit'),
        ];
    }
}
