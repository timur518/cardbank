<?php

namespace App\Filament\Admin\Widgets;

use App\Enums\CardStatus;
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
        return 3;
    }

    protected function getStats(): array
    {
        return [
            $this->usersStat(),
            $this->activeCardsStat(),
            // $this->issuedCardsStat(),
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

    /**
     * Оборот по картам — сумма вообще всех операций по картам (покупки, пополнения,
     * комиссии, возвраты), включая ещё не подтверждённые холды (`authorization`/
     * `verification`, статус «В обработке») — деньги под ними уже фактически заняты
     * на карте. Исключены только отклонённые операции (`Decline`) — по ним деньги
     * никуда не двигались.
     */
    protected function paymentsStat(): Stat
    {
        $totalByCurrency = CardTransaction::query()
            ->where('type', '!=', CardTransactionType::Decline)
            ->selectRaw('currency, sum(amount) as total')
            ->groupBy('currency')
            ->pluck('total', 'currency')
            ->map(fn ($value) => (float) $value)
            ->toArray();

        return Stat::make('Оборот по картам', $this->formatMoneyByCurrency($totalByCurrency))
            ->description('Сумма всех операций по картам')
            ->icon(Heroicon::OutlinedBanknotes)
            ->color('primary');
    }
}
