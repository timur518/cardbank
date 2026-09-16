<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum RiskFlagType: string implements HasLabel
{
    case SuspiciousActivity = 'suspicious_activity';
    case MultiAccounting = 'multi_accounting';
    case Chargeback = 'chargeback';
    case Other = 'other';

    public function getLabel(): string
    {
        return match ($this) {
            self::SuspiciousActivity => 'Подозрительная активность',
            self::MultiAccounting => 'Множественные аккаунты',
            self::Chargeback => 'Чарджбэк',
            self::Other => 'Другое',
        };
    }
}
