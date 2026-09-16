<?php

namespace App\Filament\Admin\Resources\CardTransactions\Pages;

use App\Filament\Admin\Resources\CardTransactions\CardTransactionResource;
use Filament\Resources\Pages\ViewRecord;

class ViewCardTransaction extends ViewRecord
{
    protected static string $resource = CardTransactionResource::class;

    protected function getHeaderActions(): array
    {
        // Раздел заполняется автоматически, редактирование и удаление записей не предусмотрено.
        return [];
    }
}
