<?php

namespace App\Console\Commands\Providers\Concerns;

use App\Services\Integrations\CardsPro\Exceptions\CardsProException;
use Throwable;

/**
 * Общее форматирование ошибок для фоновых команд `providers:sync-*`: если ошибка
 * пришла от API провайдера, добавляет к сообщению тело ответа — там обычно и есть
 * настоящая причина 4xx/5xx (в отличие от голого "вернул ошибку 400").
 */
trait DescribesSyncErrors
{
    protected function describeError(Throwable $e): string
    {
        if (! $e instanceof CardsProException) {
            return $e->getMessage();
        }

        $body = $e->responseBody();

        if ($body === []) {
            return $e->getMessage();
        }

        return $e->getMessage() . ' Ответ провайдера: ' . json_encode($body, JSON_UNESCAPED_UNICODE);
    }
}
