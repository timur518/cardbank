<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum PayoutDestination: string implements HasLabel
{
    case Wallet = 'wallet';
    case BankCard = 'bank_card';

    public function getLabel(): string
    {
        return match ($this) {
            self::Wallet => 'Внутренний счёт',
            self::BankCard => 'Банковская карта',
        };
    }
}
