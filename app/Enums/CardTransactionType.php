<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum CardTransactionType: string implements HasLabel
{
    case Purchase = 'purchase';
    case Topup = 'topup';
    case Fee = 'fee';
    case Refund = 'refund';
    case Decline = 'decline';

    public function getLabel(): string
    {
        return match ($this) {
            self::Purchase => 'Покупка',
            self::Topup => 'Пополнение',
            self::Fee => 'Комиссия',
            self::Refund => 'Возврат',
            self::Decline => 'Отклонённый платёж',
        };
    }
}
