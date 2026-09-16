<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum PaymentMethodType: string implements HasLabel
{
    case Gateway = 'gateway';
    case Crypto = 'crypto';
    case Card = 'card';
    case Wallet = 'wallet';

    public function getLabel(): string
    {
        return match ($this) {
            self::Gateway => 'Платёжный шлюз',
            self::Crypto => 'Криптовалюта',
            self::Card => 'Банковская карта',
            self::Wallet => 'Кошелёк',
        };
    }
}
