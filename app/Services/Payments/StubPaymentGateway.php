<?php

namespace App\Services\Payments;

use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Throwable;

/**
 * Заглушка платёжной системы: настоящий шлюз ещё не выбран/не подключён. Всегда
 * «успешно» создаёт платёж и возвращает фиктивную ссылку на оплату, ведущую на
 * несуществующий хост.
 *
 * Чтобы выпуск/пополнение можно было проверить целиком без реального шлюза,
 * `initiate()` сама эмулирует его успешный вебхук (`autoConfirm()`, вызывает тот же
 * PaymentWebhookHandler::handle(), что и реальный вебхук на
 * `POST /api/webhooks/payment/{paymentMethod}`) сразу после ответа клиенту. Это
 * единственное отличие заглушки от реальной интеграции — при подключении настоящего
 * шлюза (замена бинда `PaymentGatewayContract` в AppServiceProvider) эта эмуляция
 * просто перестаёт вызываться вместе с остальным StubPaymentGateway.
 *
 * Формат вебхука (`parseWebhookPayload()`) — собственный простой контракт для
 * ручного тестирования (`POST /api/webhooks/payment/{paymentMethod}`):
 *
 *     {"transaction_id": "pt_...", "status": "paid"}   // или "status": "failed"
 *
 * При подключении реального провайдера нужно заменить `verifyWebhookSignature()` и
 * `parseWebhookPayload()` на разбор его настоящего формата — остальной код
 * (PaymentWebhookController, PaymentWebhookHandler, CardsProOrderProcessor) его
 * не касается.
 */
class StubPaymentGateway implements PaymentGatewayContract
{
    public function initiate(float $amountRub, string $description, string $orderReference): array
    {
        $transactionId = 'pt_' . Str::uuid();

        $this->autoConfirm($transactionId);

        return [
            'transaction_id' => $transactionId,
            'payment_url' => "https://payment-gateway.example/pay/{$transactionId}",
        ];
    }

    /**
     * Эмулирует боевой вебхук об оплате: вызывает тот же PaymentWebhookHandler, что и
     * реальный вебхук, но откладывает вызов до afterResponse() — чтобы OrderController
     * уже успел сохранить `payment_transaction_id` в Income до того, как хендлер начнёт
     * искать заказ по нему. Работает без очереди — dispatch()->afterResponse() выполняет
     * замыкание синхронно в том же процессе сразу после отправки ответа клиенту. Ошибка
     * (например, CardsPro временно недоступен) только логируется — ответ клиенту к этому
     * моменту уже отправлен.
     */
    private function autoConfirm(string $transactionId): void
    {
        dispatch(function () use ($transactionId): void {
            try {
                app(PaymentWebhookHandler::class)->handle([
                    'transaction_id' => $transactionId,
                    'status' => 'paid',
                ]);
            } catch (Throwable $e) {
                report($e);
            }
        })->afterResponse();
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
