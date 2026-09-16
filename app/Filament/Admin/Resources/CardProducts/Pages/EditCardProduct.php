<?php

namespace App\Filament\Admin\Resources\CardProducts\Pages;

use App\Filament\Admin\Resources\CardProducts\CardProductResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditCardProduct extends EditRecord
{
    protected static string $resource = CardProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
