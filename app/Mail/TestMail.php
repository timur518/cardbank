<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Тестовое письмо для кнопки «Тестовое письмо» на странице настроек уведомлений
 * (App\Filament\Admin\Pages\NotificationSettings) — проверка текущих SMTP-настроек.
 */
class TestMail extends Mailable
{
    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Тестовое письмо');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.test');
    }
}
