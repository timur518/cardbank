<?php

namespace App\Services\Integrations\CardsPro;

use App\Models\CardProvider;
use App\Services\Integrations\CardsPro\Exceptions\CardsProException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * Низкоуровневый транспорт для API CardsPro: собирает подпись запроса
 * (заголовки CAP-TOKEN/CAP-NONCE/CAP-SIGN) и отправляет GET/POST на `/cards/v1/*`.
 * Бизнес-логики не содержит — только HTTP и подпись. Использовать через
 * {@see CardsProService}, а не напрямую.
 *
 * Алгоритм подписи описан в https://docs.cardspro.com/getting-started/quickstart:
 *   GET:  CAP-SIGN = sha256Hex(CAP-NONCE + query_string + secret)
 *   POST: CAP-SIGN = sha256Hex(CAP-NONCE + request_body + query_string + secret)
 */
class CardsProClient
{
    protected const BASE_PATH = '/cards/v1';

    public function __construct(protected CardProvider $provider)
    {
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    public function get(string $path, array $query = []): array
    {
        $queryString = $this->buildQueryString($query);
        $nonce = $this->nonce();
        $sign = $this->sign($nonce, '', $queryString);

        $response = Http::withHeaders($this->headers($nonce, $sign))
            ->timeout($this->timeout())
            ->get($this->url($path, $queryString));

        return $this->handle($response);
    }

    /**
     * Тело запроса кодируется в JSON один раз и переиспользуется как для подписи,
     * так и для самой отправки — это гарантирует, что подписан ровно тот текст,
     * который уходит на сервер.
     *
     * @param  array<string, mixed>  $body
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    public function post(string $path, array $body = [], array $query = []): array
    {
        $queryString = $this->buildQueryString($query);
        $body = array_filter($body, fn ($value) => $value !== null);
        $jsonBody = json_encode((object) $body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        $nonce = $this->nonce();
        $sign = $this->sign($nonce, $jsonBody, $queryString);

        $response = Http::withHeaders($this->headers($nonce, $sign))
            ->withBody($jsonBody, 'application/json')
            ->timeout($this->timeout())
            ->post($this->url($path, $queryString));

        return $this->handle($response);
    }

    protected function nonce(): string
    {
        return (string) (int) round(microtime(true) * 1000);
    }

    protected function sign(string $nonce, string $jsonBody, string $queryString): string
    {
        return hash('sha256', $nonce . $jsonBody . $queryString . $this->secret());
    }

    /**
     * @return array<string, string>
     */
    protected function headers(string $nonce, string $sign): array
    {
        return [
            'CAP-TOKEN' => $this->apiKey(),
            'CAP-NONCE' => $nonce,
            'CAP-SIGN' => $sign,
            'Accept' => 'application/json',
        ];
    }

    /**
     * @param  array<string, mixed>  $query
     */
    protected function buildQueryString(array $query): string
    {
        $query = array_filter($query, fn ($value) => $value !== null && $value !== '');

        return $query === [] ? '' : http_build_query($query, '', '&', PHP_QUERY_RFC3986);
    }

    protected function url(string $path, string $queryString): string
    {
        $url = rtrim($this->baseUrl(), '/') . self::BASE_PATH . $path;

        return $queryString === '' ? $url : $url . '?' . $queryString;
    }

    /**
     * @return array<string, mixed>
     */
    protected function handle(Response $response): array
    {
        $json = $response->json();
        $body = is_array($json) ? $json : [];

        if ($response->failed()) {
            throw new CardsProException(
                "CardsPro API вернул ошибку {$response->status()} для провайдера «{$this->provider->name}».",
                $response->status(),
                $body,
            );
        }

        return $body;
    }

    protected function baseUrl(): string
    {
        if (blank($this->provider->api_base_url)) {
            throw new CardsProException("У провайдера «{$this->provider->name}» не заполнен адрес технического подключения (api_base_url).");
        }

        return $this->provider->api_base_url;
    }

    protected function apiKey(): string
    {
        if (blank($this->provider->api_key)) {
            throw new CardsProException("У провайдера «{$this->provider->name}» не заполнен API Key (api_key).");
        }

        return $this->provider->api_key;
    }

    protected function secret(): string
    {
        if (blank($this->provider->api_secret)) {
            throw new CardsProException("У провайдера «{$this->provider->name}» не заполнен Secret Key (api_secret).");
        }

        return $this->provider->api_secret;
    }

    protected function timeout(): int
    {
        return (int) config('services.cardspro.timeout', 20);
    }
}
