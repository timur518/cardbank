<?php

namespace App\Filament\Admin\Resources\ComplianceAlerts\Pages;

use App\Filament\Admin\Resources\ComplianceAlerts\ComplianceAlertResource;
use Filament\Resources\Pages\ViewRecord;

class ViewComplianceAlert extends ViewRecord
{
    protected static string $resource = ComplianceAlertResource::class;

    protected function getHeaderActions(): array
    {
        // Редактирование и удаление записей не предусмотрено, разбор — через действия в таблице.
        return [];
    }
}
