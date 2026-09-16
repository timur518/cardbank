<?php

namespace App\Filament\Admin\Resources\Broadcasts\Pages;

use App\Filament\Admin\Resources\Broadcasts\BroadcastResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewBroadcast extends ViewRecord
{
    protected static string $resource = BroadcastResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
