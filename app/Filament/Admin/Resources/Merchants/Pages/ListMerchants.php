<?php

namespace App\Filament\Admin\Resources\Merchants\Pages;

use App\Filament\Admin\Pages\MerchantProductRatesPage;
use App\Filament\Admin\Resources\Merchants\MerchantResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListMerchants extends ListRecords
{
    protected static string $resource = MerchantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('rates')
                ->label('Рейтинг платежей')
                ->icon(Heroicon::OutlinedChartBar)
                ->color('gray')
                ->url(MerchantProductRatesPage::getUrl()),
            CreateAction::make()->label('Добавить мерчанта'),
        ];
    }
}
