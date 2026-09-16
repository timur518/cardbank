<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum KycVerificationType: string implements HasLabel
{
    case Internal = 'internal';
    case Provider = 'provider';

    public function getLabel(): string
    {
        return match ($this) {
            self::Internal => 'Внутренняя',
            self::Provider => 'У провайдера',
        };
    }
}
