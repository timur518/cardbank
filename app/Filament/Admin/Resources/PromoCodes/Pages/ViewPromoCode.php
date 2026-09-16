<?php

namespace App\Filament\Admin\Resources\PromoCodes\Pages;

use App\Filament\Admin\Resources\PromoCodes\PromoCodeResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPromoCode extends ViewRecord
{
    protected static string $resource = PromoCodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
