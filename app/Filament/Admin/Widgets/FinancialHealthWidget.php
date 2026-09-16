<?php

namespace App\Filament\Admin\Widgets;

use App\Enums\CardStatus;
use App\Filament\Admin\Widgets\Concerns\FormatsMoney;
use App\Models\Card;
use App\Models\CardProvider;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * «Финансовое здоровье компании» — сравнение того, сколько денег компания должна
 * своим клиентам (остатки на картах), с тем, сколько денег у компании реально есть
 * (остатки у всех провайдеров карт). Суммы по всем валютам складываются по номиналу
 * без учёта курса — это приблизительная оценка до появления модуля курсов валют.
 */
class FinancialHealthWidget extends StatsOverviewWidget
{
    use FormatsMoney;

    protected static ?int $sort = 9;

    protected function getStats(): array
    {
        $owed = (float) Card::whereIn('status', [CardStatus::Active, CardStatus::Frozen])->sum('balance');
        $available = (float) CardProvider::sum('reserve_balance_usd');
        $diff = $available - $owed;

        return [
            Stat::make('Компания должна клиентам', number_format($owed, 2, ',', ' '))
                ->description('Сумма остатков на всех активных и замороженных картах')
                ->descriptionIcon(Heroicon::OutlinedScale)
                ->icon(Heroicon::OutlinedScale)
                ->color('warning'),

            Stat::make('У компании реально есть', $this->formatMoney($available, 'USD'))
                ->description('Остаток резерва у всех провайдеров карт')
                ->descriptionIcon(Heroicon::OutlinedBuildingLibrary)
                ->icon(Heroicon::OutlinedBuildingLibrary)
                ->color('info'),

            Stat::make('Финансовое здоровье', number_format($diff, 2, ',', ' '))
                ->description($diff < 0 ? 'Реальных денег меньше, чем компания должна клиентам' : 'Реальных денег хватает на все обязательства')
                ->descriptionIcon($diff < 0 ? Heroicon::OutlinedExclamationTriangle : Heroicon::OutlinedCheckCircle)
                ->icon($diff < 0 ? Heroicon::OutlinedExclamationTriangle : Heroicon::OutlinedCheckCircle)
                ->color($diff < 0 ? 'danger' : 'success'),
        ];
    }
}
