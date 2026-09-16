<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum PromoCodeScope: string implements HasLabel
{
    case CardIssue = 'card_issue';
    case CardTopup = 'card_topup';

    public function getLabel(): string
    {
        return match ($this) {
            self::CardIssue => 'Выпуск карты',
            self::CardTopup => 'Пополнение карты',
        };
    }
}
