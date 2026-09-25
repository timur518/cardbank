<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * Категория уведомления — определяет иконку/группировку в ленте «Уведомления» в
 * личном кабинете. Конкретные события (выпуск карты, отклонённая операция и т.п.)
 * подключаются позже — здесь только базовый набор укрупнённых категорий, к каждой
 * из которых будет привязано по несколько типов событий.
 */
enum NotificationType: string implements HasColor, HasLabel
{
    case System = 'system';
    case Card = 'card';
    case Payment = 'payment';
    case Security = 'security';
    case Promo = 'promo';
    case OtpCode = 'otp_code';
    case Kyc = 'kyc';

    public function getLabel(): string
    {
        return match ($this) {
            self::System => 'Системное',
            self::Card => 'Карта',
            self::Payment => 'Платежи',
            self::Security => 'Безопасность',
            self::Promo => 'Акции и предложения',
            self::OtpCode => 'Коды подтверждения',
            self::Kyc => 'Верификация личности',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::System => 'gray',
            self::Card => 'info',
            self::Payment => 'success',
            self::Security => 'danger',
            self::Promo => 'warning',
            self::OtpCode => 'primary',
            self::Kyc => 'info',
        };
    }
}
