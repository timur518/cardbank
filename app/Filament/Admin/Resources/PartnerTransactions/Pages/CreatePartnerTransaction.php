<?php

namespace App\Filament\Admin\Resources\PartnerTransactions\Pages;

use App\Filament\Admin\Resources\PartnerTransactions\PartnerTransactionResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePartnerTransaction extends CreateRecord
{
    protected static string $resource = PartnerTransactionResource::class;
}
