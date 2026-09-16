<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum PartnerStatus: string implements HasColor, HasLabel
{
    case Active = 'active';
    case Blocked = 'blocked';

    public function getLabel(): string
    {
        return match ($this) {
            self::Active => 'Активен',
            self::Blocked => 'Заблокирован',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Active => 'success',
            self::Blocked => 'danger',
        };
    }
}
