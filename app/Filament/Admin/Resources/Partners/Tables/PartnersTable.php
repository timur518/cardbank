<?php

namespace App\Filament\Admin\Resources\Partners\Tables;

use App\Enums\IncomePaymentStatus;
use App\Enums\PayoutRequestStatus;
use App\Models\Partner;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Партнёры не заводятся вручную — здесь перечислены все пользователи, у которых есть
 * хотя бы один приглашённый (см. App\Models\Partner и глобальный scope hasReferrals).
 * Статистика считается «на лету» через withCount/withSum в getEloquentQuery(), без
 * хранения отдельных счётчиков.
 */
class PartnersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query
                ->withCount('referredUsers')
                ->withCount(['referredUsers as active_referred_users_count' => fn ($q) => $q
                    ->whereHas('incomes', fn ($q2) => $q2->where('payment_status', IncomePaymentStatus::Paid))])
                ->withSum('partnerTransactions as earned_usd_sum', 'commission_amount')
                ->withSum(['payoutRequests as pending_payout_usd_sum' => fn ($q) => $q
                    ->whereIn('status', [PayoutRequestStatus::Pending, PayoutRequestStatus::Approved])], 'amount_usd')
                ->withSum(['payoutRequests as paid_payout_usd_sum' => fn ($q) => $q
                    ->where('status', PayoutRequestStatus::Paid)], 'amount_usd'))
            ->defaultSort('earned_usd_sum', 'desc')
            ->emptyStateHeading('Партнёров пока нет')
            ->emptyStateDescription('Здесь автоматически появится любой пользователь, у которого есть хотя бы один приглашённый по его коду (?pid=) пользователь.')
            ->emptyStateIcon('heroicon-o-user-group')
            ->columns([
                TextColumn::make('name')
                    ->label('Партнёр')
                    ->description(fn (Partner $record) => $record->email)
                    ->searchable(['name', 'email']),
                TextColumn::make('referred_users_count')
                    ->label('Приглашено / Активных')
                    ->state(fn (Partner $record) => "{$record->referred_users_count} / {$record->active_referred_users_count}")
                    ->alignCenter()
                    ->sortable(query: function (Builder $query, string $direction) {
                        $query->orderBy('referred_users_count', $direction)
                            ->orderBy('active_referred_users_count', $direction);
                    }),
                TextColumn::make('earned_usd_sum')
                    ->label('Сумма вознаграждений')
                    ->state(fn (Partner $record) => (float) $record->earned_usd_sum)
                    ->money('USD')
                    ->sortable(),
                TextColumn::make('available_usd')
                    ->label('Доступно к выводу')
                    ->state(fn (Partner $record) => (float) $record->earned_usd_sum
                        - (float) $record->pending_payout_usd_sum - (float) $record->paid_payout_usd_sum)
                    ->money('USD'),
                TextColumn::make('pending_payout_usd_sum')
                    ->label('Ожидает к выплате')
                    ->state(fn (Partner $record) => (float) $record->pending_payout_usd_sum)
                    ->money('USD')
                    ->sortable(),
                TextColumn::make('paid_payout_usd_sum')
                    ->label('Выплачено всего')
                    ->state(fn (Partner $record) => (float) $record->paid_payout_usd_sum)
                    ->money('USD')
                    ->sortable(),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
