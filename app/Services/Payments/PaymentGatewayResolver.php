<?php

namespace App\Services\Payments;

use App\Enums\PaymentGatewayCode;
use App\Models\PaymentMethod;
use App\Services\Integrations\CardLink\CardLinkGateway;

/**
 * Единственное место, которое знает, какая интеграция обслуживает конкретный способ
 * оплаты («Способы оплаты» → `PaymentMethod.gateway_code`). Вебхук
 * ({@see \App\Http\Controllers\Api\Webhooks\PaymentWebhookController}) и оформление
 * заказа ({@see \App\Http\Controllers\Api\V1\OrderController}) вызывают только этот
 * метод и интерфейс {@see PaymentGatewayContract} — подключение новой платёжной
 * системы сводится к добавлению одной ветки `match` здесь и одного `case` в
 * {@see PaymentGatewayCode}, без изменений в контроллерах.
 */
class PaymentGatewayResolver
{
    public static function for(PaymentMethod $paymentMethod): PaymentGatewayContract
    {
        return match ($paymentMethod->gateway_code) {
            PaymentGatewayCode::CardLink => CardLinkGateway::for($paymentMethod),
            // null или ещё не подключённая интеграция — тестовая заглушка (см. привязку в AppServiceProvider).
            default => app(PaymentGatewayContract::class),
        };
    }
}
