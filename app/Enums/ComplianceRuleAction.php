<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ComplianceRuleAction: string implements HasColor, HasLabel
{
    case Warn = 'warn';
    case Block = 'block';

    public function getLabel(): string
    {
        return match ($this) {
            self::Warn => 'Предупредить',
            self::Block => 'Заблокировать',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Warn => 'warning',
            self::Block => 'danger',
        };
    }
}
