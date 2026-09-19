<?php

namespace App\Services\Payments;

use Illuminate\Support\Str;

/**
 * Заглушка платёжной системы: настоящий шлюз ещё не выбран/не подключён. Всегда
 * "успешно" создаёт платёж и возвращает фиктивную ссылку на оплату — реальное
 * подтверждение оплаты (вебхук платёжной системы, шаг 3 в
 * CARD_ORDER_AND_ISSUANCE_FLOW.md) в код пока не входит.
 */
class StubPaymentGateway implements PaymentGatewayContract
{
    public function initiate(float $amountRub, string $description, string $orderReference): array
    {
        $transactionId = 'pt_' . Str::uuid();

        return [
            'transaction_id' => $transactionId,
            'payment_url' => "https://payment-gateway.example/pay/{$transactionId}",
        ];
    }
}
