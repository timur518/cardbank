<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Письмо с новым случайным паролем клиенту, запросившему восстановление
 * доступа в личном кабинете (AuthController::forgotPassword).
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
