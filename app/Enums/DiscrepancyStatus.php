<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum DiscrepancyStatus: string implements HasColor, HasLabel
{
    case Open = 'open';
    case Resolved = 'resolved';

    public function getLabel(): string
    {
        return match ($this) {
            self::Open => 'Открыто',
            self::Resolved => 'Разобрано',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Open => 'danger',
            self::Resolved => 'success',
        };
    }
}
