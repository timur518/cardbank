<?php

namespace App\Services\Payments;

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
}
