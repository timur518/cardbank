<?php

namespace App\Services\Payments;

use App\Enums\PaymentGatewayCode;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\Webhooks\PaymentWebhookController;
use App\Models\PaymentMethod;
use App\Services\Integrations\ParityPay\ParityPayGateway;

/**
 * Единственное место, которое знает, какая интеграция обслуживает конкретный способ
 * оплаты («Способы оплаты» → `PaymentMethod.gateway_code`). Вебхук
 * ({@see PaymentWebhookController}) и оформление
 * заказа ({@see OrderController}) вызывают только этот
 * метод и интерфейс {@see PaymentGatewayContract} — подключение новой платёжной
 * системы сводится к добавлению одной ветки `match` здесь и одного `case` в
 * {@see PaymentGatewayCode}, без изменений в контроллерах.
 */
class PaymentGatewayResolver
{
    public static function for(PaymentMethod $paymentMethod): PaymentGatewayContract
    {
        return match ($paymentMethod->gateway_code) {
            PaymentGatewayCode::ParityPay => ParityPayGateway::for($paymentMethod),
            // null или ещё не подключённая интеграция — тестовая заглушка (см. привязку в AppServiceProvider).
            default => app(PaymentGatewayContract::class),
        };
    }
}
