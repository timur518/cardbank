<?php

namespace App\Mail;

use App\Models\Card;
use App\Services\Mail\EmailBranding;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * «Избегайте блокировки карты!» — отправляется при отклонённой попытке списания
 * из-за нехватки средств на балансе карты, см.
 * App\Services\Integrations\CardsPro\CardsProWebhookHandler::handleTransaction()
 * (ветка `status === CardTransactionStatus::Declined`) — там же создаётся
 * одноимённое по смыслу уведомление ЛК «Недостаточно средств»
 * (NotificationEvent::CardPurchaseDeclined).
 */
class InsufficientFundsMail extends Mailable
{
    public function __construct(
        public readonly Card $card,
        public readonly string $amount,
        public readonly ?string $merchant = null,
    ) {
        //
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Избегайте блокировки карты!');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.insufficient-funds',
            with: [
                'firstName' => $this->card->user->first_name,
                'last4' => $this->card->card_last4,
                'amount' => $this->amount,
                'merchant' => $this->merchant,
                'actionUrl' => EmailBranding::cabinetUrl('/cards/'.$this->card->uuid),
            ],
        );
    }
}
