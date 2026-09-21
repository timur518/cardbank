<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum CardNetwork: string implements HasLabel
{
    case MasterCard = 'mc';
    case Visa = 'visa';
    case Mir = 'mir';
    case UnionPay = 'unionpay';
    case Amex = 'amex';
    case Jcb = 'jcb';

    public function getLabel(): string
    {
        return match ($this) {
            self::MasterCard => 'MasterCard',
            self::Visa => 'Visa',
            self::Mir => 'Мир',
            self::UnionPay => 'UnionPay',
            self::Amex => 'American Express',
            self::Jcb => 'JCB',
        };
    }
}
