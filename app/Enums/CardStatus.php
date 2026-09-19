<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum CardStatus: string implements HasColor, HasLabel
{
    case Waiting = 'waiting';
    case Pending = 'pending';
    case Active = 'active';
    case Frozen = 'frozen';
    case Closed = 'closed';
    case Cancelled = 'cancelled';
    case Failed = 'failed';

    public function getLabel(): string
    {
        return match ($this) {
            self::Waiting => 'Ожидает оплаты',
            self::Pending => 'В процессе выпуска',
            self::Active => 'Активна',
            self::Frozen => 'Заморожена',
            self::Closed => 'Закрыта',
            self::Cancelled => 'Отменён',
            self::Failed => 'Ошибка выпуска',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Active => 'success',
            self::Frozen => 'warning',
            self::Closed => 'gray',
            self::Waiting => 'info',
            self::Pending => 'info',
            self::Cancelled => 'gray',
            self::Failed => 'danger',
        };
    }
}
