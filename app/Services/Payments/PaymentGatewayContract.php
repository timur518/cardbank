<?php

namespace App\Services\Payments;

use App\Models\PaymentMethod;
use Illuminate\Http\Request;

/**
 * Платёжная система для оплаты заказов ЛК (выпуск/пополнение карты). Реальный
 * провайдер в коде пока не подключён (см. CARD_ORDER_AND_ISSUANCE_FLOW.md, шаг 2–3) —
 * используется StubPaymentGateway, см. привязку в AppServiceProvider.
 */
interface PaymentGatewayContract
{
    /**
     * Создать платёж на сумму в рублях, вернуть id транзакции и ссылку на оплату.
     *
     * @return array{transaction_id: string, payment_url: string}
     */
    public function initiate(float $amountRub, string $description, string $orderReference): array;

    /**
     * Проверить подлинность вебхука (шаг 3 в CARD_ORDER_AND_ISSUANCE_FLOW.md) — подпись/
     * токен, схема целиком зависит от протокола конкретной платёжной системы. Секрет
     * читается из `PaymentMethod.settlement_config`.
     */
    public function verifyWebhookSignature(Request $request, PaymentMethod $paymentMethod): bool;

    /**
     * Разобрать тело вебхука в наш нормализованный формат — конкретные названия полей
     * зависят от платёжной системы.
     *
     * @param  array<string, mixed>  $payload
     * @return array{transaction_id: string, status: 'paid'|'failed'|'unknown'}
     */
    public function parseWebhookPayload(array $payload): array;
}
