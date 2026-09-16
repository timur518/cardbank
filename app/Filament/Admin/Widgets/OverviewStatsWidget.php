<?php

namespace App\Filament\Admin\Widgets;

use App\Enums\CardStatus;
use App\Enums\IncomePaymentStatus;
use App\Enums\IncomeType;
use App\Filament\Admin\Widgets\Concerns\FormatsMoney;
use App\Models\Card;
use App\Models\Income;
use App\Models\User;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OverviewStatsWidget extends StatsOverviewWidget
{
    use FormatsMoney;

    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected function getColumns(): int|array|null
    {
        return 5;
    }

    protected function getStats(): array
    {
        return [
            $this->usersStat(),
            $this->activeCardsStat(),
            $this->issuedCardsStat(),
            $this->cardBalancesStat(),
            $this->paymentsStat(),
        ];
    }

    protected function usersStat(): Stat
    {
        $total = User::query()->count();

        return Stat::make('Пользователи', number_format($total, 0, ',', ' '))
            ->description('Всего')
            ->icon(Heroicon::OutlinedUsers)
            ->color('primary');
    }

    protected function activeCardsStat(): Stat
    {
        $total = Card::where('status', CardStatus::Active)->count();

        return Stat::make('Активные карты', number_format($total, 0, ',', ' '))
            ->description('Всего')
            ->icon(Heroicon::OutlinedCreditCard)
            ->color('success');
    }

    protected function issuedCardsStat(): Stat
    {
        $total = Card::query()->count();

        return Stat::make('Выпущено карт', number_format($total, 0, ',', ' '))
            ->description('Всего')
            ->icon(Heroicon::OutlinedRectangleStack)
            ->color('info');
    }

    protected function cardBalancesStat(): Stat
    {
        $balancesByCurrency = Card::query()
            ->whereIn('status', [CardStatus::Active, CardStatus::Frozen])
            ->selectRaw('currency, sum(balance) as total')
            ->groupBy('currency')
            ->pluck('total', 'currency')
            ->toArray();

        return Stat::make('Баланс на картах', $this->formatMoneyByCurrency($balancesByCurrency))
            ->description('Всего')
            ->icon(Heroicon::OutlinedWallet)
            ->color('warning');
    }

    protected function paymentsStat(): Stat
    {
        $totalByCurrency = Income::query()
            ->whereIn('type', [IncomeType::CardIssue, IncomeType::CardTopup])
            ->where('payment_status', IncomePaymentStatus::Paid)
            ->selectRaw('currency, sum(amount) as total')
            ->groupBy('currency')
            ->pluck('total', 'currency')
            ->map(fn ($value) => (float) $value)
            ->toArray();

        return Stat::make('Сумма платежей по картам', $this->formatMoneyByCurrency($totalByCurrency))
            ->description('Всего всех платежей по картам')
            ->icon(Heroicon::OutlinedBanknotes)
            ->color('primary');
    }
}
