<?php

namespace App\Services\Payments;

use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Заглушка платёжной системы: настоящий шлюз ещё не выбран/не подключён. Всегда
 * "успешно" создаёт платёж и возвращает фиктивную ссылку на оплату.
 *
 * Формат вебхука (`parseWebhookPayload()`) — наш собственный придуманный контракт,
 * рассчитанный на ручное тестирование ({@see routes/api.php}, `POST
 * /api/webhooks/payment/{paymentMethod}`):
 *
 *     {"transaction_id": "pt_...", "status": "paid"}   // или "status": "failed"
 *
 * При подключении реального провайдера нужно заменить именно эти два метода
 * (`verifyWebhookSignature()` и `parseWebhookPayload()`) на разбор его настоящего
 * формата — остальной код (PaymentWebhookController, PaymentWebhookHandler,
 * CardsProOrderProcessor) их не касается.
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

    /**
     * Заглушка проверяет только необязательный `?token=` по секрету из
     * `settlement_config.webhook_secret` (если он не заполнен — проверка пропускается,
     * как и у CardsPro, см. CardsProWebhookController::tokenIsValid()).
     */
    public function verifyWebhookSignature(Request $request, PaymentMethod $paymentMethod): bool
    {
        $secret = (string) ($paymentMethod->settlement_config['webhook_secret'] ?? '');

        if ($secret === '') {
            return true;
        }

        return hash_equals($secret, (string) $request->query('token'));
    }

    public function parseWebhookPayload(array $payload): array
    {
        $status = match ((string) ($payload['status'] ?? '')) {
            'paid' => 'paid',
            'failed' => 'failed',
            default => 'unknown',
        };

        return [
            'transaction_id' => (string) ($payload['transaction_id'] ?? ''),
            'status' => $status,
        ];
    }
}
