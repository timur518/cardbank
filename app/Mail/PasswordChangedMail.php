<?php

namespace App\Mail;

use App\Models\User;
use App\Services\Mail\EmailBranding;
use Carbon\Carbon;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * «Пароль изменён» — отправляется, когда клиент меняет пароль в профиле, см.
 * App\Http\Controllers\Api\V1\ProfileController::updatePassword().
 */
class PasswordChangedMail extends Mailable
{
    public function __construct(
        public readonly User $user,
        public readonly Carbon $changedAt,
    ) {
        //
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Пароль изменён');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.password-changed',
            with: [
                'firstName' => $this->user->first_name,
                'datetime' => $this->changedAt->format('d.m.Y H:i'),
                'actionUrl' => EmailBranding::cabinetUrl('/profile'),
            ],
        );
    }
}
