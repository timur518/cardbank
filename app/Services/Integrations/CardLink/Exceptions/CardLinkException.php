<?php

namespace App\Services\Integrations\CardLink\Exceptions;

use RuntimeException;

/**
 * Ошибка при обращении к API CardLink: неверная настройка способа оплаты, сетевая
 * ошибка или ответ с кодом 4xx/5xx (см. https://cardlink.link/reference/api,
 * раздел «Possible errors» каждого метода). Содержит код ответа и тело ответа
 * (если было) для диагностики прямо из места вызова.
 */
class CardLinkException extends RuntimeException
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
