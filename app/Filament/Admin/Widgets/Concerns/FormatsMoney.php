<?php

namespace App\Filament\Admin\Widgets\Concerns;

/**
 * Общее форматирование денежных сумм для виджетов главной страницы.
 */
trait FormatsMoney
{
    protected function formatMoney(float|int|string $amount, string $currency): string
    {
        return number_format((float) $amount, 2, ',', ' ') . ' ' . $currency;
    }

    /**
     * Собрать суммы по валютам в одну строку вида «12 345,00 RUB · 100,00 USD».
     *
     * @param  array<string, float|int|string>  $amountsByCurrency
     */
    protected function formatMoneyByCurrency(array $amountsByCurrency): string
    {
        if ($amountsByCurrency === []) {
            return '—';
        }

        return collect($amountsByCurrency)
            ->map(fn ($amount, $currency) => $this->formatMoney($amount, $currency))
            ->implode(' · ');
    }
}
