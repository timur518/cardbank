<?php

namespace App\Services\Telegram;

use App\Enums\AdminTelegramEvent;
use App\Models\Setting;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Psr\Log\LogLevel;
use ReflectionMethod;
use Throwable;

/**
 * Отправка админ-уведомлений в Telegram (настраиваются в админке — «Настройки уведомлений»,
 * вкладка Telegram: токен бота и chat_id). Вызывается напрямую из мест события
 * ({@see AdminTelegramEvent} — одно событие = один case + один текст), без
 * Event/Listener-прослойки, по аналогии с {@see \App\Models\Notification::notify()}.
 *
 * Намеренно не бросает исключения наружу — сбой отправки в Telegram (неверный токен,
 * сеть, заблокированный бот) не должен ломать основной бизнес-процесс (регистрацию,
 * выпуск карты и т.д.), только логируется как warning.
 */
class AdminTelegramNotifier
{
    /**
     * Прокси Telegram Bot API (тот же, что и TELEGRAM_API_PROXY_URL у консультанта
     * mojno-help, см. public/mojno-help/.env.example) — у сервера приложения нет
     * прямого доступа к api.telegram.org. Контракт: POST JSON {token, method,
     * ...поля запроса}, ответ Telegram возвращается прокси без изменений.
     */
    protected const PROXY_URL = 'https://my-nimb.ru/proxy/telegram.php';

    /**
     * @param  array<string, mixed>  $params
     */
    public static function notify(AdminTelegramEvent $event, array $params = []): void
    {
        [$botToken, $chatId] = self::credentials();

        if ($botToken === null || $chatId === null) {
            return;
        }

        self::send($botToken, $chatId, $event->message($params));
    }

    /**
     * Единая точка подключения алертов об ошибках бэкенда — вызывается один раз из
     * reportable()-коллбэка в bootstrap/app.php, поэтому не требует ручной вставки в каждом
     * try/catch приложения — срабатывает на любом вызове report($e), который дошёл до
     * записи в laravel.log (Laravel по умолчанию уже отсеивает от логирования/report()
     * "ожидаемые" исключения — ValidationException, ModelNotFoundException, 404/403 HttpException,
     * TokenMismatchException и т.д., см. Handler::$internalDontReport — поэтому сюда доходят
     * только реальные баги).
     */
    public static function notifyException(Throwable $e): void
    {
        [$botToken, $chatId] = self::credentials();

        if ($botToken === null || $chatId === null) {
            return;
        }

        // Нужны только ERROR и CRITICAL — более мягкие уровни (warning/notice/info/debug) и
        // более серьёзные (alert/emergency) не шлют алерт. Уровень определяется тем же
        // механизмом, что и сам Laravel при записи в laravel.log (Handler::mapLogLevel()) — по
        // умолчанию это ERROR для любого исключения, но может быть переопределен в bootstrap/app.php
        // через \$exceptions->level().
        if (! in_array(self::resolvedLogLevel($e), [LogLevel::ERROR, LogLevel::CRITICAL], true)) {
            return;
        }

        try {
            $file = str_replace(base_path().'/', '', $e->getFile());

            $message = sprintf(
                "\u{1f534} <b>Ошибка сервера</b>\n\n<b>%s</b>\n%s\n\n\u{1f4cd} <code>%s:%d</code>%s",
                e(get_class($e)),
                e(Str::limit($e->getMessage(), 500)),
                e($file),
                $e->getLine(),
                self::requestContext(),
            );

            self::send($botToken, $chatId, $message);
        } catch (Throwable) {
            // Сборка самого алерта не должна порождать новое исключение внутри report().
        }
    }

    /**
     * Уровень лога, с которым исключение будет записано в laravel.log — через рефлексию
     * к protected Handler::mapLogLevel(), так как у него нет публичного аналога. При любой
     * ошибке рефлексии возвращает ERROR — чтобы не заглушить алерт из-за возможных
     * в будущем изменений в API Laravel.
     */
    protected static function resolvedLogLevel(Throwable $e): string
    {
        try {
            // app(ExceptionHandler::class) — тот же синглтон, что и реально обработал исключение
            // (с учётом возможных \$exceptions->level() из bootstrap/app.php) — app(Handler::class)
            // напрямую соберёт другой, ненастроенный экземпляр.
            $handler = app(ExceptionHandler::class);
            $method = new ReflectionMethod($handler, 'mapLogLevel');
            $method->setAccessible(true);

            return (string) $method->invoke($handler, $e);
        } catch (Throwable) {
            return LogLevel::ERROR;
        }
    }

    /**
     * Строка "HTTP METHOD /path" текущего запроса (если ошибка произошла внутри
     * HTTP-цикла, а не в консольной команде или очереди — там app('request') не привязан).
     */
    protected static function requestContext(): string
    {
        if (! app()->bound('request')) {
            return '';
        }

        $request = request();

        return sprintf("\n\u{1f517} <code>%s %s</code>", $request->method(), e($request->path()));
    }

    /**
     * Прямая отправка произвольного текста — используется также кнопкой «Тестовое
     * сообщение в Telegram» в настройках, где chat_id может отличаться от сохранённого.
     */
    public static function send(string $botToken, string $chatId, string $text): bool
    {
        try {
            $response = Http::timeout(10)->post(self::PROXY_URL, [
                'token' => $botToken,
                'method' => 'sendMessage',
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'HTML',
            ]);

            if (! $response->successful() || ! ($response->json('ok') ?? false)) {
                Log::warning('AdminTelegramNotifier: Telegram API вернул ошибку.', [
                    'chat_id' => $chatId,
                    'response' => $response->json(),
                ]);

                return false;
            }

            return true;
        } catch (Throwable $e) {
            Log::warning('AdminTelegramNotifier: не удалось отправить сообщение в Telegram.', [
                'chat_id' => $chatId,
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * @return array{0: ?string, 1: ?string} [botToken, chatId] — null, если хотя бы одно из
     *   значений не заполнено в настройках (тогда отправка молча пропускается).
     */
    protected static function credentials(): array
    {
        $botToken = Setting::get('notifications_bot_token');
        $chatId = Setting::get('notifications_notify_chat_id');

        return [
            blank($botToken) ? null : (string) $botToken,
            blank($chatId) ? null : (string) $chatId,
        ];
    }
}
