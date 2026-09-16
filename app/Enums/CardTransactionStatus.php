<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum CardTransactionStatus: string implements HasColor, HasLabel
{
    case Pending = 'pending';
    case Success = 'success';
    case Declined = 'declined';
    case Reversed = 'reversed';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending => 'В обработке',
            self::Success => 'Успешно',
            self::Declined => 'Отклонена',
            self::Reversed => 'Возвращена',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Success => 'success',
            self::Declined => 'danger',
            self::Reversed => 'gray',
            self::Pending => 'warning',
        };
    }
}
