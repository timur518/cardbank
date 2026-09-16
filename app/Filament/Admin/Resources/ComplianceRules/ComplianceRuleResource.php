<?php

namespace App\Filament\Admin\Resources\ComplianceRules;

use App\Filament\Admin\Resources\ComplianceRules\Pages\CreateComplianceRule;
use App\Filament\Admin\Resources\ComplianceRules\Pages\EditComplianceRule;
use App\Filament\Admin\Resources\ComplianceRules\Pages\ListComplianceRules;
use App\Filament\Admin\Resources\ComplianceRules\Pages\ViewComplianceRule;
use App\Filament\Admin\Resources\ComplianceRules\Schemas\ComplianceRuleForm;
use App\Filament\Admin\Resources\ComplianceRules\Schemas\ComplianceRuleInfolist;
use App\Filament\Admin\Resources\ComplianceRules\Tables\ComplianceRulesTable;
use App\Models\ComplianceRule;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ComplianceRuleResource extends Resource
{
    protected static ?string $model = ComplianceRule::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldExclamation;

    protected static string|\UnitEnum|null $navigationGroup = 'Комплаенс';

    protected static ?string $navigationLabel = 'Правила';

    protected static ?string $modelLabel = 'Правило';

    protected static ?string $pluralModelLabel = 'Правила';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return ComplianceRuleForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ComplianceRuleInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ComplianceRulesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListComplianceRules::route('/'),
            'create' => CreateComplianceRule::route('/create'),
            'view' => ViewComplianceRule::route('/{record}'),
            'edit' => EditComplianceRule::route('/{record}/edit'),
        ];
    }
}
