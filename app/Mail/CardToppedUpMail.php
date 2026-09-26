<?php

namespace App\Mail;

use App\Models\Card;
use App\Services\Mail\EmailBranding;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * «Баланс карты пополнен! Можно платить» — отправляется при успешном пополнении,
 * см. App\Services\CardProviderOperationResolver::resolvePendingTopupTransaction()
 * и App\Services\Integrations\CardsPro\CardsProWebhookHandler::handleTopup() —
 * ровно там же, где создаётся одноимённое уведомление ЛК (NotificationEvent::TopupSuccess).
 */
class CardToppedUpMail extends Mailable
{
    public function __construct(
        public readonly Card $card,
        public readonly string $amount,
        public readonly string $balance,
    ) {
        //
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Баланс карты пополнен! Можно платить');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.card-topped-up',
            with: [
                'firstName' => $this->card->user->first_name,
                'last4' => $this->card->card_last4,
                'amount' => $this->amount,
                'balance' => $this->balance,
                'actionUrl' => EmailBranding::cabinetUrl('/cards/'.$this->card->uuid),
            ],
        );
    }
}
