<?php

namespace App\Filament\Admin\Resources\ComplianceAlerts\Pages;

use App\Filament\Admin\Resources\ComplianceAlerts\ComplianceAlertResource;
use Filament\Resources\Pages\ListRecords;

class ListComplianceAlerts extends ListRecords
{
    protected static string $resource = ComplianceAlertResource::class;

    protected function getHeaderActions(): array
    {
        // Предупреждения создаются автоматически при срабатывании правил, ручное создание не предусмотрено.
        return [];
    }
}
