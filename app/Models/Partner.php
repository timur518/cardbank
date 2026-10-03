<?php

namespace App\Models;

use App\Enums\IncomePaymentStatus;
use App\Enums\PayoutRequestStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * «Партнёр» — это не отдельная сущность, а обычный User, у которого есть хотя бы один
 * приглашённый пользователь (кто-то зарегистрировался с его invite_code в поле
 * users.referral_code). Отдельной таблицы `partners` больше нет (раньше партнёров
 * заводили вручную в админке — теперь это считается автоматически из данных пользователей,
 * начислений {@see PartnerTransaction} и заявок на выплату {@see PayoutRequest}).
 *
 * Наследование от User (та же таблица `users`) позволяет Filament-ресурсу «Партнёры»
 * и его политике доступа (PartnerPolicy) оставаться отдельными от ресурса
 * «Пользователи» (UserResource/UserPolicy), не дублируя данные.
 */
class Partner extends User
{
    protected $table = 'users';

    protected static function booted(): void
    {
        parent::booted();

        static::addGlobalScope('hasReferrals', function (Builder $query) {
            $query->whereHas('referredUsers');
        });
    }

    /**
     * Начисления, где этот пользователь выступает партнёром-получателем.
     */
    public function partnerTransactions(): HasMany
    {
        return $this->hasMany(PartnerTransaction::class, 'partner_user_id');
    }

    public function payoutRequests(): HasMany
    {
        return $this->hasMany(PayoutRequest::class, 'user_id');
    }

    /**
     * Сколько приглашённых совершили хотя бы одну оплату (выпуск карты или пополнение).
     */
    public function activeReferredUsersCount(): int
    {
        return $this->referredUsers()
            ->whereHas('incomes', fn ($query) => $query->where('payment_status', IncomePaymentStatus::Paid))
            ->count();
    }

    /**
     * Сумма всех начислений партнёру за всё время, $.
     */
    public function totalEarnedUsd(): float
    {
        return (float) $this->partnerTransactions()->sum('commission_amount');
    }

    /**
     * Сумма заявок на выплату в заданных статусах, $ (amount_usd — валюта начислений,
     * в отличие от amount_rub, который является суммой фактического перевода).
     *
     * @param  array<int, PayoutRequestStatus>  $statuses
     */
    protected function payoutRequestsSumUsd(array $statuses): float
    {
        return (float) $this->payoutRequests()->whereIn('status', $statuses)->sum('amount_usd');
    }

    /**
     * Ожидает к выплате — заявки поданы, но ещё не выплачены.
     */
    public function pendingPayoutUsd(): float
    {
        return $this->payoutRequestsSumUsd([PayoutRequestStatus::Pending, PayoutRequestStatus::Approved]);
    }

    public function paidPayoutUsd(): float
    {
        return $this->payoutRequestsSumUsd([PayoutRequestStatus::Paid]);
    }

    /**
     * Доступно к выводу — заработано за всё время минус уже учтённое в заявках на
     * выплату (ожидающих, одобренных и уже выплаченных), чтобы не вывести одну и ту
     * же сумму дважды.
     */
    public function availableBalanceUsd(): float
    {
        $locked = $this->payoutRequestsSumUsd([
            PayoutRequestStatus::Pending,
            PayoutRequestStatus::Approved,
            PayoutRequestStatus::Paid,
        ]);

        return round($this->totalEarnedUsd() - $locked, 2);
    }
}
