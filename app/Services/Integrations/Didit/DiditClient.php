<?php

namespace App\Services\Integrations\Didit;

use App\Models\Setting;
use App\Services\Integrations\Didit\Exceptions\DiditException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * Низкоуровневый транспорт для API Didit (https://docs.didit.me/api-reference/overview):
 * авторизация `x-api-key` и JSON-запросы на `{base_url}/v3/*`. Бизнес-логики не
 * содержит — только HTTP. Использовать через {@see DiditService}, а не напрямую.
 *
 * Учётные данные (api_key, workflow_id) хранятся в Setting, а не в .env — их
 * заполняет администратор через App\Filament\Admin\Pages\KycSettings, т.к. Didit
 * в системе один (в отличие от карточных провайдеров/платёжных систем).
 */
class DiditClient
{
    /**
     * Создать сессию верификации — POST /v3/session/. `workflow_id` подставляется
     * автоматически, остальные поля (vendor_data, callback, metadata, language) —
     * из $payload. См. https://docs.didit.me/sessions-api/create-session.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function createSession(array $payload): array
    {
        return $this->post('/v3/session/', array_merge(['workflow_id' => $this->workflowId()], $payload));
    }

    /**
     * Забрать решение по сессии — GET /v3/session/{id}/decision/. Используется только
     * для сверки/бэкфилла: основной путь получения результата — вебхук
     * (DiditWebhookHandler), см. https://docs.didit.me/sessions-api/retrieve-session.
     *
     * @return array<string, mixed>
     */
    public function getDecision(string $sessionId): array
    {
        return $this->get("/v3/session/{$sessionId}/decision/");
    }

    /**
     * @return array<string, mixed>
     */
    protected function get(string $path): array
    {
        $response = Http::withHeaders(['x-api-key' => $this->apiKey()])
            ->acceptJson()
            ->timeout($this->timeout())
            ->get($this->url($path));

        return $this->handle($response);
    }

    /**
     * @param  array<string, mixed>  $body
     * @return array<string, mixed>
     */
    protected function post(string $path, array $body): array
    {
        $response = Http::withHeaders(['x-api-key' => $this->apiKey()])
            ->acceptJson()
            ->timeout($this->timeout())
            ->post($this->url($path), array_filter($body, fn ($value) => $value !== null && $value !== ''));

        return $this->handle($response);
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
        if ($response->failed()) {
            throw new DiditException(
                "Didit API вернул ошибку {$response->status()} при обращении к {$response->effectiveUri()}.",
                $response->status(),
                (array) $response->json(),
            );
        }

        return (array) $response->json();
    }

    protected function baseUrl(): string
    {
        return (string) config('services.didit.base_url');
    }

    protected function timeout(): int
    {
        return (int) config('services.didit.timeout', 20);
    }

    public function apiKey(): string
    {
        $key = (string) Setting::get('didit_api_key', '');

        if ($key === '') {
            throw new DiditException('Не заполнен Didit API Key — см. «Комплаенс» → «Верификация (Didit)» в админке.');
        }

        return $key;
    }

    public function workflowId(): string
    {
        $workflowId = (string) Setting::get('didit_workflow_id', '');

        if ($workflowId === '') {
            throw new DiditException('Не заполнен Didit Workflow ID — см. «Комплаенс» → «Верификация (Didit)» в админке.');
        }

        return $workflowId;
    }
}
