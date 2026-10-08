<?php

namespace App\Rules;

use App\Models\Setting;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Запрещает регистрацию с email на доменах из чёрного списка — список доменов
 * администратор ведёт в админке: Настройки → Настройки уведомлений → вкладка
 * «Запрещённые email» (см. NotificationSettings::KEYS, ключ notifications_blocked_email_domains,
 * хранится в settings как JSON-массив строк).
 *
 * Используется в RegisterRequest и LandingRegisterRequest — единственных точках
 * самостоятельной регистрации пользователя на сайте.
 */
class EmailDomainNotBlocked implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $domain = strtolower((string) substr(strrchr((string) $value, '@') ?: '', 1));

        if ($domain === '') {
            return;
        }

        if (in_array($domain, self::blockedDomains(), true)) {
            $fail("Регистрация с email на домене @{$domain} запрещена.");
        }
    }

    /**
     * @return array<int, string>
     */
    public static function blockedDomains(): array
    {
        $raw = json_decode((string) Setting::get('notifications_blocked_email_domains', '[]'), true);

        return self::normalize(is_array($raw) ? $raw : []);
    }

    /**
     * Привести список доменов к однообразному виду — используется и здесь (при сравнении),
     * и в NotificationSettings::save() перед сохранением введённых в TagsInput значений.
     *
     * @param  array<int, mixed>  $domains
     * @return array<int, string>
     */
    public static function normalize(array $domains): array
    {
        return collect($domains)
            ->map(fn ($domain) => strtolower(trim((string) $domain, " \t\n\r\0\x0B@")))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
