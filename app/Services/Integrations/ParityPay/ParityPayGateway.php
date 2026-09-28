<?php

namespace App\Services\Integrations\ParityPay;

use App\Models\PaymentMethod;
use App\Services\Integrations\ParityPay\Exceptions\ParityPayException;
use App\Services\Payments\PaymentGatewayContract;
use Illuminate\Http\Request;

/**
 * Интеграция с платёжной системой ParityPay (https://docs.paritypay.net).
 * Ключи подключения читаются из `PaymentMethod.settlement_config`:
 *   - shop_id           — UUID кассы (заголовок X-ShopId), обязателен;
 *   - secret_key        — секретный ключ кассы №1 (заголовок X-SecretKey, доступ к API), обязателен;
 *   - webhook_secret_key — секретный ключ кассы №2 (подпись HTTP-уведомлений X-SIGNATURE), обязателен;
 *   - success_url, fail_url, base_url — опционально.
 * См. PaymentMethodForm. Несколько касс ParityPay подключаются как несколько
 * записей «Способы оплаты» с `gateway_code = paritypay`.
 */
class ParityPayGateway implements PaymentGatewayContract
{
    protected ParityPayClient $client;

    public function __construct(protected PaymentMethod $paymentMethod)
    {
        $this->client = new ParityPayClient($paymentMethod);
    }

    public static function for(PaymentMethod $paymentMethod): self
    {
        return new self($paymentMethod);
    }

    /**
     * Создаёт счёт (`POST /v2/invoice/create`) и возвращает ссылку на платёжную форму
     * (`link`) для редиректа клиента. `$orderReference` (Income.id) уходит как `order_id` —
     * уникален в рамках кассы, повторный вызов с тем же значением вернёт ошибку `422`.
     * Ответный `id` счёта в процессинге сохраняется как `transaction_id` и приходит
     * обратно в HTTP-уведомлении.
     */
    public function initiate(float $amountRub, string $description, string $orderReference): array
    {
        $config = $this->paymentMethod->settlement_config ?? [];

        $response = $this->client->post('/v2/invoice/create', [
            'order_id' => $orderReference,
            'amount' => (float) number_format($amountRub, 2, '.', ''),
            'comment' => $description,
            'success_url' => $config['success_url'] ?? null,
            'fail_url' => $config['fail_url'] ?? null,
            'callback_url' => $config['callback_url'] ?? null,
        ]);

        if (! isset($response['id'], $response['link'])) {
            throw new ParityPayException('ParityPay не вернул ссылку на оплату (id/link отсутствуют в ответе).', 0, $response);
        }

        return [
            'transaction_id' => (string) $response['id'],
            'payment_url' => (string) $response['link'],
        ];
    }

    /**
     * Подпись HTTP-уведомления (заголовок `X-SIGNATURE`): поля тела запроса
     * сортируются по ключам в алфавитном порядке, их значения объединяются в одну
     * строку без разделителей (`null` → пустая строка), от полученной строки
     * рассчитывается HMAC-SHA256 на секретном ключе №2 (`webhook_secret_key`,
     * отличном от ключа доступа к API). См. раздел «Проверка подписи» документации.
     */
    public function verifyWebhookSignature(Request $request, PaymentMethod $paymentMethod): bool
    {
        $secretKey2 = (string) ($paymentMethod->settlement_config['webhook_secret_key'] ?? '');
        $signature = (string) $request->header('X-SIGNATURE', '');

        if ($secretKey2 === '' || $signature === '') {
            return false;
        }

        $payload = $request->all();
        ksort($payload);

        $concatenated = implode('', array_map(
            fn ($value) => $value === null ? '' : (string) $value,
            $payload,
        ));

        $expected = hash_hmac('sha256', $concatenated, $secretKey2);

        return hash_equals($expected, $signature);
    }

    /**
     * Разбирает HTTP-уведомление по счёту (`InvoiceWebhook`, см. документацию,
     * раздел `webhooks.invoice`). `id` — тот же идентификатор счёта в процессинге,
     * что вернул `initiate()`.
     *
     * @param  array<string, mixed>  $payload
     */
    public function parseWebhookPayload(array $payload): array
    {
        $status = match (strtoupper((string) ($payload['status'] ?? ''))) {
            'PAID' => 'paid',
            'ERROR', 'EXPIRED' => 'failed',
            // NEW — счёт ещё не оплачен, REFUNDED — возврат по уже обработанному счёту:
            // окончательное решение уже принято раньше, тихо игнорируется PaymentWebhookHandler
            // (Income к этому моменту не в статусе Pending).
            default => 'unknown',
        };

        return [
            'transaction_id' => (string) ($payload['id'] ?? ''),
            'status' => $status,
        ];
    }

    /**
     * ParityPay не предоставляет метод API для инициирования возврата — возврат
     * выполняется на стороне процессинга, магазину приходит только HTTP-уведомление
     * со статусом `REFUNDED` (см. документацию, раздел `webhooks.invoice`).
     *
     * @return array{success: bool, refund_id: ?string, status: string, raw: array<string, mixed>}
     */
    public function refund(string $transactionId, ?float $amount = null): array
    {
        throw new ParityPayException("ParityPay не поддерживает создание возврата через API для способа оплаты «{$this->paymentMethod->name}» — возврат оформляется в личном кабинете ParityPay.");
    }

    /**
     * Баланс кассы (`GET /v2/shop/balance`): `balance` — доступный остаток,
     * `balance_hold` — сумма в заморозке. Отдельного понятия «заблокировано к
     * выплате» (`locked`) у ParityPay нет.
     *
     * @return array{available: float, locked: float, hold: float, currency: string}
     */
    public function getMasterBalance(): array
    {
        $response = $this->client->get('/v2/shop/balance');

        return [
            'available' => (float) ($response['balance'] ?? 0),
            'locked' => 0.0,
            'hold' => (float) ($response['balance_hold'] ?? 0),
            'currency' => (string) ($response['currency'] ?? ($this->paymentMethod->currency ?: 'RUB')),
        ];
    }
}
