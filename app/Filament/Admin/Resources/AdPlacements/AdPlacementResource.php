<?php

namespace App\Filament\Admin\Resources\AdPlacements;

use App\Filament\Admin\Resources\AdPlacements\Pages\CreateAdPlacement;
use App\Filament\Admin\Resources\AdPlacements\Pages\EditAdPlacement;
use App\Filament\Admin\Resources\AdPlacements\Pages\ListAdPlacements;
use App\Filament\Admin\Resources\AdPlacements\Pages\ViewAdPlacement;
use App\Filament\Admin\Resources\AdPlacements\Schemas\AdPlacementForm;
use App\Filament\Admin\Resources\AdPlacements\Schemas\AdPlacementInfolist;
use App\Filament\Admin\Resources\AdPlacements\Tables\AdPlacementsTable;
use App\Models\AdPlacement;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AdPlacementResource extends Resource
{
    protected static ?string $model = AdPlacement::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSpeakerWave;

    protected static string|\UnitEnum|null $navigationGroup = 'Маркетинг';

    protected static ?string $navigationLabel = 'Рекламные размещения';

    protected static ?string $modelLabel = 'Рекламное размещение';

    protected static ?string $pluralModelLabel = 'Рекламные размещения';

    protected static ?int $navigationSort = 8;

    public static function form(Schema $schema): Schema
    {
        return AdPlacementForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AdPlacementInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AdPlacementsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAdPlacements::route('/'),
            'create' => CreateAdPlacement::route('/create'),
            'view' => ViewAdPlacement::route('/{record}'),
            'edit' => EditAdPlacement::route('/{record}/edit'),
        ];
    }
}
