<?php

namespace App\Filament\Admin\Resources\ComplianceRules\Pages;

use App\Filament\Admin\Resources\ComplianceRules\ComplianceRuleResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewComplianceRule extends ViewRecord
{
    protected static string $resource = ComplianceRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
