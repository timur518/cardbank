<?php

namespace App\Services\Referral;

use App\Enums\IncomeType;
use App\Enums\PartnerTransactionType;
use App\Models\Income;
use App\Models\PartnerTransaction;
use App\Models\Setting;
use App\Models\User;

/**
 * Автоматические начисления партнёрской программы (см. ReferralSettings и
 * PartnerTransaction): кто кого пригласил определяется по users.referral_code
 * (код приглашения в URL ?pid=, см. tracking.ts) против users.invite_code
 * приглашающего. Вызывается из AuthController (бонус за регистрацию) и
 * PaymentWebhookHandler (процент от оплаченного выпуска карты/пополнения).
 */
class ReferralService
{
    /**
     * Начисляет фиксированный бонус партнёру за регистрацию приглашённого им
     * пользователя (ReferralSettings::referral_registration_bonus_usd). Ничего не
     * делает, если у пользователя нет referral_code, код не найден среди
     * invite_code других пользователей, это самоприглашение или ставка равна 0.
     */
    public static function accrueForRegistration(User $newUser): ?PartnerTransaction
    {
        if (! $newUser->referral_code) {
            return null;
        }

        $partner = User::where('invite_code', $newUser->referral_code)->first();

        if (! $partner || $partner->id === $newUser->id) {
            return null;
        }

        // Идемпотентность: один бонус за регистрацию на одного приглашённого.
        if (PartnerTransaction::where('buyer_user_id', $newUser->id)
            ->where('type', PartnerTransactionType::Registration)
            ->exists()) {
            return null;
        }

        $bonus = (float) Setting::get('referral_registration_bonus_usd', 0);

        if ($bonus <= 0) {
            return null;
        }

        return PartnerTransaction::create([
            'partner_user_id' => $partner->id,
            'buyer_user_id' => $newUser->id,
            'type' => PartnerTransactionType::Registration,
            'income_id' => null,
            'rate' => $bonus,
            'commission_amount' => $bonus,
        ]);
    }

    /**
     * Начисляет партнёру процент (referral_issue_rate / referral_topup_rate) от
     * оплаченной суммы (Income.amount_usd) выпуска карты или пополнения приглашённым
     * им пользователем. Вызывается из PaymentWebhookHandler::handlePaid() сразу после
     * перевода Income в статус Paid.
     */
    public static function accrueForIncome(Income $income): ?PartnerTransaction
    {
        $type = match ($income->type) {
            IncomeType::CardIssue => PartnerTransactionType::CardIssue,
            IncomeType::CardTopup => PartnerTransactionType::CardTopup,
            default => null,
        };

        if (! $type) {
            return null;
        }

        // Идемпотентность: повторная доставка вебхука/двойной вызов не создаст второе начисление.
        if (PartnerTransaction::where('income_id', $income->id)->exists()) {
            return null;
        }

        $buyer = $income->user;

        if (! $buyer || ! $buyer->referral_code) {
            return null;
        }

        $partner = User::where('invite_code', $buyer->referral_code)->first();

        if (! $partner || $partner->id === $buyer->id) {
            return null;
        }

        $rateKey = $type === PartnerTransactionType::CardIssue ? 'referral_issue_rate' : 'referral_topup_rate';
        $rate = (float) Setting::get($rateKey, 0);

        if ($rate <= 0) {
            return null;
        }

        $commission = round(((float) $income->amount_usd) * $rate / 100, 2);

        if ($commission <= 0) {
            return null;
        }

        return PartnerTransaction::create([
            'partner_user_id' => $partner->id,
            'buyer_user_id' => $buyer->id,
            'type' => $type,
            'income_id' => $income->id,
            'rate' => $rate,
            'commission_amount' => $commission,
        ]);
    }
}
