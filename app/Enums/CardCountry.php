<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Страна выпуска карты (CardProduct.card_country) — используется в админке (Select
 * вместо свободного текста, чтобы название страны и флаг были согласованы) и в API
 * карточных продуктов (URL круглого SVG-флага + русское название для тултипа в ЛК).
 * SVG-файлы лежат в public/assets/images — те же статичные ассеты лендинга.
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

    public function flagUrl(): string
    {
        return asset('assets/images/' . match ($this) {
            self::Singapore => 'singapore.svg',
            self::Usa => 'usa.svg',
            self::HongKong => 'honkong.svg',
        });
    }
}
