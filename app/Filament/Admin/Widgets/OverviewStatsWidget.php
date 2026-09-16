<?php

namespace App\Filament\Admin\Widgets;

use App\Enums\CardStatus;
use App\Enums\IncomePaymentStatus;
use App\Enums\IncomeType;
use App\Filament\Admin\Widgets\Concerns\FormatsMoney;
use App\Models\Card;
use App\Models\CardStatusHistory;
use App\Models\Income;
use App\Models\User;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class OverviewStatsWidget extends StatsOverviewWidget
{
    use FormatsMoney;

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            $this->usersStat(),
            $this->activeCardsStat(),
            $this->issuedCardsStat(),
            $this->cardBalancesStat(),
            $this->turnoverStat(),
        ];
    }

    protected function usersStat(): Stat
    {
        $total = User::query()->count();

        return Stat::make('Пользователи', number_format($total, 0, ',', ' '))
            ->description(
                '+' . User::where('created_at', '>=', now()->subDay())->count() . ' за сутки · '
                . '+' . User::where('created_at', '>=', now()->subWeek())->count() . ' за неделю · '
                . '+' . User::where('created_at', '>=', now()->subMonth())->count() . ' за месяц'
            )
            ->descriptionIcon(Heroicon::OutlinedUsers)
            ->icon(Heroicon::OutlinedUsers)
            ->color('primary');
    }

    protected function activeCardsStat(): Stat
    {
        $total = Card::where('status', CardStatus::Active)->count();

        return Stat::make('Активные карты', number_format($total, 0, ',', ' '))
            ->description(
                $this->formatChange($this->activeCardsChange(now()->subDay())) . ' за сутки · '
                . $this->formatChange($this->activeCardsChange(now()->subWeek())) . ' за неделю · '
                . $this->formatChange($this->activeCardsChange(now()->subMonth())) . ' за месяц'
            )
            ->descriptionIcon(Heroicon::OutlinedCreditCard)
            ->icon(Heroicon::OutlinedCreditCard)
            ->color('success');
    }

    /**
     * Чистое изменение количества активных карт за период: сколько карт стало активными
     * минус сколько перестало быть активными, по данным истории изменения статуса карты.
     */
    protected function activeCardsChange(Carbon $since): int
    {
        $becameActive = CardStatusHistory::where('new_status', CardStatus::Active)
            ->where('created_at', '>=', $since)
            ->count();

        $leftActive = CardStatusHistory::where('old_status', CardStatus::Active)
            ->where('new_status', '!=', CardStatus::Active)
            ->where('created_at', '>=', $since)
            ->count();

        return $becameActive - $leftActive;
    }

    protected function formatChange(int $value): string
    {
        return ($value >= 0 ? '+' : '') . number_format($value, 0, ',', ' ');
    }

    protected function issuedCardsStat(): Stat
    {
        $today = Card::whereDate('issued_at', now()->toDateString())->count();
        $monthToDate = Card::where('issued_at', '>=', now()->startOfMonth())->count();

        return Stat::make('Выпущено карт', number_format($today, 0, ',', ' ') . ' сегодня')
            ->description('С начала месяца: ' . number_format($monthToDate, 0, ',', ' '))
            ->descriptionIcon(Heroicon::OutlinedCalendarDays)
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

        return Stat::make('Баланс на картах клиентов', $this->formatMoneyByCurrency($balancesByCurrency))
            ->description('Сколько денег сейчас лежит на активных и замороженных картах')
            ->descriptionIcon(Heroicon::OutlinedWallet)
            ->icon(Heroicon::OutlinedWallet)
            ->color('warning');
    }

    protected function turnoverStat(): Stat
    {
        $today = $this->turnoverByCurrency(now()->startOfDay());
        $monthToDate = $this->turnoverByCurrency(now()->startOfMonth());

        return Stat::make('Оборот за сегодня', $this->formatMoneyByCurrency($today))
            ->description('С начала месяца: ' . $this->formatMoneyByCurrency($monthToDate))
            ->descriptionIcon(Heroicon::OutlinedBanknotes)
            ->icon(Heroicon::OutlinedBanknotes)
            ->color('primary');
    }

    /**
     * Сумма всех операций (выпуск карт и пополнения) с оплаченным статусом, по валютам.
     *
     * @return array<string, float>
     */
    protected function turnoverByCurrency(Carbon $since): array
    {
        return Income::query()
            ->whereIn('type', [IncomeType::CardIssue, IncomeType::CardTopup])
            ->where('payment_status', IncomePaymentStatus::Paid)
            ->where('created_at', '>=', $since)
            ->selectRaw('currency, sum(amount) as total')
            ->groupBy('currency')
            ->pluck('total', 'currency')
            ->map(fn ($value) => (float) $value)
            ->toArray();
    }
}
