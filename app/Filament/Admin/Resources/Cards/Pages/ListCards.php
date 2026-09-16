<?php

namespace App\Filament\Admin\Resources\Cards\Pages;

use App\Filament\Admin\Resources\Cards\CardResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCards extends ListRecords
{
    protected static string $resource = CardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
