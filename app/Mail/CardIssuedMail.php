<?php

namespace App\Mail;

use App\Models\Card;
use App\Services\Mail\EmailBranding;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * «Ваша карта готова! Можно платить» — отправляется, как только карта выпущена
 * и активна, см. App\Services\CardProviderOperationResolver::applyIssue().
 */
class CardIssuedMail extends Mailable
{
    public function __construct(public readonly Card $card)
    {
        //
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Ваша карта готова! Можно платить');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.card-issued',
            with: [
                'firstName' => $this->card->user->first_name,
                'last4' => $this->card->card_last4,
                'productName' => $this->card->cardProduct?->name,
                'actionUrl' => EmailBranding::cabinetUrl('/cards/'.$this->card->uuid),
            ],
        );
    }
}
