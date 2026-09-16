<?php

namespace App\Filament\Admin\Resources\PromoCodes;

use App\Filament\Admin\Resources\PromoCodes\Pages\CreatePromoCode;
use App\Filament\Admin\Resources\PromoCodes\Pages\EditPromoCode;
use App\Filament\Admin\Resources\PromoCodes\Pages\ListPromoCodes;
use App\Filament\Admin\Resources\PromoCodes\Pages\ViewPromoCode;
use App\Filament\Admin\Resources\PromoCodes\RelationManagers\UsagesRelationManager;
use App\Filament\Admin\Resources\PromoCodes\Schemas\PromoCodeForm;
use App\Filament\Admin\Resources\PromoCodes\Schemas\PromoCodeInfolist;
use App\Filament\Admin\Resources\PromoCodes\Tables\PromoCodesTable;
use App\Models\PromoCode;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PromoCodeResource extends Resource
{
    protected static ?string $model = PromoCode::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTicket;

    protected static string|\UnitEnum|null $navigationGroup = 'Маркетинг';

    protected static ?string $navigationLabel = 'Промокоды и акции';

    protected static ?string $modelLabel = 'Промокод';

    protected static ?string $pluralModelLabel = 'Промокоды';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return PromoCodeForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PromoCodeInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PromoCodesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            UsagesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPromoCodes::route('/'),
            'create' => CreatePromoCode::route('/create'),
            'view' => ViewPromoCode::route('/{record}'),
            'edit' => EditPromoCode::route('/{record}/edit'),
        ];
    }
}
