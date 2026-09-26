<?php

namespace App\Services\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Отправка транзакционных писем клиентам (регистрация, смена пароля, готовность
 * карты, пополнение, недостаток средств — см. app/Mail/*) с изоляцией ошибок SMTP:
 * сбой почты не должен ронять регистрацию, смену пароля или обработку вебхука
 * провайдера, поэтому исключение только логируется, а не пробрасывается дальше
 * (тот же принцип, что и у refreshCardBalance() в CardsProWebhookHandler).
 */
class SafeMailer
{
    public static function send(?string $to, Mailable $mailable): void
    {
        if (blank($to)) {
            return;
        }

        try {
            Mail::to($to)->send($mailable);
        } catch (Throwable $e) {
            Log::warning('Не удалось отправить письмо клиенту', [
                'mailable' => $mailable::class,
                'to' => $to,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
