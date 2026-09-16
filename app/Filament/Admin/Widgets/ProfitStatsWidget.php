<?php

namespace App\Filament\Admin\Widgets;

use App\Enums\ExpenseCategory;
use App\Enums\IncomePaymentStatus;
use App\Enums\IncomeType;
use App\Filament\Admin\Widgets\Concerns\FormatsMoney;
use App\Models\CardProvider;
use App\Models\Expense;
use App\Models\Income;
use App\Models\Setting;
use App\Models\User;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ProfitStatsWidget extends StatsOverviewWidget
{
    use FormatsMoney;

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected function getColumns(): int|array|null
    {
        return 5;
    }

    protected function getStats(): array
    {
        return [
            $this->grossProfitStat(),
            $this->expensesStat(),
            $this->referralStat(),
            $this->masterAccountsStat(),
        ];
    }

    /**
     * Доход минус связанные расходы провайдерам по операциям выпуска и пополнения, за
     * всё время, одним числом. Суммы по всем валютам складываются по номиналу
     * без учёта курса — как и в других виджетах главной страницы.
     */
    protected function grossProfitStat(): Stat
    {
        $income = (float) Income::query()
            ->whereIn('type', [IncomeType::CardIssue, IncomeType::CardTopup])
            ->where('payment_status', IncomePaymentStatus::Paid)
            ->sum('amount');

        $expense = (float) Expense::query()
            ->whereIn('category', [ExpenseCategory::CardIssue, ExpenseCategory::CardTopup])
            ->sum('amount');

        $profit = $income - $expense;

        return Stat::make('Валовая прибыль', number_format($profit, 2, ',', ' '))
            ->description('Всего')
            ->icon(Heroicon::OutlinedCurrencyDollar)
            ->color($profit >= 0 ? 'success' : 'danger');
    }

    protected function expensesStat(): Stat
    {
        $byCurrency = Expense::query()
            ->selectRaw('currency, sum(amount) as total')
            ->groupBy('currency')
            ->pluck('total', 'currency')
            ->map(fn ($value) => (float) $value)
            ->toArray();

        return Stat::make('Расходы', $this->formatMoneyByCurrency($byCurrency))
            ->description('Всего')
            ->icon(Heroicon::OutlinedReceiptPercent)
            ->color('danger');
    }

    /**
     * Сколько всего начислено партнёрам за реферальные операции за всё время (оценка
     * по действующим ставкам из настроек реферальной системы).
     */
    protected function referralStat(): Stat
    {
        $rates = Setting::getMany(['referral_issue_rate', 'referral_topup_rate']);
        $issueRate = ((float) ($rates['referral_issue_rate'] ?? 0)) / 100;
        $topupRate = ((float) ($rates['referral_topup_rate'] ?? 0)) / 100;

        $referredUserIds = User::whereNotNull('referral_code')->pluck('id');

        $issueIncome = (float) Income::where('type', IncomeType::CardIssue)
            ->where('payment_status', IncomePaymentStatus::Paid)
            ->whereIn('user_id', $referredUserIds)
            ->sum('amount');

        $topupIncome = (float) Income::where('type', IncomeType::CardTopup)
            ->where('payment_status', IncomePaymentStatus::Paid)
            ->whereIn('user_id', $referredUserIds)
            ->sum('amount');

        $accrued = $issueIncome * $issueRate + $topupIncome * $topupRate;

        return Stat::make('Реферальные начисления', $this->formatMoney($accrued, 'USD'))
            ->description('Всего')
            ->icon(Heroicon::OutlinedGift)
            ->color('warning');
    }

    protected function masterAccountsStat(): Stat
    {
        $reserve = (float) CardProvider::sum('reserve_balance_usd');

        return Stat::make('Баланс на мастер-счетах', $this->formatMoney($reserve, 'USD'))
            ->description('Всего')
            ->icon(Heroicon::OutlinedBuildingLibrary)
            ->color('success');
    }
}
