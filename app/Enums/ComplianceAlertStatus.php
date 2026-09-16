<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ComplianceAlertStatus: string implements HasColor, HasLabel
{
    case NeedsReview = 'needs_review';
    case Confirmed = 'confirmed';
    case FalsePositive = 'false_positive';

    public function getLabel(): string
    {
        return match ($this) {
            self::NeedsReview => 'Требует внимания',
            self::Confirmed => 'Нарушение подтверждено',
            self::FalsePositive => 'Ложное срабатывание',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::NeedsReview => 'warning',
            self::Confirmed => 'danger',
            self::FalsePositive => 'gray',
        };
    }
}
