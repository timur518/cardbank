<?php

namespace App\Services\Payments;

use App\Enums\AdminTelegramEvent;
use App\Enums\CardStatus;
use App\Enums\CardTransactionStatus;
use App\Enums\CardTransactionType;
use App\Enums\NotificationEvent;
use App\Mail\CardIssuedMail;
use App\Mail\CardToppedUpMail;
use App\Models\Card;
use App\Models\CardTransaction;
use App\Models\Notification;
use App\Services\Integrations\CardsPro\CardsProOrderProcessor;
use App\Services\Mail\SafeMailer;
use App\Services\Telegram\AdminTelegramNotifier;
use Illuminate\Support\Str;

/**
 * Аналог {@see CardsProOrderProcessor} для способов
 * оплаты с `PaymentMethod.sandbox_mode = true`: вызывается из
 * {@see PaymentWebhookHandler::handlePaid()} вместо настоящей
 * интеграции с провайдером — оплата принимается и обрабатывается полностью реально (Income,
 * вебхуки, уведомления, e-mail), но к CardsPro (или любому другому провайдеру) ни один запрос
 * не уходит. Вместо реальных реквизитов карты и пополнения баланса через провайдера сюда
 * сохраняется случайный синтетический набор данных — чтобы можно было гонять весь путь
 * приёма платежа (LK → PaymentGatewayContract → вебхук → выпуск/пополнение → уведомления)
 * без реального выпуска карт и трат на мастер-счету у провайдера.
 *
 * `provider_card_id` синтетических карт всегда начинается с `SANDBOX-` — по этому префиксу
 * их легко отличить в БД, а флаг `Card.is_sandbox` исключает их из опроса реального
 * провайдера фоновыми командами (`providers:sync-card-balances`, `providers:sync-card-transactions`,
 * см. Card::refreshBalanceFromProvider()).
 */
class SandboxOrderProcessor
{
    /**
     * Вместо запроса CardsPro `issueCard()` сразу активирует карту со случайными
     * реквизитами и балансом, равным сумме пополнения при выпуске.
     */
    public function initiateIssue(Card $card, float $topupUsd): void
    {
        $card->update([
            'provider_card_id' => $this->sandboxId(),
            'card_number' => $this->randomCardNumber($card->cardProduct?->bin),
            'cvv' => (string) random_int(100, 999),
            'expiry' => now()->addYears(3)->format('m/y'),
            'balance' => $topupUsd,
            'status' => CardStatus::Active,
            'is_sandbox' => true,
            'issued_at' => now(),
        ]);

        Notification::notify($card->user, NotificationEvent::CardIssued, ['last4' => $card->card_last4], '/cards/'.$card->uuid);
        SafeMailer::send($card->user->email, new CardIssuedMail($card));
        AdminTelegramNotifier::notify(AdminTelegramEvent::CardIssued, [
            'name' => $card->user->name,
            'email' => $card->user->email,
            'card_product' => $card->cardProduct?->name,
            'last4' => $card->card_last4,
        ]);
    }

    /**
     * Вместо запроса CardsPro `topUpCard()` сразу зачисляет `$topupUsd` на баланс карты
     * (в отличие от боевого пути — намеренно локальным `increment()`, а не
     * `refreshBalanceFromProvider()`: у песочной карты нет настоящего провайдера, у которого
     * можно перезапросить актуальный баланс) и заводит уже успешную операцию в истории.
     */
    public function initiateTopup(Card $card, float $topupUsd): void
    {
        $card->increment('balance', $topupUsd);
        $card->refresh();

        CardTransaction::create([
            'card_id' => $card->id,
            'type' => CardTransactionType::Topup,
            'amount' => $topupUsd,
            'currency' => $card->currency,
            'status' => CardTransactionStatus::Success,
            'provider_tx_id' => $this->sandboxId(),
            'occurred_at' => now(),
        ]);

        $amount = NotificationEvent::money($topupUsd, $card->currency);
        $balance = NotificationEvent::money($card->balance, $card->currency);

        Notification::notify($card->user, NotificationEvent::TopupSuccess, [
            'last4' => $card->card_last4,
            'amount' => $amount,
            'balance' => $balance,
        ], '/cards/'.$card->uuid);
        SafeMailer::send($card->user->email, new CardToppedUpMail($card, $amount, $balance));
    }

    protected function sandboxId(): string
    {
        return 'SANDBOX-'.Str::upper(Str::random(20));
    }

    /**
     * 16 цифр: первые 6 — BIN карточного продукта, если он задан (правдоподобный номер),
     * иначе случайный тестовый диапазон; остальное — случайные цифры.
     */
    protected function randomCardNumber(?string $bin): string
    {
        $prefix = $bin && strlen($bin) >= 6 ? substr($bin, 0, 6) : (string) random_int(400000, 499999);

        $suffix = '';

        for ($i = 0; $i < 10; $i++) {
            $suffix .= (string) random_int(0, 9);
        }

        return $prefix.$suffix;
    }
}
