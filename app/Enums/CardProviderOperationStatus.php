<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum CardProviderOperationStatus: string implements HasColor, HasLabel
{
    case Pending = 'pending';
    case Completed = 'completed';
    case Failed = 'failed';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending => 'Ожидает подтверждения',
            self::Completed => 'Выполнена',
            self::Failed => 'Не выполнена',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Completed => 'success',
            self::Failed => 'danger',
        };
    }
}
