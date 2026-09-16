<?php

namespace App\Services\Integrations\CardsPro\Exceptions;

use RuntimeException;

/**
 * Ошибка при обращении к API CardsPro: неверная настройка провайдера, сетевая
 * ошибка или ответ с кодом 4xx/5xx. Содержит код ответа и тело ответа (если было)
 * для диагностики прямо из места вызова.
 */
class CardsProException extends RuntimeException
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
