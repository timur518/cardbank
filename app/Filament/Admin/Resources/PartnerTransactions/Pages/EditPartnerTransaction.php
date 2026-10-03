<?php

namespace App\Filament\Admin\Resources\PartnerTransactions\Pages;

use App\Filament\Admin\Resources\PartnerTransactions\PartnerTransactionResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditPartnerTransaction extends EditRecord
{
    protected static string $resource = PartnerTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
