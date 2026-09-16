<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum NotificationChannel: string implements HasLabel
{
    case Email = 'email';
    case Telegram = 'telegram';
    case Push = 'push';

    public function getLabel(): string
    {
        return match ($this) {
            self::Email => 'Email',
            self::Telegram => 'Telegram',
            self::Push => 'Push-уведомление',
        };
    }
}
