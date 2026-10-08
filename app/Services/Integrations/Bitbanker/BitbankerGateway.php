<?php

namespace App\Services\Integrations\Bitbanker;

use App\Models\Income;
use App\Models\PaymentMethod;
use App\Services\Integrations\Bitbanker\Exceptions\BitbankerException;
use App\Services\Payments\PaymentGatewayContract;
use Illuminate\Http\Request;

/**
 * Интеграция с платёжной системой BitBanker — оплата по СБП с автоматической
 * конвертацией в USDT (DEV-swagger: https://ext-api.dev.bitbanker.ru/docs/public/openapi,
 * PROD: https://api.bitbanker.org/latest/docs/public/openapi). Ключи подключения
 * читаются из `PaymentMethod.settlement_config`:
 *   - api_key    — заголовок X-API-KEY, обязателен;
 *   - api_secret — расчёт full_sign (запросы и вебхуки), обязателен;
 *   - base_url   — опционально, по умолчанию DEV;
 *   - invoice_header — опционально, название организации в счёте (иначе config('app.name')).
 */
class BitbankerGateway implements PaymentGatewayContract
{
    protected BitbankerClient $client;

    protected BitbankerSigner $signer;

    public function __construct(protected PaymentMethod $paymentMethod)
    {
        $this->client = new BitbankerClient($paymentMethod);
        $this->signer = new BitbankerSigner;
    }

    public static function for(PaymentMethod $paymentMethod): self
    {
        return new self($paymentMethod);
    }

    /**
     * Создаёt инвойс с оплатой по СБП и конвертацией в USDT (`POST /api/v2/invoices`).
     * `$orderReference` — `Income.id`: в отличие от ParityPay, BitBanker не принимает
     * свой order_id в URL, а требует `partner_client_external_id` (uuid пользователя) —
     * поэтому Income подгружается заново вместе с `user` (initiate() контракта не меняется,
     * доп. данные достаются отсюда).
     * `Idempotency-Key` — `Income.idempotency_key` (тот же, что получает и ParityPay
     * через order_id — здесь обязателен заголовком, см. BitbankerClient::postSigned()).
     *
     * Возвращает `payment_url` = `sbp_info.qr_url` (ссылка НСПК) и дополнительно
     * `qr_code` (`sbp_info.sbp_qr`, base64 PNG) + `fallback_url` (`link`, хостед-страница
     * инвойса BitBanker) — контракт расширен необязательными ключами по сравнению с базовым PaymentGatewayContract::initiate().
     *
     * @return array{transaction_id: string, payment_url: string, qr_code: ?string, fallback_url: ?string}
     */
    public function initiate(float $amountRub, string $description, string $orderReference): array
    {
        $income = Income::with('user')->find((int) $orderReference);
        $user = $income?->user;

        if (! $user) {
            throw new BitbankerException("Не найден пользователь заказа #{$orderReference} для регистрации инвойса BitBanker.");
        }

        $response = $this->client->postSigned('/api/v2/invoices', [
            'payment_currencies' => ['RUBR'],
            'currency' => 'RUBR',
            'amount' => (float) number_format($amountRub, 2, '.', ''),
            'description' => $description,
            'header' => (string) ($this->paymentMethod->settlement_config['invoice_header'] ?? config('app.name')),
            'sbp_payment' => true,
            'is_convert_payments' => true,
            'take_currency' => 'USDT',
            'partner_client_external_id' => $user->uuid,
        ], $income->idempotency_key);

        if (! isset($response['id'])) {
            throw new BitbankerException('BitBanker не вернул id инвойса.', 0, $response);
        }

        $sbpInfo = (array) ($response['sbp_info'] ?? []);
        $qrUrl = $sbpInfo['qr_url'] ?? null;
        $qrCode = $sbpInfo['sbp_qr'] ?? null;
        $fallbackUrl = $response['link'] ?? null;

        if (blank($qrUrl) && blank($qrCode)) {
            throw new BitbankerException('BitBanker не вернул ни ссылку, ни QR-код для оплаты по СБП.', 0, $response);
        }

        return [
            'transaction_id' => (string) $response['id'],
            'payment_url' => (string) ($qrUrl ?? ''),
            'qr_code' => $qrCode,
            'fallback_url' => $fallbackUrl,
        ];
    }

    /**
     * `full_sign` лежит в самом теле вебхука (не в заголовке, в отличие от
     * ParityPay) — см. BitbankerSigner::verify().
     */
    public function verifyWebhookSignature(Request $request, PaymentMethod $paymentMethod): bool
    {
        $apiSecret = (string) ($paymentMethod->settlement_config['api_secret'] ?? '');
        $signature = (string) ($request->input('full_sign') ?? '');

        return $this->signer->verify($request->getContent(), $signature, $apiSecret);
    }

    /**
     * Разбирает `invoices_webhook` (формат v2 — тот же payload, что и у
     * `GET /api/v2/invoices`). `payed=true` — top-level признак фактической оплаты
     * (надёжнее `sbp_info.status`, который может быть промежуточным `authorized`
     * и впоследствии ещё смениться); непустой `exchange_deal` появляется, как
     * только сделка конвертации обработана. Полный список статусов `sbp_info.status`
     * см. в документации BitBanker — успех: `authorized`/`captured`, отказ:
     * `declined`/`failed`/`cancelled`, просрочка: `expired`, остальное — не финально.
     *
     * Дополнительно возвращает сумму реальной конвертации в USDT
     * (`exchange_deal[0].volume_take_final`) через ключ `amount_usd` — контракт
     * расширен необязательным ключом по сравнению с базовым PaymentGatewayContract::parseWebhookPayload().
     *
     * @param  array<string, mixed>  $payload
     * @return array{transaction_id: string, status: 'paid'|'failed'|'unknown', amount_usd: ?float}
     */
    public function parseWebhookPayload(array $payload): array
    {
        $transactionId = (string) ($payload['id'] ?? '');
        $exchangeDeal = (array) ($payload['exchange_deal'] ?? []);
        $sbpStatus = (string) ($payload['sbp_info']['status'] ?? '');

        $status = match (true) {
            ($payload['payed'] ?? false) === true && $exchangeDeal !== [] => 'paid',
            in_array($sbpStatus, ['declined', 'failed', 'cancelled', 'expired'], true) => 'failed',
            default => 'unknown',
        };

        $amountUsd = $status === 'paid' && isset($exchangeDeal[0]['volume_take_final'])
            ? (float) $exchangeDeal[0]['volume_take_final']
            : null;

        return [
            'transaction_id' => $transactionId,
            'status' => $status,
            'amount_usd' => $amountUsd,
        ];
    }

    /**
     * У BitBanker нет API для инициирования возврата — вывод USDT и спорные
     * транзакции обрабатываются вручную через их личный кабинет/почту compliance@bitbanker.org.
     *
     * @return array{success: bool, refund_id: ?string, status: string, raw: array<string, mixed>}
     */
    public function refund(string $transactionId, ?float $amount = null): array
    {
        throw new BitbankerException("BitBanker не поддерживает создание возврата через API для способа оплаты «{$this->paymentMethod->name}» — обратитесь в личный кабинет BitBanker.");
    }

    /**
     * У BitBanker нет публичного API баланса мастер-счёта для партнёров.
     *
     * @return array{available: float, locked: float, hold: float, currency: string}
     */
    public function getMasterBalance(): array
    {
        throw new BitbankerException("BitBanker не поддерживает получение баланса через API для способа оплаты «{$this->paymentMethod->name}».");
    }
}
