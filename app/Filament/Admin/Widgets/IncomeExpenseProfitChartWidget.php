<?php

namespace App\Filament\Admin\Widgets;

use App\Enums\ExpenseCategory;
use App\Enums\IncomePaymentStatus;
use App\Enums\IncomeType;
use App\Models\Expense;
use App\Models\Income;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * «Доходы, расходы и прибыль» — сколько заработали (отдельно по выпуску карт и по
 * пополнениям), сколько заплатили провайдерам карт, сколько ушло на общие расходы
 * бизнеса, и итоговая прибыль. Всё считается только в долларах (поле «amount_usd»
 * у поступлений и расходов), так как клиенты платят в рублях через СБП, а провайдеру
 * карт мы платим в долларах. Записи без заполненного amount_usd в график не попадают.
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
        return 'Доходы, расходы и прибыль';
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

        $issueIncome = $this->sumIncomeByPeriod($periods, IncomeType::CardIssue);
        $topupIncome = $this->sumIncomeByPeriod($periods, IncomeType::CardTopup);
        $providerCosts = $this->sumExpenseByPeriod($periods, [ExpenseCategory::CardIssue, ExpenseCategory::CardTopup]);
        $generalExpenses = $this->sumExpenseByPeriod($periods, null, [ExpenseCategory::CardIssue, ExpenseCategory::CardTopup]);

        $profit = $periods->map(fn ($period, $key) => $issueIncome[$key] + $topupIncome[$key] - $providerCosts[$key] - $generalExpenses[$key]);

        return [
            'datasets' => [
                [
                    'label' => 'Доход от выпуска карт',
                    'data' => $issueIncome->values()->all(),
                    'borderColor' => '#22c55e',
                    'backgroundColor' => '#22c55e',
                ],
                [
                    'label' => 'Доход от пополнений',
                    'data' => $topupIncome->values()->all(),
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => '#3b82f6',
                ],
                [
                    'label' => 'Выплаты провайдерам',
                    'data' => $providerCosts->values()->all(),
                    'borderColor' => '#f97316',
                    'backgroundColor' => '#f97316',
                ],
                [
                    'label' => 'Общие расходы бизнеса',
                    'data' => $generalExpenses->values()->all(),
                    'borderColor' => '#ef4444',
                    'backgroundColor' => '#ef4444',
                ],
                [
                    'label' => 'Прибыль',
                    'data' => $profit->values()->all(),
                    'borderColor' => '#a855f7',
                    'backgroundColor' => '#a855f7',
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
     * @return Collection<string, float>
     */
    protected function sumIncomeByPeriod(Collection $periods, IncomeType $type): Collection
    {
        [$rangeStart, $rangeEnd] = [$periods->first()[0], $periods->last()[1]];

        $rows = Income::query()
            ->where('type', $type)
            ->where('payment_status', IncomePaymentStatus::Paid)
            ->whereBetween('created_at', [$rangeStart, $rangeEnd])
            ->get(['amount_usd', 'created_at']);

        return $periods->map(fn ($range) => (float) $rows
            ->filter(fn ($row) => $row->created_at->between($range[0], $range[1]))
            ->sum('amount_usd'));
    }

    /**
     * @param  Collection<string, array{0: Carbon, 1: Carbon}>  $periods
     * @param  array<ExpenseCategory>|null  $only
     * @param  array<ExpenseCategory>|null  $except
     * @return Collection<string, float>
     */
    protected function sumExpenseByPeriod(Collection $periods, ?array $only, ?array $except = null): Collection
    {
        [$rangeStart, $rangeEnd] = [$periods->first()[0], $periods->last()[1]];

        $query = Expense::query()->whereBetween('date', [$rangeStart, $rangeEnd]);

        if ($only !== null) {
            $query->whereIn('category', $only);
        }

        if ($except !== null) {
            $query->whereNotIn('category', $except);
        }

        $rows = $query->get(['amount_usd', 'date']);

        return $periods->map(fn ($range) => (float) $rows
            ->filter(fn ($row) => $row->date->between($range[0], $range[1]))
            ->sum('amount_usd'));
    }
}
