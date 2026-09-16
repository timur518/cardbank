<?php

namespace App\Filament\Admin\Resources\CardProviders\Pages;

use App\Filament\Admin\Resources\CardProviders\CardProviderResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditCardProvider extends EditRecord
{
    protected static string $resource = CardProviderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
