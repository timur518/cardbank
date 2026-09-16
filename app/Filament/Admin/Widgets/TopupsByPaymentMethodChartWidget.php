<?php

namespace App\Filament\Admin\Widgets;

use App\Enums\IncomePaymentStatus;
use App\Enums\IncomeType;
use App\Models\Income;
use Filament\Widgets\ChartWidget;

/**
 * «Пополнения по способам оплаты» — сколько денег прошло через каждый способ приёма
 * платежей. Суммы по всем валютам складываются по номиналу без учёта курса.
 */
class TopupsByPaymentMethodChartWidget extends ChartWidget
{
    protected static ?int $sort = 4;

    protected function getType(): string
    {
        return 'doughnut';
    }

    public function getHeading(): string
    {
        return 'Пополнения по способам оплаты';
    }

    protected function getData(): array
    {
        $rows = Income::query()
            ->where('type', IncomeType::CardTopup)
            ->where('payment_status', IncomePaymentStatus::Paid)
            ->whereNotNull('payment_method_id')
            ->with('paymentMethod')
            ->get()
            ->groupBy(fn (Income $income) => $income->paymentMethod?->name ?? 'Без способа оплаты')
            ->map(fn ($group) => (float) $group->sum('amount'));

        return [
            'datasets' => [
                [
                    'data' => $rows->values()->all(),
                    'backgroundColor' => [
                        '#3b82f6', '#22c55e', '#f97316', '#a855f7', '#eab308', '#ef4444', '#06b6d4',
                    ],
                ],
            ],
            'labels' => $rows->keys()->all(),
        ];
    }
}
