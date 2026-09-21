<?php

namespace App\Services\Payments;

use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Throwable;

/**
 * Заглушка платёжной системы: настоящий шлюз ещё не выбран/не подключён. Всегда
 * "успешно" создаёт платёж и возвращает фиктивную ссылку на оплату, ведущую на
 * несуществующий хост (на реальном шлюзе клиент ушёл бы по ней оплачивать заказ).
 *
 * Чтобы выпуск/пополнение всё равно можно было проверить end-to-end без реального
 * шлюза, `initiate()` сама себе эмулирует его успешный вебхук (`autoConfirm()`,
 * вызывает тот же PaymentWebhookHandler::handle(), что и реальный вебхук на
 * `POST /api/webhooks/payment/{paymentMethod}`) сразу после ответа клиенту — к этому
 * моменту OrderController уже успеет сохранить `payment_transaction_id` в Income, так что
 * хендлер найдёт заказ по нему. Это единственное отличие заглушки от реальной
 * интеграции, живёт только внутри этого класса, не затрагивает OrderController,
 * интерфейс и сам PaymentWebhookHandler — при подключении настоящего шлюза
 * (замена бинда `PaymentGatewayContract` в AppServiceProvider на его реализацию) эта
 * эмуляция просто перестаёт вызываться вместе с остальным StubPaymentGateway.
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

        $this->autoConfirm($transactionId);

        return [
            'transaction_id' => $transactionId,
            'payment_url' => "https://payment-gateway.example/pay/{$transactionId}",
        ];
    }

    /**
     * Самоэмуляция боевой оплаты для этой заглушки: откладывает вызов того же
     * PaymentWebhookHandler, что и реальный вебхук, до afterResponse() — чтобы OrderController уже
     * успел сохранить `payment_transaction_id` в Income до того, как хендлер попытается
     * найти заказ по нему. Работает вне очереди (QUEUE_CONNECTION не важен) —
     * dispatchAfterResponse() выполняет замыкание синхронно в том же процессе, сразу после
     * отправки ответа клиенту. Сам вызов вне транзакции и try/catch, как у реального
     * PaymentWebhookController — ошибка (например, CardsPro временно недоступен) только
     * логируется — к этому моменту ответ клиенту уже отправлен, ронять её
     * нечем — а необработанное исключение внутри dispatchAfterResponse() не должно ломать
     * завершение запроса.
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
