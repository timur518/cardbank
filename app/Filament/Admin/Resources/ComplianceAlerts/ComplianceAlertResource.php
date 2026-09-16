<?php

namespace App\Filament\Admin\Resources\ComplianceAlerts;

use App\Filament\Admin\Resources\ComplianceAlerts\Pages\ListComplianceAlerts;
use App\Filament\Admin\Resources\ComplianceAlerts\Pages\ViewComplianceAlert;
use App\Filament\Admin\Resources\ComplianceAlerts\Schemas\ComplianceAlertInfolist;
use App\Filament\Admin\Resources\ComplianceAlerts\Tables\ComplianceAlertsTable;
use App\Models\ComplianceAlert;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ComplianceAlertResource extends Resource
{
    protected static ?string $model = ComplianceAlert::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBellAlert;

    protected static string|\UnitEnum|null $navigationGroup = 'Комплаенс';

    protected static ?string $navigationLabel = 'Предупреждения';

    protected static ?string $modelLabel = 'Предупреждение';

    protected static ?string $pluralModelLabel = 'Предупреждения';

    protected static ?int $navigationSort = 2;

    public static function infolist(Schema $schema): Schema
    {
        return ComplianceAlertInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ComplianceAlertsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListComplianceAlerts::route('/'),
            'view' => ViewComplianceAlert::route('/{record}'),
        ];
    }
}
