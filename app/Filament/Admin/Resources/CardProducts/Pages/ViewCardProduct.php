<?php

namespace App\Filament\Admin\Resources\CardProducts\Pages;

use App\Filament\Admin\Resources\CardProducts\CardProductResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCardProduct extends ViewRecord
{
    protected static string $resource = CardProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
