<?php

namespace App\Services\Integrations\Bitbanker;

use App\Models\PaymentMethod;
use App\Services\Integrations\Bitbanker\Exceptions\BitbankerException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * Низкоуровневый транспорт для API BitBanker (DEV: база https://ext-api.dev.bitbanker.ru,
 * swagger https://ext-api.dev.bitbanker.ru/docs/public/openapi; PROD: база
 * https://api.aws.bitbanker.org/latest): авторизация заголовком `X-API-KEY`,
 * подпись `full_sign` через {@see BitbankerSigner} — в теле для POST-запросов,
 * в query-параметрах для GET. Бизнес-логики не содержит — только HTTP.
 * Использовать через {@see \App\Services\Integrations\Bitbanker\BitbankerGateway}
 * или {@see \App\Services\Integrations\Bitbanker\BitbankerClientService}, а не напрямую.
 */
class BitbankerClient
{
    protected const DEFAULT_BASE_URL = 'https://ext-api.dev.bitbanker.ru';

    protected BitbankerSigner $signer;

    public function __construct(protected PaymentMethod $paymentMethod)
    {
        $this->signer = new BitbankerSigner;
    }

    /**
     * Подписанный POST-запрос: `timestamp`/`nonce`/`full_sign` добавляются в тело.
     * `$idempotencyKey` обязателен у BitBanker для `POST /api/v2/invoices` и
     * `POST`/`GET /api/v2/partner-clients`.
     *
     * @param  array<string, mixed>  $body
     * @return array<string, mixed>
     */
    public function postSigned(string $path, array $body, ?string $idempotencyKey = null): array
    {
        $payload = $this->clean(array_merge($body, [
            'timestamp' => now()->timestamp,
            'nonce' => $this->signer->nonce(),
        ]));
        $payload['full_sign'] = $this->signer->sign($payload, $this->apiSecret());

        return $this->handle($this->httpClient($idempotencyKey)->post($this->url($path), $payload));
    }

    /**
     * Подписанный GET-запрос (опрос статуса): `timestamp`/`nonce`/`full_sign`
     * передаются в query-строке, а не в теле — Idempotency-Key не нужен (запрос
     * не создаёт данных).
     *
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    public function getSigned(string $path, array $query): array
    {
        $payload = $this->clean(array_merge($query, [
            'timestamp' => now()->timestamp,
            'nonce' => $this->signer->nonce(),
        ]));
        $payload['full_sign'] = $this->signer->sign($payload, $this->apiSecret());

        return $this->handle($this->httpClient()->get($this->url($path), $payload));
    }

    protected function httpClient(?string $idempotencyKey = null): PendingRequest
    {
        $client = Http::withHeaders(['X-API-KEY' => $this->apiKey()])
            ->acceptJson()
            ->timeout($this->timeout());

        if ($idempotencyKey !== null) {
            $client = $client->withHeaders(['Idempotency-Key' => $idempotencyKey]);
        }

        return $client;
    }

    /**
     * BitBanker просит не передавать необязательные поля вовсе, если они не
     * заполнены (`null`), а не присылать их пустыми — иначе часть bool-флагов с
     * `default: null` в схеме (см. swagger) лучше оставить реальным дефолтом
     * BitBanker, чем явно слать `null`. Важно: фильтрация идёт ДО расчёта
     * `full_sign` (см. postSigned()/getSigned()) — подпись должна совпадать
     * ровно с тем, что реально уходит в запросе.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function clean(array $data): array
    {
        return array_filter($data, fn ($value) => $value !== null);
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

        if ($response->failed()) {
            $code = (string) ($body['code'] ?? $body['detail'] ?? '');
            $message = $code !== '' ? $code : "BitBanker API вернул ошибку {$response->status()}";

            throw new BitbankerException(
                "BitBanker API: {$message} (способ оплаты «{$this->paymentMethod->name}»).",
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

    public function apiKey(): string
    {
        $apiKey = (string) ($this->paymentMethod->settlement_config['api_key'] ?? '');

        if ($apiKey === '') {
            throw new BitbankerException("У способа оплаты «{$this->paymentMethod->name}» не заполнен API-ключ (settlement_config.api_key).");
        }

        return $apiKey;
    }

    public function apiSecret(): string
    {
        $apiSecret = (string) ($this->paymentMethod->settlement_config['api_secret'] ?? '');

        if ($apiSecret === '') {
            throw new BitbankerException("У способа оплаты «{$this->paymentMethod->name}» не заполнен API-секрет (settlement_config.api_secret).");
        }

        return $apiSecret;
    }

    protected function timeout(): int
    {
        return (int) config('services.bitbanker.timeout', 20);
    }
}
