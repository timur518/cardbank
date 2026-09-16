<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum CardStatus: string implements HasColor, HasLabel
{
    case Pending = 'pending';
    case Active = 'active';
    case Frozen = 'frozen';
    case Closed = 'closed';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending => 'В процессе выпуска',
            self::Active => 'Активна',
            self::Frozen => 'Заморожена',
            self::Closed => 'Закрыта',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Active => 'success',
            self::Frozen => 'warning',
            self::Closed => 'gray',
            self::Pending => 'info',
        };
    }
}
