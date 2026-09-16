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
     * всё время, одним числом. Считается только в долларах (поле «amount_usd»): клиенты
     * платят в рублях через СБП, но платёжная система конвертирует их в доллары, а провайдеру
     * карт мы платим тоже в долларах — так наценка считается в единой валюте без искажений от
     * курса. Записи без заполненного amount_usd в расчёт не попадают.
     */
    protected function grossProfitStat(): Stat
    {
        $income = (float) Income::query()
            ->whereIn('type', [IncomeType::CardIssue, IncomeType::CardTopup])
            ->where('payment_status', IncomePaymentStatus::Paid)
            ->sum('amount_usd');

        $expense = (float) Expense::query()
            ->whereIn('category', [ExpenseCategory::CardIssue, ExpenseCategory::CardTopup])
            ->sum('amount_usd');

        $profit = $income - $expense;

        return Stat::make('Валовая прибыль', $this->formatMoney($profit, 'USD'))
            ->description('Всего, выпуск + пополнения')
            ->icon(Heroicon::OutlinedCurrencyDollar)
            ->color($profit >= 0 ? 'success' : 'danger');
    }

    /**
     * Все расходы компании за всё время в долларовом эквиваленте — и выплаты провайдерам
     * (уже в $), и общие расходы бизнеса в рублях (зарплаты, реклама и т.д.) по их
     * долларовому эквиваленту из поля amount_usd.
     */
    protected function expensesStat(): Stat
    {
        $total = (float) Expense::sum('amount_usd');

        return Stat::make('Расходы', $this->formatMoney($total, 'USD'))
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
            ->sum('amount_usd');

        $topupIncome = (float) Income::where('type', IncomeType::CardTopup)
            ->where('payment_status', IncomePaymentStatus::Paid)
            ->whereIn('user_id', $referredUserIds)
            ->sum('amount_usd');

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
