<?php

namespace App\Http\Controllers\Api\V1;

use App\Filament\Admin\Pages\BrandSettings;
use App\Filament\Admin\Pages\ReferralSettings;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\CurrencyRateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    /**
     * GET /api/v1/settings/brand — см. CABINET_API_SPEC.md, п. 8.
     */
    public function brand(): JsonResponse
    {
        $data = Setting::getMany(BrandSettings::KEYS);

        if (! empty($data['brand_logo'])) {
            $data['brand_logo'] = Storage::disk('public')->url($data['brand_logo']);
        }

        return response()->json(['data' => $data]);
    }

    /**
     * GET /api/v1/settings/referral — см. CABINET_API_SPEC.md, п. 9.
     */
    public function referral(): JsonResponse
    {
        return response()->json(['data' => Setting::getMany(ReferralSettings::KEYS)]);
    }

    /**
     * GET /api/v1/settings/currency-rates — см. CABINET_API_SPEC.md, п. 10.
     * Отдаётся только итоговый курс продажи, без сырого курса ЦБ и наценки.
     */
    public function currencyRates(CurrencyRateService $rates): JsonResponse
    {
        $data = collect($rates->sellRates())
            ->map(fn (float $rate) => number_format($rate, 2, '.', ''))
            ->all();

        return response()->json(['data' => $data]);
    }
}
