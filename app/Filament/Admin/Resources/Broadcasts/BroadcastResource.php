<?php

namespace App\Filament\Admin\Resources\Broadcasts;

use App\Filament\Admin\Resources\Broadcasts\Pages\CreateBroadcast;
use App\Filament\Admin\Resources\Broadcasts\Pages\EditBroadcast;
use App\Filament\Admin\Resources\Broadcasts\Pages\ListBroadcasts;
use App\Filament\Admin\Resources\Broadcasts\Pages\ViewBroadcast;
use App\Filament\Admin\Resources\Broadcasts\Schemas\BroadcastForm;
use App\Filament\Admin\Resources\Broadcasts\Schemas\BroadcastInfolist;
use App\Filament\Admin\Resources\Broadcasts\Tables\BroadcastsTable;
use App\Models\Broadcast;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BroadcastResource extends Resource
{
    protected static ?string $model = Broadcast::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMegaphone;

    protected static string|\UnitEnum|null $navigationGroup = 'Маркетинг';

    protected static ?string $navigationLabel = 'Рассылки';

    protected static ?string $modelLabel = 'Рассылка';

    protected static ?string $pluralModelLabel = 'Рассылки';

    protected static ?int $navigationSort = 7;

    public static function form(Schema $schema): Schema
    {
        return BroadcastForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return BroadcastInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BroadcastsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBroadcasts::route('/'),
            'create' => CreateBroadcast::route('/create'),
            'view' => ViewBroadcast::route('/{record}'),
            'edit' => EditBroadcast::route('/{record}/edit'),
        ];
    }
}
