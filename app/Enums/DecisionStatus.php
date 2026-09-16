<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * Общий статус решения по заявке: используется для проверки личности (KycVerification)
 * и заявок на возврат (Refund) — оба домена имеют одинаковый жизненный цикл решения.
 */
enum DecisionStatus: string implements HasColor, HasLabel
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Declined = 'declined';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending => 'На рассмотрении',
            self::Approved => 'Одобрено',
            self::Declined => 'Отклонено',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Approved => 'success',
            self::Declined => 'danger',
            self::Pending => 'warning',
        };
    }
}
