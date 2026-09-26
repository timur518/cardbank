<?php

namespace App\Mail;

use App\Models\User;
use App\Services\Mail\EmailBranding;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * «Вы зарегистрированы» — отправляется сразу после регистрации, см.
 * App\Http\Controllers\Api\V1\AuthController::register().
 */
class WelcomeMail extends Mailable
{
    public function __construct(public readonly User $user)
    {
        //
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Вы зарегистрированы');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.welcome',
            with: [
                'firstName' => $this->user->first_name,
                'actionUrl' => EmailBranding::cabinetUrl('/cards/new'),
            ],
        );
    }
}
