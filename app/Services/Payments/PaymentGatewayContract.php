<?php

namespace App\Services\Payments;

use App\Models\PaymentMethod;
use Illuminate\Http\Request;

/**
 * Платёжная система для оплаты заказов личного кабинета (выпуск/пополнение
 * карты). Абстрагирует конкретного провайдера, чтобы его можно было подключить/
 * заменить, не меняя OrderController и PaymentWebhookController. Реальный провайдер
 * в коде пока не подключён — используется StubPaymentGateway, см. привязку в AppServiceProvider.
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
     * Проверить подлинность входящего вебхука — подпись/токен, схема
     * целиком зависит от протокола конкретной платёжной системы. Секрет
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

    /**
     * Создать возврат по уже проведённому платежу — полный (amount = null) или частичный.
     * $transactionId — тот же идентификатор, что вернул initiate() и что пришёл в вебхуке
     * (Income.payment_transaction_id).
     *
     * @return array{success: bool, refund_id: ?string, status: string, raw: array<string, mixed>}
     */
    public function refund(string $transactionId, ?float $amount = null): array;

    /**
     * Баланс мастер-счёта платёжной системы (реквизиты — из settlement_config способа
     * оплаты, для которого резолвился этот шлюз, см. PaymentGatewayResolver::for()).
     *
     * @return array{
     *     available: float,
     *     locked: float,
     *     hold: float,
     *     currency: string,
     * } available — доступный баланс, locked — заблокировано к выплате, hold — временный
     *   холд (перейдёт в available спустя время).
     */
    public function getMasterBalance(): array;
}
