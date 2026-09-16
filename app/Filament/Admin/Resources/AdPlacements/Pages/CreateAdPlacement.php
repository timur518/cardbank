<?php

namespace App\Filament\Admin\Resources\AdPlacements\Pages;

use App\Filament\Admin\Resources\AdPlacements\AdPlacementResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAdPlacement extends CreateRecord
{
    protected static string $resource = AdPlacementResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        $data['tracking_link'] = $this->buildTrackingLink($data);

        return $data;
    }

    protected function buildTrackingLink(array $data): string
    {
        $query = array_filter([
            'utm_source' => $data['utm_source'] ?? null,
            'utm_medium' => $data['utm_medium'] ?? null,
            'utm_campaign' => $data['utm_campaign'] ?? null,
            'utm_content' => $data['utm_content'] ?? null,
        ]);

        return rtrim(config('app.url'), '/') . '/?' . http_build_query($query);
    }
}
