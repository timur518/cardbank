<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ProviderEnvironment: string implements HasColor, HasLabel
{
    case Sandbox = 'sandbox';
    case Production = 'production';

    public function getLabel(): string
    {
        return match ($this) {
            self::Sandbox => 'Тестовое',
            self::Production => 'Боевое',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Sandbox => 'warning',
            self::Production => 'danger',
        };
    }
}
