<?php

namespace App\Mail;

use App\Models\User;
use App\Services\Mail\EmailBranding;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Письмо с новым паролем — отправляется вместо старого App\Notifications\NewPasswordNotification,
 * см. App\Http\Controllers\Api\V1\AuthController::forgotPassword().
 */
class PasswordResetMail extends Mailable
{
    public function __construct(
        public readonly User $user,
        public readonly string $newPassword,
    ) {
        //
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Новый пароль для входа');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.password-reset',
            with: [
                'firstName' => $this->user->first_name,
                'newPassword' => $this->newPassword,
                'actionUrl' => EmailBranding::cabinetUrl('/login'),
            ],
        );
    }
}
