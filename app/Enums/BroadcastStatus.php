<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum BroadcastStatus: string implements HasColor, HasLabel
{
    case Draft = 'draft';
    case Sending = 'sending';
    case Completed = 'completed';
    case Failed = 'failed';

    public function getLabel(): string
    {
        return match ($this) {
            self::Draft => 'Черновик',
            self::Sending => 'Отправляется',
            self::Completed => 'Завершена',
            self::Failed => 'Ошибка',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Sending => 'warning',
            self::Completed => 'success',
            self::Failed => 'danger',
        };
    }
}
