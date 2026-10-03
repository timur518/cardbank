<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * За что начислено партнёрское вознаграждение — см. App\Models\PartnerTransaction
 * и App\Services\Referral\ReferralService.
 */
enum PartnerTransactionType: string implements HasColor, HasLabel
{
    case CardIssue = 'card_issue';
    case CardTopup = 'card_topup';
    case Registration = 'registration';

    public function getLabel(): string
    {
        return match ($this) {
            self::CardIssue => 'Выпуск карты',
            self::CardTopup => 'Пополнение',
            self::Registration => 'Регистрация',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::CardIssue => 'info',
            self::CardTopup => 'success',
            self::Registration => 'gray',
        };
    }

    /**
     * Ставка этого типа начисления — процент от суммы операции (card_issue/card_topup)
     * или фиксированная сумма в $ (registration), см. PartnerTransactionsTable.
     */
    public function isPercentRate(): bool
    {
        return $this !== self::Registration;
    }
}
