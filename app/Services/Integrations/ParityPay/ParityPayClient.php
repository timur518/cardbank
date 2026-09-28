<?php

namespace App\Services\Integrations\ParityPay;

use App\Models\PaymentMethod;
use App\Services\Integrations\ParityPay\Exceptions\ParityPayException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * Низкоуровневый транспорт для API ParityPay (https://docs.paritypay.net):
 * авторизация заголовками `X-ShopId` (UUID кассы) и `X-SecretKey` (секретный ключ
 * кассы №1), JSON-запросы на `{base_url}/v2/*`. Бизнес-логики не содержит — только
 * HTTP. Использовать через {@see ParityPayGateway}, а не напрямую.
 */
class ParityPayClient
{
    protected const DEFAULT_BASE_URL = 'https://api.paritypay.net';

    public function __construct(protected PaymentMethod $paymentMethod) {}

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    public function get(string $path, array $query = []): array
    {
        $response = $this->client()
            ->get($this->url($path), $this->clean($query));

        return $this->handle($response);
    }

    /**
     * @param  array<string, mixed>  $body
     * @return array<string, mixed>
     */
    public function post(string $path, array $body = []): array
    {
        $response = $this->client()
            ->post($this->url($path), $this->clean($body));

        return $this->handle($response);
    }

    protected function client()
    {
        return Http::withHeaders([
            'X-ShopId' => $this->shopId(),
            'X-SecretKey' => $this->secretKey(),
        ])->acceptJson()->timeout($this->timeout());
    }

    /**
     * ParityPay просит не передавать `null`-значения в теле запроса вовсе, а не
     * присылать их пустыми (см. схему `InvoiceCreateRequest` в документации).
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function clean(array $data): array
    {
        return array_filter($data, fn ($value) => $value !== null && $value !== '');
    }

    protected function url(string $path): string
    {
        return rtrim($this->baseUrl(), '/').'/'.ltrim($path, '/');
    }

    /**
     * @return array<string, mixed>
     */
    protected function handle(Response $response): array
    {
        $json = $response->json();
        $body = is_array($json) ? $json : [];

        if ($response->failed() || isset($body['error'])) {
            $message = (string) ($body['error'] ?? "ParityPay API вернул ошибку {$response->status()}");

            throw new ParityPayException(
                "ParityPay API: {$message} (способ оплаты «{$this->paymentMethod->name}»).",
                $response->status(),
                $body,
            );
        }

        return $body;
    }

    protected function baseUrl(): string
    {
        return (string) ($this->paymentMethod->settlement_config['base_url'] ?? self::DEFAULT_BASE_URL);
    }

    public function shopId(): string
    {
        $shopId = (string) ($this->paymentMethod->settlement_config['shop_id'] ?? '');

        if ($shopId === '') {
            throw new ParityPayException("У способа оплаты «{$this->paymentMethod->name}» не заполнен ID кассы (settlement_config.shop_id).");
        }

        return $shopId;
    }

    public function secretKey(): string
    {
        $secretKey = (string) ($this->paymentMethod->settlement_config['secret_key'] ?? '');

        if ($secretKey === '') {
            throw new ParityPayException("У способа оплаты «{$this->paymentMethod->name}» не заполнен секретный ключ кассы №1 (settlement_config.secret_key).");
        }

        return $secretKey;
    }

    /**
     * Секретный ключ №2 — отдельный от ключа доступа к API (`secret_key`), используется
     * только для проверки подписи HTTP-уведомлений (заголовок `X-SIGNATURE`).
     */
    public function webhookSecretKey(): string
    {
        return (string) ($this->paymentMethod->settlement_config['webhook_secret_key'] ?? '');
    }

    protected function timeout(): int
    {
        return (int) config('services.paritypay.timeout', 20);
    }
}
