<?php

namespace App\Filament\Admin\Resources\PartnerTransactions\Pages;

use App\Filament\Admin\Resources\PartnerTransactions\PartnerTransactionResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPartnerTransaction extends ViewRecord
{
    protected static string $resource = PartnerTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
