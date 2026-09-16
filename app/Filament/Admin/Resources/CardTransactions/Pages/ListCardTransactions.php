<?php

namespace App\Filament\Admin\Resources\CardTransactions\Pages;

use App\Filament\Admin\Resources\CardTransactions\CardTransactionResource;
use Filament\Resources\Pages\ListRecords;

class ListCardTransactions extends ListRecords
{
    protected static string $resource = CardTransactionResource::class;

    protected function getHeaderActions(): array
    {
        // Раздел заполняется автоматически по данным от карточного провайдера, ручное создание не предусмотрено.
        return [];
    }
}
