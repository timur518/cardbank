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
     * Отдаёт публичные настройки бренда (логотип, контакты и т.п. из
     * BrandSettings::KEYS) для оформления интерфейса личного кабинета.
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
     * Отдаёт настройки реферальной программы (ReferralSettings::KEYS) для
     * блока «peригласите друга» в личном кабинете.
     */
    public function referral(): JsonResponse
    {
        return response()->json(['data' => Setting::getMany(ReferralSettings::KEYS)]);
    }

    /**
     * Отдаёт итоговый курс продажи валют (с наценкой к курсу ЦБ РФ) — именно
     * его фронтенд использует для расчёта суммы в рублях при оплате в валюте;
     * сырой курс ЦБ и наценка клиенту не показываются.
     */
    public function currencyRates(CurrencyRateService $rates): JsonResponse
    {
        $data = collect($rates->sellRates())
            ->map(fn (float $rate) => number_format($rate, 2, '.', ''))
            ->all();

        return response()->json(['data' => $data]);
    }
}
