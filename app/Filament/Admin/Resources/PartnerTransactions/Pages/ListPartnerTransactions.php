<?php

namespace App\Filament\Admin\Resources\PartnerTransactions\Pages;

use App\Filament\Admin\Resources\PartnerTransactions\PartnerTransactionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPartnerTransactions extends ListRecords
{
    protected static string $resource = PartnerTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
