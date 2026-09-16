<?php

namespace App\Filament\Admin\Widgets;

use App\Enums\ExpenseCategory;
use App\Enums\IncomePaymentStatus;
use App\Enums\IncomeType;
use App\Enums\PayoutRequestStatus;
use App\Filament\Admin\Widgets\Concerns\FormatsMoney;
use App\Models\CardProvider;
use App\Models\Expense;
use App\Models\Income;
use App\Models\PayoutRequest;
use App\Models\Setting;
use App\Models\User;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class ProfitStatsWidget extends StatsOverviewWidget
{
    use FormatsMoney;

    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        return [
            $this->grossProfitStat('RUB'),
            $this->grossProfitStat('USD'),
            $this->expensesStat(),
            $this->marginStat(),
            $this->referralStat(),
            $this->providersReserveStat(),
        ];
    }

    /**
     * Доход минус связанные расходы провайдерам по операциям выпуска и пополнения,
     * за сегодня, с начала месяца и с начала года — отдельно по каждой валюте.
     */
    protected function grossProfitStat(string $currency): Stat
    {
        $today = $this->profit($currency, now()->startOfDay());
        $monthToDate = $this->profit($currency, now()->startOfMonth());
        $yearToDate = $this->profit($currency, now()->startOfYear());

        return Stat::make("Валовая прибыль, {$currency}", $this->formatMoney($today, $currency) . ' сегодня')
            ->description(
                'С начала месяца: ' . $this->formatMoney($monthToDate, $currency)
                . ' · С начала года: ' . $this->formatMoney($yearToDate, $currency)
            )
            ->descriptionIcon(Heroicon::OutlinedArrowTrendingUp)
            ->icon(Heroicon::OutlinedCurrencyDollar)
            ->color($monthToDate >= 0 ? 'success' : 'danger');
    }

    protected function profit(string $currency, Carbon $since): float
    {
        $income = (float) Income::query()
            ->whereIn('type', [IncomeType::CardIssue, IncomeType::CardTopup])
            ->where('payment_status', IncomePaymentStatus::Paid)
            ->where('currency', $currency)
            ->where('created_at', '>=', $since)
            ->sum('amount');

        $expense = (float) Expense::query()
            ->whereIn('category', [ExpenseCategory::CardIssue, ExpenseCategory::CardTopup])
            ->where('currency', $currency)
            ->where('date', '>=', $since)
            ->sum('amount');

        return $income - $expense;
    }

    protected function expensesStat(): Stat
    {
        $byCurrency = Expense::query()
            ->where('date', '>=', now()->startOfMonth())
            ->selectRaw('currency, sum(amount) as total')
            ->groupBy('currency')
            ->pluck('total', 'currency')
            ->map(fn ($value) => (float) $value)
            ->toArray();

        return Stat::make('Расходы с начала месяца', $this->formatMoneyByCurrency($byCurrency))
            ->description('Зарплаты, реклама, аренда и всё остальное из раздела «Расходы»')
            ->descriptionIcon(Heroicon::OutlinedReceiptPercent)
            ->icon(Heroicon::OutlinedReceiptPercent)
            ->color('danger');
    }

    /**
     * Процент прибыли на операцию, отдельно по выпуску карт и отдельно по пополнениям.
     * Доход и расход сравниваются по номиналу без учёта курса — как и оценка прибыли
     * с одного выпуска в карточных продуктах (см. CardProduct::getEstimatedProfitAttribute).
     */
    protected function marginStat(): Stat
    {
        $issueMargin = $this->marginPercent(IncomeType::CardIssue, ExpenseCategory::CardIssue);
        $topupMargin = $this->marginPercent(IncomeType::CardTopup, ExpenseCategory::CardTopup);

        return Stat::make('Процент прибыли на операцию', $this->formatPercent($issueMargin) . ' на выпуске')
            ->description('На пополнениях: ' . $this->formatPercent($topupMargin))
            ->descriptionIcon(Heroicon::OutlinedChartPie)
            ->icon(Heroicon::OutlinedChartPie)
            ->color('info');
    }

    protected function marginPercent(IncomeType $incomeType, ExpenseCategory $expenseCategory): ?float
    {
        $income = (float) Income::where('type', $incomeType)
            ->where('payment_status', IncomePaymentStatus::Paid)
            ->sum('amount');

        if ($income <= 0) {
            return null;
        }

        $expense = (float) Expense::where('category', $expenseCategory)->sum('amount');

        return (($income - $expense) / $income) * 100;
    }

    protected function formatPercent(?float $value): string
    {
        return $value === null ? '—' : number_format($value, 1, ',', ' ') . '%';
    }

    /**
     * Сколько начислено партнёрам за реферальные операции с начала месяца (оценка по
     * ставкам из настроек реферальной системы) и сколько уже реально выплачено.
     */
    protected function referralStat(): Stat
    {
        $rates = Setting::getMany(['referral_issue_rate', 'referral_topup_rate']);
        $issueRate = ((float) ($rates['referral_issue_rate'] ?? 0)) / 100;
        $topupRate = ((float) ($rates['referral_topup_rate'] ?? 0)) / 100;

        $referredUserIds = User::whereNotNull('referral_code')->pluck('id');
        $monthStart = now()->startOfMonth();

        $issueIncome = (float) Income::where('type', IncomeType::CardIssue)
            ->where('payment_status', IncomePaymentStatus::Paid)
            ->whereIn('user_id', $referredUserIds)
            ->where('created_at', '>=', $monthStart)
            ->sum('amount');

        $topupIncome = (float) Income::where('type', IncomeType::CardTopup)
            ->where('payment_status', IncomePaymentStatus::Paid)
            ->whereIn('user_id', $referredUserIds)
            ->where('created_at', '>=', $monthStart)
            ->sum('amount');

        $accrued = $issueIncome * $issueRate + $topupIncome * $topupRate;

        $paid = (float) PayoutRequest::where('status', PayoutRequestStatus::Paid)
            ->where('resolved_at', '>=', $monthStart)
            ->sum('amount_usd');

        return Stat::make('Реферальная программа за месяц', 'Начислено (оценка): ' . $this->formatMoney($accrued, 'USD'))
            ->description('Выплачено: ' . $this->formatMoney($paid, 'USD'))
            ->descriptionIcon(Heroicon::OutlinedGift)
            ->icon(Heroicon::OutlinedGift)
            ->color('warning');
    }

    protected function providersReserveStat(): Stat
    {
        $reserve = (float) CardProvider::sum('reserve_balance_usd');

        $averageDailyExpense = (float) Expense::query()
            ->whereIn('category', [ExpenseCategory::CardIssue, ExpenseCategory::CardTopup])
            ->where('currency', 'USD')
            ->where('date', '>=', now()->subDays(30))
            ->sum('amount') / 30;

        $runwayDays = $averageDailyExpense > 0 ? (int) floor($reserve / $averageDailyExpense) : null;

        return Stat::make('Остаток резерва у провайдеров', $this->formatMoney($reserve, 'USD'))
            ->description($runwayDays === null ? 'Недостаточно данных о расходах для прогноза' : "Хватит примерно на {$runwayDays} дн. при текущих расходах")
            ->descriptionIcon(Heroicon::OutlinedClock)
            ->icon(Heroicon::OutlinedBuildingLibrary)
            ->color($runwayDays !== null && $runwayDays < 14 ? 'danger' : 'success');
    }
}
