<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Письмо с новым паролем — см. CABINET_API_SPEC.md, п. 5
 * (POST /api/v1/auth/password/forgot).
 */
class NewPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly string $newPassword)
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Новый пароль для входа')
            ->line('Вы запросили восстановление пароля.')
            ->line("Ваш новый пароль: {$this->newPassword}")
            ->line('После входа рекомендуем сменить пароль в настройках профиля.');
    }
}
