<?php

namespace App\Filament\Admin\Resources\CardProviders\Pages;

use App\Filament\Admin\Resources\CardProviders\CardProviderResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCardProvider extends ViewRecord
{
    protected static string $resource = CardProviderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
