<?php

namespace App\Enums;

use App\Services\Payments\PaymentGatewayResolver;
use Filament\Support\Contracts\HasLabel;

/**
 * Какая конкретная интеграция обслуживает способ оплаты — используется
 * {@see PaymentGatewayResolver} для выбора реализации
 * PaymentGatewayContract. Подключение новой платёжной системы — это один новый
 * `case` здесь и одна новая ветка `match` в резолвере.
 */
enum PaymentGatewayCode: string implements HasLabel
{
    case Stub = 'stub';
    case ParityPay = 'paritypay';

    public function getLabel(): string
    {
        return match ($this) {
            self::Stub => 'Заглушка (тестовый режим)',
            self::ParityPay => 'ParityPay',
        };
    }
}
