<?php

namespace App\Filament\Admin\Widgets;

use App\Enums\CardStatus;
use App\Enums\CardTransactionStatus;
use App\Enums\CardTransactionType;
use App\Filament\Admin\Widgets\Concerns\FormatsMoney;
use App\Models\Card;
use App\Models\CardTransaction;
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
        return 4;
    }

    protected function getStats(): array
    {
        return [
            $this->usersStat(),
            $this->activeCardsStat(),
            // $this->issuedCardsStat(),
            $this->cardBalancesStat(),
            $this->paymentsStat(),
        ];
    }

    protected function usersStat(): Stat
    {
        $total = User::query()->count();

        return Stat::make('Пользователи', number_format($total, 0, ',', ' '))
            ->description('Зарегистрировано')
            ->icon(Heroicon::OutlinedUsers)
            ->color('primary');
    }

    protected function activeCardsStat(): Stat
    {
        $total = Card::where('status', CardStatus::Active)->count();

        return Stat::make('Активные карты', number_format($total, 0, ',', ' '))
            ->description('Сейчас активны')
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
            ->description('На балансах клиентов')
            ->icon(Heroicon::OutlinedWallet)
            ->color('warning');
    }

    /**
     * Оборот по картам — сумма всех успешных операций по картам (покупки и пополнения),
     * а не сумма зачисленных нам платежей за выпуск/пополнение. Комиссии и
     * отклонённые операции не учитываются.
     */
    protected function paymentsStat(): Stat
    {
        $totalByCurrency = CardTransaction::query()
            ->whereIn('type', [CardTransactionType::Purchase, CardTransactionType::Topup])
            ->where('status', CardTransactionStatus::Success)
            ->selectRaw('currency, sum(amount) as total')
            ->groupBy('currency')
            ->pluck('total', 'currency')
            ->map(fn ($value) => (float) $value)
            ->toArray();

        return Stat::make('Оборот по картам', $this->formatMoneyByCurrency($totalByCurrency))
            ->description('Покупки и пополнения')
            ->icon(Heroicon::OutlinedBanknotes)
            ->color('primary');
    }
}
