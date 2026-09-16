<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * Статус обработки входящего сообщения: используется для сообщений от карточного провайдера
 * и от платёжной системы — оба домена имеют одинаковый жизненный цикл обработки.
 */
enum MessageProcessingStatus: string implements HasColor, HasLabel
{
    case Pending = 'pending';
    case Processed = 'processed';
    case Failed = 'failed';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending => 'В ожидании',
            self::Processed => 'Обработано',
            self::Failed => 'Ошибка',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Processed => 'success',
            self::Failed => 'danger',
            self::Pending => 'warning',
        };
    }
}
