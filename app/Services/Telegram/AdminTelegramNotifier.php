<?php

namespace App\Services\Telegram;

use App\Enums\AdminTelegramEvent;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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
