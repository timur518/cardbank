<?php

namespace App\Filament\Admin\Resources\ComplianceRules\Pages;

use App\Filament\Admin\Resources\ComplianceRules\ComplianceRuleResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditComplianceRule extends EditRecord
{
    protected static string $resource = ComplianceRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
