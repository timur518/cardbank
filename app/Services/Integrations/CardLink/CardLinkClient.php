<?php

namespace App\Services\Integrations\CardLink;

use App\Models\PaymentMethod;
use App\Services\Integrations\CardLink\Exceptions\CardLinkException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * Низкоуровневый транспорт для API CardLink (https://cardlink.link/reference/api):
 * авторизация `Authorization: Bearer {api_token}` и JSON-запросы на
 * `{base_url}/*`. Бизнес-логики не содержит — только HTTP. Использовать через
 * {@see CardLinkGateway}, а не напрямую.
 */
class CardLinkClient
{
    protected const DEFAULT_BASE_URL = 'https://cardlink.link/api/v1';

    public function __construct(protected PaymentMethod $paymentMethod)
    {
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    public function get(string $path, array $query = []): array
    {
        $response = Http::withToken($this->apiToken())
            ->acceptJson()
            ->timeout($this->timeout())
            ->get($this->url($path), $this->clean($query));

        return $this->handle($response);
    }

    /**
     * @param  array<string, mixed>  $body
     * @return array<string, mixed>
     */
    public function post(string $path, array $body = []): array
    {
        $response = Http::withToken($this->apiToken())
            ->acceptJson()
            ->timeout($this->timeout())
            ->post($this->url($path), $this->clean($body));

        return $this->handle($response);
    }

    /**
     * CardLink просит не передавать пустые строки/NULL в полях вовсе, а не
     * присылать их пустыми (см. «Order Item Resource» в документации — то же
     * правило действует и для остальных методов).
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
        return rtrim($this->baseUrl(), '/') . '/' . ltrim($path, '/');
    }

    /**
     * @return array<string, mixed>
     */
    protected function handle(Response $response): array
    {
        $json = $response->json();
        $body = is_array($json) ? $json : [];

        if ($response->failed() || ($body['success'] ?? true) === false) {
            throw new CardLinkException(
                "CardLink API вернул ошибку {$response->status()} для способа оплаты «{$this->paymentMethod->name}».",
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

    protected function apiToken(): string
    {
        $token = (string) ($this->paymentMethod->settlement_config['api_token'] ?? '');

        if ($token === '') {
            throw new CardLinkException("У способа оплаты «{$this->paymentMethod->name}» не заполнен API-токен (settlement_config.api_token).");
        }

        return $token;
    }

    public function shopId(): string
    {
        $shopId = (string) ($this->paymentMethod->settlement_config['shop_id'] ?? '');

        if ($shopId === '') {
            throw new CardLinkException("У способа оплаты «{$this->paymentMethod->name}» не заполнен ID магазина (settlement_config.shop_id).");
        }

        return $shopId;
    }

    protected function timeout(): int
    {
        return (int) config('services.cardlink.timeout', 20);
    }
}
