<?php

namespace App\Filament\Admin\Resources\PayoutRequests\Pages;

use App\Filament\Admin\Resources\PayoutRequests\PayoutRequestResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPayoutRequest extends ViewRecord
{
    protected static string $resource = PayoutRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
