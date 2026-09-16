<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum IncomeType: string implements HasLabel
{
    case CardIssue = 'card_issue';
    case CardTopup = 'card_topup';
    case PaidRefund = 'paid_refund';
    case Penalty = 'penalty';
    case Other = 'other';

    public function getLabel(): string
    {
        return match ($this) {
            self::CardIssue => 'Выпуск карты',
            self::CardTopup => 'Пополнение карты',
            self::PaidRefund => 'Платный возврат',
            self::Penalty => 'Штраф',
            self::Other => 'Прочие',
        };
    }
}
