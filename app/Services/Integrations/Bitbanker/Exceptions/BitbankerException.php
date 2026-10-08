<?php

namespace App\Services\Integrations\Bitbanker\Exceptions;

use RuntimeException;

/**
 * Ошибка при обращении к API BitBanker (DEV-swagger:
 * https://ext-api.dev.bitbanker.ru/docs/public/openapi): неверная настройка
 * способа оплаты, сетевая ошибка или ответ с кодом 4xx/5xx. У реального API нет
 * именованных кодов бизнес-ошибок (проверено по swagger — `BadRequestParametersError.code`
 * содержит единственное значение `BadRequestParameters`), поэтому тело ответа
 * сохраняется целиком для разбора оператором (см. BitbankerClient::class).
 */
class BitbankerException extends RuntimeException
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
