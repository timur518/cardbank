<?php

namespace App\Mail;

use App\Models\Card;
use App\Services\Mail\EmailBranding;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Письмо об отклонённой попытке списания по карте, см.
 * App\Services\Integrations\CardsPro\CardsProWebhookHandler::handleTransaction()
 * (ветка `status === CardTransactionStatus::Declined`) — там же создаётся
 * одноимённое по смыслу уведомление ЛК (NotificationEvent::CardPurchaseDeclined).
 *
 * Текст зависит от $insufficientFunds: при подтверждённой нехватке средств (см.
 * CardsProService::isInsufficientFundsDecline()) — прежний текст про баланс и риск блокировки;
 * иначе — нейтральный текст без упоминания баланса, с переведённой причиной ($reason), если она известна
 * (см. CardsProService::translateDeclineReason()).
 */
class InsufficientFundsMail extends Mailable
{
    public function __construct(
        public readonly Card $card,
        public readonly string $amount,
        public readonly ?string $merchant = null,
        public readonly bool $insufficientFunds = true,
        public readonly ?string $reason = null,
    ) {
        //
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->insufficientFunds ? 'Избегайте блокировки карты!' : 'Платёж по карте отклонён');
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
                'insufficientFunds' => $this->insufficientFunds,
                'reason' => $this->reason,
                'actionUrl' => EmailBranding::cabinetUrl('/cards/'.$this->card->uuid),
            ],
        );
    }
}
