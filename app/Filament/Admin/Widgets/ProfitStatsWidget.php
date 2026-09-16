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
     * всё время, отдельно по каждой валюте.
     */
    protected function grossProfitStat(): Stat
    {
        $incomeByCurrency = Income::query()
            ->whereIn('type', [IncomeType::CardIssue, IncomeType::CardTopup])
            ->where('payment_status', IncomePaymentStatus::Paid)
            ->selectRaw('currency, sum(amount) as total')
            ->groupBy('currency')
            ->pluck('total', 'currency');

        $expenseByCurrency = Expense::query()
            ->whereIn('category', [ExpenseCategory::CardIssue, ExpenseCategory::CardTopup])
            ->selectRaw('currency, sum(amount) as total')
            ->groupBy('currency')
            ->pluck('total', 'currency');

        $profitByCurrency = $incomeByCurrency->keys()->merge($expenseByCurrency->keys())->unique()
            ->mapWithKeys(fn ($currency) => [
                $currency => (float) ($incomeByCurrency[$currency] ?? 0) - (float) ($expenseByCurrency[$currency] ?? 0),
            ])
            ->toArray();

        $isNegative = collect($profitByCurrency)->contains(fn ($value) => $value < 0);

        return Stat::make('Валовая прибыль', $this->formatMoneyByCurrency($profitByCurrency))
            ->description('Всего')
            ->icon(Heroicon::OutlinedCurrencyDollar)
            ->color($isNegative ? 'danger' : 'success');
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
