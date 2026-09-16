<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum KycStatus: string implements HasColor, HasLabel
{
    case NotStarted = 'not_started';
    case Pending = 'pending';
    case Approved = 'approved';
    case Declined = 'declined';

    public function getLabel(): string
    {
        return match ($this) {
            self::NotStarted => 'Не начата',
            self::Pending => 'На рассмотрении',
            self::Approved => 'Одобрена',
            self::Declined => 'Отклонена',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Approved => 'success',
            self::Declined => 'danger',
            self::Pending => 'warning',
            self::NotStarted => 'gray',
        };
    }
}
