<?php

namespace App\Services\Integrations\ParityPay\Exceptions;

use RuntimeException;

/**
 * Ошибка при обращении к API ParityPay (https://docs.paritypay.net): неверная
 * настройка способа оплаты, сетевая ошибка или ответ с кодом 4xx/5xx (тело ответа
 * содержит `{"error": "..."}`, см. раздел «Формат ответа» документации). Содержит
 * код ответа и тело ответа (если было) для диагностики прямо из места вызова.
 */
class ParityPayException extends RuntimeException
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
