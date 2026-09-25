<?php

namespace App\Services\Integrations\Didit\Exceptions;

use RuntimeException;

/**
 * Ошибка при обращении к API Didit (https://docs.didit.me/api-reference/overview):
 * не заполнены учётные данные, сетевая ошибка или ответ 4xx/5xx. Auth-ошибки Didit
 * всегда возвращает как 403 (см. «Auth errors are 403, never 401» в
 * https://docs.didit.me/integration/api-full-flow), а не 401.
 */
class DiditException extends RuntimeException
{
    /**
     * @param  array<string, mixed>  $responseBody
     */
    public function __construct(string $message, protected int $statusCode = 0, protected array $responseBody = [])
    {
        parent::__construct($message);
    }

    public function statusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @return array<string, mixed>
     */
    public function responseBody(): array
    {
        return $this->responseBody;
    }
}
