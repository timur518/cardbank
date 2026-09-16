<?php

namespace App\Filament\Admin\Resources\CardProviders\Pages;

use App\Filament\Admin\Resources\CardProviders\CardProviderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCardProviders extends ListRecords
{
    protected static string $resource = CardProviderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
