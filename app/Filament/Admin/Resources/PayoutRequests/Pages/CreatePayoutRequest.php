<?php

namespace App\Filament\Admin\Resources\PayoutRequests\Pages;

use App\Filament\Admin\Resources\PayoutRequests\PayoutRequestResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePayoutRequest extends CreateRecord
{
    protected static string $resource = PayoutRequestResource::class;
}
