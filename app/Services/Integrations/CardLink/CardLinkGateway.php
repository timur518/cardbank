<?php

namespace App\Services\Integrations\CardLink;

use App\Models\PaymentMethod;
use App\Services\Integrations\CardLink\Exceptions\CardLinkException;
use App\Services\Payments\PaymentGatewayContract;
use Illuminate\Http\Request;

/**
 * Интеграция с платёжной системой CardLink (https://cardlink.link/reference/api).
 * Ключи подключения читаются из `PaymentMethod.settlement_config` (api_token,
 * shop_id — обязательны; success_url, fail_url, base_url — опционально), см.
 * PaymentMethodForm. Несколько магазинов CardLink подключаются как несколько
 * записей «Способы оплаты» с `gateway_code = cardlink`.
 */
class CardLinkGateway implements PaymentGatewayContract
{
    protected CardLinkClient $client;

    public function __construct(protected PaymentMethod $paymentMethod)
    {
        $this->client = new CardLinkClient($paymentMethod);
    }

    public static function for(PaymentMethod $paymentMethod): self
    {
        return new self($paymentMethod);
    }

    /**
     * Создаёт одноразовый счёт (type=normal — оплатить второй раз по той же ссылке
     * нельзя, что соответствует одному заказу выпуска/пополнения карты) и
     * возвращает ссылку на оплату (link_page_url) для редиректа клиента.
     * $orderReference (Income.id) уходит как order_id и возвращается CardLink
     * в постбэке в поле InvId.
     */
    public function initiate(float $amountRub, string $description, string $orderReference): array
    {
        $config = $this->paymentMethod->settlement_config ?? [];

        $response = $this->client->post('/bill/create', [
            'amount' => number_format($amountRub, 2, '.', ''),
            'shop_id' => $this->client->shopId(),
            'order_id' => $orderReference,
            'description' => $description,
            'type' => 'normal',
            'currency_in' => 'RUB',
            'success_url' => $config['success_url'] ?? null,
            'fail_url' => $config['fail_url'] ?? null,
        ]);

        if (! isset($response['bill_id'], $response['link_page_url'])) {
            throw new CardLinkException('CardLink не вернул ссылку на оплату (bill_id/link_page_url отсутствуют в ответе).', 0, $response);
        }

        return [
            'transaction_id' => (string) $response['bill_id'],
            'payment_url' => (string) $response['link_page_url'],
        ];
    }

    /**
     * Постбэк (Result URL) подписан тем же алгоритмом, что и редиректы Success/Fail
     * URL (см. документацию, разделы «Success POST request»/«Fail POST request»):
     *   SignatureValue = strtoupper(md5($OutSum . ":" . $InvId . ":" . $apiToken))
     */
    public function verifyWebhookSignature(Request $request, PaymentMethod $paymentMethod): bool
    {
        $token = (string) ($paymentMethod->settlement_config['api_token'] ?? '');
        $signature = (string) $request->input('SignatureValue', '');

        if ($token === '' || $signature === '') {
            return false;
        }

        $outSum = (string) $request->input('OutSum', '');
        $invId = (string) $request->input('InvId', '');
        $expected = strtoupper(md5($outSum . ':' . $invId . ':' . $token));

        return hash_equals($expected, strtoupper($signature));
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function parseWebhookPayload(array $payload): array
    {
        $status = match (strtoupper((string) ($payload['Status'] ?? ''))) {
            'SUCCESS' => 'paid',
            'FAIL' => 'failed',
            // NEW/PROCESS/UNDERPAID/OVERPAID — промежуточные статусы счёта, окончательного
            // решения ещё нет, ждём следующий постбэк (тихо игнорируется PaymentWebhookHandler).
            default => 'unknown',
        };

        return [
            // Для одноразового счёта (type=normal, см. initiate()) TrsId в постбэке — тот же
            // идентификатор, что и bill_id, возвращённый при создании счёта (см. пример
            // "Basic example of using the API" в документации CardLink, где оба поля совпадают).
            'transaction_id' => (string) ($payload['TrsId'] ?? ''),
            'status' => $status,
        ];
    }

    /**
     * @return array{success: bool, refund_id: ?string, status: string, raw: array<string, mixed>}
     */
    public function refund(string $transactionId, ?float $amount = null): array
    {
        $response = $amount === null
            ? $this->client->post('/refund/full/create', ['payment_id' => $transactionId])
            : $this->client->post('/refund/partial/create', [
                'payment_id' => $transactionId,
                'amount' => number_format($amount, 2, '.', ''),
            ]);

        return [
            'success' => (bool) ($response['success'] ?? false),
            'refund_id' => isset($response['id']) ? (string) $response['id'] : null,
            'status' => (string) ($response['status'] ?? 'unknown'),
            'raw' => $response,
        ];
    }

    /**
     * @return array{available: float, locked: float, hold: float, currency: string}
     */
    public function getMasterBalance(): array
    {
        $response = $this->client->get('/merchant/balance');
        $balances = $response['balances'] ?? [];
        $currency = (string) ($this->paymentMethod->currency ?: 'RUB');

        // У мерчанта CardLink может быть несколько балансов (по одному на валюту) — берём тот,
        // что совпадает с валютой расчётов способа оплаты, иначе первый попавшийся (обычно баланс один).
        $balance = collect($balances)->firstWhere('currency', $currency) ?? ($balances[0] ?? null);

        if (! $balance) {
            return ['available' => 0.0, 'locked' => 0.0, 'hold' => 0.0, 'currency' => $currency];
        }

        return [
            'available' => (float) ($balance['balance_available'] ?? 0),
            'locked' => (float) ($balance['balance_locked'] ?? 0),
            'hold' => (float) ($balance['balance_hold'] ?? 0),
            'currency' => (string) ($balance['currency'] ?? $currency),
        ];
    }
}
