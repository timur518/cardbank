<?php

namespace App\Filament\Admin\Resources\CardProviders;

use App\Filament\Admin\Resources\CardProviders\Pages\CreateCardProvider;
use App\Filament\Admin\Resources\CardProviders\Pages\EditCardProvider;
use App\Filament\Admin\Resources\CardProviders\Pages\ListCardProviders;
use App\Filament\Admin\Resources\CardProviders\Pages\ViewCardProvider;
use App\Filament\Admin\Resources\CardProviders\RelationManagers\DiscrepanciesRelationManager;
use App\Filament\Admin\Resources\CardProviders\RelationManagers\MessagesRelationManager;
use App\Filament\Admin\Resources\CardProviders\RelationManagers\ReserveTopupsRelationManager;
use App\Filament\Admin\Resources\CardProviders\Schemas\CardProviderForm;
use App\Filament\Admin\Resources\CardProviders\Schemas\CardProviderInfolist;
use App\Filament\Admin\Resources\CardProviders\Tables\CardProvidersTable;
use App\Models\CardProvider;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CardProviderResource extends Resource
{
    protected static ?string $model = CardProvider::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingLibrary;

    protected static string|\UnitEnum|null $navigationGroup = 'Карты';

    protected static ?string $navigationLabel = 'Провайдеры карт';

    protected static ?string $modelLabel = 'Провайдер карт';

    protected static ?string $pluralModelLabel = 'Провайдеры карт';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return CardProviderForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CardProviderInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CardProvidersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ReserveTopupsRelationManager::class,
            MessagesRelationManager::class,
            DiscrepanciesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCardProviders::route('/'),
            'create' => CreateCardProvider::route('/create'),
            'view' => ViewCardProvider::route('/{record}'),
            'edit' => EditCardProvider::route('/{record}/edit'),
        ];
    }
}
