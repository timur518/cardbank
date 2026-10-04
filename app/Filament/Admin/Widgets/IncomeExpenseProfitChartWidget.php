<?php

namespace App\Filament\Admin\Widgets;

use App\Enums\IncomePaymentStatus;
use App\Enums\IncomeType;
use App\Models\Expense;
use App\Models\Income;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * «Поступления, расходы и прибыль» — три линии в рублях: сколько заплатили клиенты за
 * выпуск и пополнения карт (Income.amount, всегда RUB — клиент платит через СБП), сколько
 * потратили (Expense.amount, тоже всегда RUB — см. ExpenseForm) и итоговая прибыль (разница).
 */
class IncomeExpenseProfitChartWidget extends ChartWidget
{
    protected static ?int $sort = 3;

    public ?string $filter = 'day';

    protected int|string|array $columnSpan = 'full';

    protected ?string $maxHeight = '350px';

    protected function getType(): string
    {
        return 'line';
    }

    public function getHeading(): string
    {
        return 'Поступления, расходы и прибыль';
    }

    protected function getFilters(): ?array
    {
        return [
            'day' => 'По дням',
            'week' => 'По неделям',
            'month' => 'По месяцам',
        ];
    }

    protected function getData(): array
    {
        $periods = $this->periods();

        $receipts = $this->sumIncomeByPeriod($periods, [IncomeType::CardIssue, IncomeType::CardTopup]);
        $expenses = $this->sumExpenseByPeriod($periods);

        $profit = $periods->map(fn ($period, $key) => $receipts[$key] - $expenses[$key]);

        return [
            'datasets' => [
                [
                    'label' => 'Поступления',
                    'data' => $receipts->values()->all(),
                    'borderColor' => '#22c55e',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.15)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Расходы',
                    'data' => $expenses->values()->all(),
                    'borderColor' => '#ef4444',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.15)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Прибыль',
                    'data' => $profit->values()->all(),
                    'borderColor' => '#a855f7',
                    'backgroundColor' => 'rgba(168, 85, 247, 0.15)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $periods->keys()->all(),
        ];
    }

    /**
     * Границы периодов для графика: ключ — подпись на оси, значение — [начало, конец].
     *
     * @return Collection<string, array{0: Carbon, 1: Carbon}>
     */
    protected function periods(): Collection
    {
        return match ($this->filter) {
            'week' => collect(range(11, 0))->mapWithKeys(function (int $weeksAgo) {
                $start = now()->subWeeks($weeksAgo)->startOfWeek();
                $end = $start->clone()->endOfWeek();

                return [$start->format('d.m') => [$start, $end]];
            }),
            'month' => collect(range(11, 0))->mapWithKeys(function (int $monthsAgo) {
                $start = now()->subMonths($monthsAgo)->startOfMonth();
                $end = $start->clone()->endOfMonth();

                return [$start->format('m.Y') => [$start, $end]];
            }),
            default => collect(range(13, 0))->mapWithKeys(function (int $daysAgo) {
                $start = now()->subDays($daysAgo)->startOfDay();
                $end = $start->clone()->endOfDay();

                return [$start->format('d.m') => [$start, $end]];
            }),
        };
    }

    /**
     * @param  Collection<string, array{0: Carbon, 1: Carbon}>  $periods
     * @param  array<IncomeType>  $types
     * @return Collection<string, float>
     */
    protected function sumIncomeByPeriod(Collection $periods, array $types): Collection
    {
        [$rangeStart, $rangeEnd] = [$periods->first()[0], $periods->last()[1]];

        $rows = Income::query()
            ->whereIn('type', $types)
            ->where('payment_status', IncomePaymentStatus::Paid)
            ->whereBetween('created_at', [$rangeStart, $rangeEnd])
            ->get(['amount', 'created_at']);

        return $periods->map(fn ($range) => (float) $rows
            ->filter(fn ($row) => $row->created_at->between($range[0], $range[1]))
            ->sum('amount'));
    }

    /**
     * @param  Collection<string, array{0: Carbon, 1: Carbon}>  $periods
     * @return Collection<string, float>
     */
    protected function sumExpenseByPeriod(Collection $periods): Collection
    {
        [$rangeStart, $rangeEnd] = [$periods->first()[0], $periods->last()[1]];

        $rows = Expense::query()
            ->whereBetween('date', [$rangeStart, $rangeEnd])
            ->get(['amount', 'date']);

        return $periods->map(fn ($range) => (float) $rows
            ->filter(fn ($row) => $row->date->between($range[0], $range[1]))
            ->sum('amount'));
    }
}
