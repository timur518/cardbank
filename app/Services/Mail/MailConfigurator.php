<?php

namespace App\Services\Mail;

use App\Filament\Admin\Pages\NotificationSettings;
use App\Models\Setting;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Применяет SMTP-настройки почты из админки (App\Filament\Admin\Pages\NotificationSettings,
 * вкладка Email) как активную конфигурацию Laravel-мейлера — вместо жёстко прописанных
 * MAIL_* в .env. Вызывается дважды:
 * - App\Providers\AppServiceProvider::boot() — из БД, один раз на каждый запрос/воркер;
 * - App\Filament\Admin\Pages\NotificationSettings::testEmail — из текущего (возможно, ещё
 *   не сохранённого) состояния формы, чтобы кнопка «Тестовое письмо» проверяла именно то,
 *   что сейчас введено, без перезапуска процесса и повторного чтения из БД.
 */
class MailConfigurator
{
    public static function applyFromDatabase(): void
    {
        try {
            if (! Schema::hasTable('settings')) {
                return;
            }

            static::apply(Setting::getMany(NotificationSettings::KEYS));
        } catch (Throwable) {
            // Нет смысла ронять загрузку приложения из-за недоступной БД на этапе бутстрапа.
        }
    }

    /**
     * @param  array<string, mixed>  $settings  ключи — App\Filament\Admin\Pages\NotificationSettings::KEYS
     */
    public static function apply(array $settings): void
    {
        if (blank($settings['notifications_smtp_host'] ?? null)) {
            return;
        }

        $port = (int) ($settings['notifications_smtp_port'] ?? 587);

        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => $settings['notifications_smtp_host'],
            'mail.mailers.smtp.port' => $port,
            'mail.mailers.smtp.username' => $settings['notifications_smtp_username'],
            'mail.mailers.smtp.password' => $settings['notifications_smtp_password'],
            'mail.mailers.smtp.scheme' => $port === 465 ? 'smtps' : null,
            'mail.from.address' => $settings['notifications_sender_email'] ?: config('mail.from.address'),
            'mail.from.name' => $settings['notifications_sender_name'] ?: config('mail.from.name'),
        ]);
    }
}
