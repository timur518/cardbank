<?php

namespace App\Filament\Admin\Resources\Partners;

use App\Filament\Admin\Resources\Partners\Pages\ListPartners;
use App\Filament\Admin\Resources\Partners\Pages\ViewPartner;
use App\Filament\Admin\Resources\Partners\RelationManagers\PartnerTransactionsRelationManager;
use App\Filament\Admin\Resources\Partners\Schemas\PartnerInfolist;
use App\Filament\Admin\Resources\Partners\Tables\PartnersTable;
use App\Models\Partner;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

/**
 * Только просмотр: партнёры — это пользователи с приглашёнными (см. App\Models\Partner),
 * заводить/редактировать их вручную здесь нельзя, поэтому ресурс не регистрирует
 * create/edit-страницы.
 */
class PartnerResource extends Resource
{
    protected static ?string $model = Partner::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|\UnitEnum|null $navigationGroup = 'Партнерка';

    protected static ?string $navigationLabel = 'Партнёры';

    protected static ?string $modelLabel = 'Партнёр';

    protected static ?string $pluralModelLabel = 'Партнёры';

    protected static ?int $navigationSort = 1;

    public static function infolist(Schema $schema): Schema
    {
        return PartnerInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PartnersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            PartnerTransactionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPartners::route('/'),
            'view' => ViewPartner::route('/{record}'),
        ];
    }
}
