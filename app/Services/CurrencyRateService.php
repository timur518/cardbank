<?php

namespace App\Services;

use App\Models\Setting;

/**
 * Курс продажи валюты клиенту (с наценкой) — тот же расчёт, что и в
 * CurrencySettings::costHelperText(). Переиспользуется в SettingsController::currencyRates()
 * для отдачи курса клиенту и в OrderController для пересчёта суммы пополнения
 * между ₽ и $.
 */
class CurrencyRateService
{
    public const CURRENCIES = ['usd', 'eur', 'gbp'];

    /**
     * Сырой курс ЦБ, без наценки — используется только для внутренней бухгалтерии
     * (Income.amount_usd), клиенту никогда не отдаётся.
     */
    public function rawRate(string $currency): float
    {
        return (float) (Setting::get("currency_rate_{$currency}") ?? 0);
    }

    public function markupPercent(string $currency): float
    {
        return (float) (Setting::get("currency_markup_{$currency}_percent") ?? 0);
    }

    /**
     * Курс продажи клиенту: rate * (1 + markup / 100).
     */
    public function sellRate(string $currency): float
    {
        $rate = $this->rawRate($currency);
        $markup = $this->markupPercent($currency);

        return round($rate * (1 + $markup / 100), 2);
    }

    /**
     * @return array<string, float>
     */
    public function sellRates(): array
    {
        return collect(self::CURRENCIES)
            ->mapWithKeys(fn (string $currency) => [$currency => $this->sellRate($currency)])
            ->all();
    }
}
