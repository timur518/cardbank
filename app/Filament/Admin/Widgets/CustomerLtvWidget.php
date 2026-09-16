<?php

namespace App\Filament\Admin\Widgets;

use App\Enums\IncomePaymentStatus;
use App\Models\Income;
use App\Models\User;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * «Сколько платит клиент со временем» — средний доход с одного клиента в первый,
 * второй и третий месяц после регистрации. Суммы по всем валютам складываются по
 * номиналу без учёта курса, как и другие финансовые виджеты главной страницы.
 */
class CustomerLtvWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 7;

    protected function getStats(): array
    {
        return [
            $this->cohortMonthStat(1, 'Средний доход в 1-й месяц'),
            $this->cohortMonthStat(2, 'Средний доход во 2-й месяц'),
            $this->cohortMonthStat(3, 'Средний доход в 3-й месяц'),
        ];
    }

    protected function cohortMonthStat(int $month, string $label): Stat
    {
        $average = $this->averageRevenueForCohortMonth($month);

        return Stat::make($label, $average === null ? '—' : number_format($average, 2, ',', ' '))
            ->description($average === null ? 'Недостаточно клиентов такого возраста' : 'На одного клиента')
            ->descriptionIcon(Heroicon::OutlinedUserCircle)
            ->icon(Heroicon::OutlinedArrowTrendingUp)
            ->color('primary');
    }

    protected function averageRevenueForCohortMonth(int $month): ?float
    {
        $eligibleUsers = User::query()
            ->where('created_at', '<=', now()->subMonths($month))
            ->get(['id', 'created_at']);

        if ($eligibleUsers->isEmpty()) {
            return null;
        }

        $incomesByUser = Income::query()
            ->whereIn('user_id', $eligibleUsers->pluck('id'))
            ->where('payment_status', IncomePaymentStatus::Paid)
            ->get(['user_id', 'amount', 'created_at'])
            ->groupBy('user_id');

        $total = $eligibleUsers->sum(function (User $user) use ($incomesByUser, $month) {
            $windowStart = $user->created_at->clone()->addMonths($month - 1);
            $windowEnd = $user->created_at->clone()->addMonths($month);

            return ($incomesByUser->get($user->id) ?? collect())
                ->filter(fn ($income) => $income->created_at->between($windowStart, $windowEnd))
                ->sum('amount');
        });

        return $total / $eligibleUsers->count();
    }
}
