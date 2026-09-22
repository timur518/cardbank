<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Страна выпуска карты (CardProduct.card_country) — используется в админке (Select
 * вместо свободного текста, чтобы название страны и флаг были согласованы) и в API
 * карточных продуктов (флаг эмодзи + русское название для тултипа в ЛК).
 */
enum CardCountry: string implements HasLabel
{
    case Singapore = 'SG';
    case Usa = 'US';
    case HongKong = 'HK';

    public function getLabel(): string
    {
        return match ($this) {
            self::Singapore => 'Сингапур',
            self::Usa => 'США',
            self::HongKong => 'Гонконг',
        };
    }

    public function flag(): string
    {
        return match ($this) {
            self::Singapore => '🇸🇬',
            self::Usa => '🇺🇸',
            self::HongKong => '🇭🇰',
        };
    }
}
