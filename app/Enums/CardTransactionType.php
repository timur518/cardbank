<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum CardTransactionType: string implements HasColor, HasLabel
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

    public function getColor(): string
    {
        return match ($this) {
            self::Topup => 'success',
            self::Decline => 'danger',
            self::Purchase => 'warning',
            self::Fee => 'gray',
            self::Refund => 'info',
        };
    }
}
