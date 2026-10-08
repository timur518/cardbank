<?php

namespace App\Services\Integrations\Bitbanker;

use Illuminate\Support\Str;
use stdClass;

/**
 * Подпись `full_sign` для API BitBanker (DEV-swagger:
 * https://ext-api.dev.bitbanker.ru/docs/public/openapi, раздел «Безопасность и
 * подпись» документации):
 *
 *     full_sign = hmac_sha256(canonical_json(payload_without_sign_sign_2_full_sign), api_secret)
 *
 * canonical_json: JSON без полей `sign`/`sign_2`/`full_sign` (на любом уровне
 * вложенности), ключи объектов рекурсивно отсортированы (`sort_keys=True` в
 * терминах Python), компактные разделители (`separators=(',', ':')`), без
 * экранирования non-ASCII (`ensure_ascii=False`).
 *
 * Используется и для исходящих запросов (подписываем то, что сами отправляем —
 * {@see sign()}), и для входящих вебхуков (проверяем то, что прислал BitBanker —
 * {@see verify()}). Для входящих данных разбор JSON намеренно неассоциативный
 * (`json_decode($raw, false)`, см. {@see verify()}) — тот же приём, что и в
 * DiditWebhookHandler::verifySignature(): иначе пустой JSON-объект `{}`
 * превратится в пустой PHP-массив `[]`, и пересобранная строка перестанет
 * совпадать с тем, что подписал BitBanker.
 */
class BitbankerSigner
{
    private const EXCLUDED_KEYS = ['sign', 'sign_2', 'full_sign'];

    /**
     * Подпись исходящего запроса (тело POST либо query-параметры GET) — массив
     * уже не должен содержать `full_sign` (добавляется к нему уже после вызова).
     *
     * @param  array<string, mixed>  $payload
     */
    public function sign(array $payload, string $apiSecret): string
    {
        return hash_hmac('sha256', $this->canonicalJson($payload), $apiSecret);
    }

    /**
     * Проверка подписи входящего вебхука BitBanker — `$claimedSignature` берётся
     * из поля `full_sign` тела запроса, `$rawBody` — сырое тело запроса целиком
     * (не декодированное).
     */
    public function verify(string $rawBody, string $claimedSignature, string $apiSecret): bool
    {
        if ($apiSecret === '' || $claimedSignature === '') {
            return false;
        }

        $decoded = json_decode($rawBody, false);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return false;
        }

        $expected = hash_hmac('sha256', $this->canonicalJson($decoded), $apiSecret);

        return hash_equals($expected, $claimedSignature);
    }

    /**
     * Случайный `nonce` для защиты от повторов — требуется в каждом подписанном запросе.
     */
    public function nonce(): string
    {
        return (string) Str::uuid();
    }

    private function canonicalJson(mixed $value): string
    {
        return (string) json_encode($this->canonicalize($value), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    private function canonicalize(mixed $value): mixed
    {
        if ($value instanceof stdClass) {
            $props = get_object_vars($value);

            foreach (self::EXCLUDED_KEYS as $key) {
                unset($props[$key]);
            }

            ksort($props, SORT_STRING);
            $sorted = new stdClass;

            foreach ($props as $key => $item) {
                $sorted->{$key} = $this->canonicalize($item);
            }

            return $sorted;
        }

        if (is_array($value)) {
            if (array_is_list($value)) {
                return array_map(fn ($item) => $this->canonicalize($item), $value);
            }

            foreach (self::EXCLUDED_KEYS as $key) {
                unset($value[$key]);
            }

            ksort($value, SORT_STRING);
            $sorted = [];

            foreach ($value as $key => $item) {
                $sorted[$key] = $this->canonicalize($item);
            }

            return $sorted;
        }

        return $value;
    }
}
