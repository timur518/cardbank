<?php

namespace App\Filament\Admin\Widgets;

use App\Enums\CardTransactionType;
use App\Models\CardTransaction;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Collection;

/**
 * «Все операции по картам» — общая картина активности по всей карточной базе:
 * сколько всего было операций каждого типа по дням (покупки, пополнения, комиссии,
 * возвраты, отклонённые платежи). Подробная история по каждой операции — в разделе
 * «Финансы» → «Транзакции по картам».
 */
class CardOperationsChartWidget extends ChartWidget
{
    protected static ?int $sort = 5;

    protected ?string $maxHeight = '250px';

    protected function getType(): string
    {
        return 'bar';
    }

    public function getHeading(): string
    {
        return 'Все операции по картам';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'x' => ['stacked' => true],
                'y' => ['stacked' => true],
            ],
        ];
    }

    protected function getData(): array
    {
        $days = collect(range(13, 0))->map(fn (int $daysAgo) => now()->subDays($daysAgo)->startOfDay());

        $rows = CardTransaction::query()
            ->where('occurred_at', '>=', $days->first())
            ->get(['type', 'occurred_at']);

        $colors = [
            CardTransactionType::Purchase->value => '#3b82f6',
            CardTransactionType::Topup->value => '#22c55e',
            CardTransactionType::Fee->value => '#eab308',
            CardTransactionType::Refund->value => '#a855f7',
            CardTransactionType::Decline->value => '#ef4444',
        ];

        $datasets = collect(CardTransactionType::cases())->map(function (CardTransactionType $type) use ($days, $rows, $colors) {
            $counts = $days->map(fn ($day) => $rows
                ->where('type', $type)
                ->filter(fn (CardTransaction $row) => $row->occurred_at->isSameDay($day))
                ->count());

            return [
                'label' => $type->getLabel(),
                'data' => $counts->values()->all(),
                'backgroundColor' => $colors[$type->value],
            ];
        });

        return [
            'datasets' => $datasets->all(),
            'labels' => $days->map(fn ($day) => $day->format('d.m'))->all(),
        ];
    }
}
