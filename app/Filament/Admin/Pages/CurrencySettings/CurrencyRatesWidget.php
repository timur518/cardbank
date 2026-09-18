<?php

namespace App\Filament\Admin\Pages\CurrencySettings;

use App\Models\Setting;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

/**
 * Виджет курсов валют для страницы «Валютная система». Значения хранятся в общей
 * таблице настроек и обновляются фоновой задачей (будет добавлена отдельно).
 * Специально не лежит в app/Filament/Admin/Widgets, чтобы не попадать на дашборд —
 * подключается вручную только на странице настроек валют.
 */
class CurrencyRatesWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $rates = Setting::getMany([
            'currency_rate_usd',
            'currency_rate_eur',
            'currency_rate_gbp',
            'currency_rates_updated_at',
        ]);

        return [
            $this->rateStat('Курс RUB → USD', $rates['currency_rate_usd']),
            $this->rateStat('Курс RUB → EUR', $rates['currency_rate_eur']),
            $this->rateStat('Курс RUB → GBP', $rates['currency_rate_gbp']),
            $this->updatedAtStat($rates['currency_rates_updated_at']),
        ];
    }

    protected function rateStat(string $label, ?string $rate): Stat
    {
        $value = $rate !== null ? number_format((float) $rate, 2, ',', ' ') . ' ₽' : '—';

        return Stat::make($label, $value)
            ->icon(Heroicon::OutlinedArrowsRightLeft)
            ->color('primary');
    }

    protected function updatedAtStat(?string $updatedAt): Stat
    {
        if ($updatedAt === null) {
            $value = 'Ещё не обновлялось';
        } else {
            $minutesAgo = (int) Carbon::parse($updatedAt)->diffInMinutes(now());
            $value = $minutesAgo . ' мин назад';
        }

        return Stat::make('Обновлено', $value)
            ->icon(Heroicon::OutlinedClock)
            ->color('gray');
    }
}
