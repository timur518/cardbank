<?php

namespace App\Services\Integrations\CardsPro;

use App\Enums\CardProviderOperationStatus;
use App\Enums\CardProviderOperationType;
use App\Enums\CardTransactionStatus;
use App\Enums\CardTransactionType;
use App\Models\Card;
use App\Models\CardProvider;
use App\Models\CardProviderOperation;
use App\Models\CardTransaction;
use App\Services\CardProviderOperationResolver;

/**
 * По оплаченному заказу (`Income.payment_status = Paid`, см.
 * {@see \App\Services\Payments\PaymentWebhookHandler}) инициирует у CardsPro сам
 * выпуск карты (заказ = `IncomeType::CardIssue`) либо пополнение уже
 * выпущенной карты (заказ = `IncomeType::CardTopup`, через `orders/topup`). `Card`
 * в обоих случаях уже существует к этому моменту.
 *
 * `INPROCESS`/`EXECUTED` → заводим `CardProviderOperation` в статусе `Pending`, итог
 * узнаём позже по вебхуку CardsPro (`CardsProWebhookHandler`) или страховкой
 * `providers:sync-pending-operations`. `DECLINED` сразу в ответе → синхронно
 * фиксируем отказ через `CardProviderOperationResolver`.
 */
class CardsProOrderProcessor
{
    public function __construct(protected CardProviderOperationResolver $resolver)
    {
        //
    }

    /**
     * Выпуск карты с начальным пополнением на `$topupUsd` — см. шаг 4, п. 1-3.
     */
    public function initiateIssue(Card $card, float $topupUsd): void
    {
        $product = $card->cardProduct;
        $provider = $card->provider;

        $raw = CardsProService::for($provider)->issueCard([
            'productCode' => $product->provider_product_code,
            'amount' => $topupUsd,
            'currency' => $product->currency,
        ]);

        $this->recordOperation($card, $provider, CardProviderOperationType::Issue, $raw, ['topup_usd' => $topupUsd]);
    }

    /**
     * Пополнение уже выпущенной и активной карты на `$topupUsd` (заказ `orders/topup`).
     */
    public function initiateTopup(Card $card, float $topupUsd): void
    {
        $provider = $card->provider;

        $raw = CardsProService::for($provider)->topUpCard((string) $card->provider_card_id, $topupUsd, $card->currency);

        $this->recordOperation($card, $provider, CardProviderOperationType::Topup, $raw, ['amount' => $topupUsd]);

        // Синхронный DECLINED уже зафиксирован recordOperation() выше — заводить ещё и CardTransaction нечего, час на обработку там не будет.
        if (($raw['status'] ?? null) !== 'DECLINED') {
            $this->recordPendingTransaction($card, $raw, $topupUsd);
        }
    }

    /**
     * Заводит CardTransaction(type=Topup, status=Pending) сразу после синхронного
     * INPROCESS/EXECUTED ответа CardsPro на `orders/topup`, чтобы клиент видел «пополнение в
     * обработке» в истории сразу после оплаты, а не только после вебхука CARD_TOPUP.
     * `provider_tx_id` берётся из того же `docid`/`request_id`, который вернётся в вебхуке —
     * {@see \App\Services\Integrations\CardsPro\CardsProWebhookHandler::handleTopup()} находит эту же
     * строку по нему и переводит её в Success/Declined, а не создаёт вторую.
     *
     * @param  array<string, mixed>  $raw
     */
    protected function recordPendingTransaction(Card $card, array $raw, float $topupUsd): void
    {
        $providerTxId = isset($raw['docid']) ? (string) $raw['docid'] : (string) ($raw['request_id'] ?? '');

        if ($providerTxId === '') {
            return;
        }

        CardTransaction::create([
            'card_id' => $card->id,
            'type' => CardTransactionType::Topup,
            'amount' => $topupUsd,
            'currency' => $card->currency,
            'status' => CardTransactionStatus::Pending,
            'provider_tx_id' => $providerTxId,
            'occurred_at' => now(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $raw
     * @param  array<string, mixed>  $payload
     */
    protected function recordOperation(Card $card, CardProvider $provider, CardProviderOperationType $type, array $raw, array $payload): void
    {
        $requestId = (string) ($raw['request_id'] ?? '');
        $docid = isset($raw['docid']) ? (string) $raw['docid'] : null;

        if (($raw['status'] ?? null) === 'DECLINED') {
            $type === CardProviderOperationType::Issue
                ? $this->resolver->recordDeclinedIssue($card, $provider, $requestId, $docid, $raw)
                : $this->resolver->recordDeclinedTopup($card, $provider, $requestId, $docid, $raw);

            return;
        }

        // INPROCESS/EXECUTED (или неизвестный статус в ответе заглушки провайдера) —
        // в любом случае ждём итог асинхронно, ничего дополнительно тут не решаем.
        CardProviderOperation::create([
            'provider_id' => $provider->id,
            'card_id' => $card->id,
            'type' => $type,
            'request_id' => $requestId,
            'docid' => $docid,
            'status' => CardProviderOperationStatus::Pending,
            'payload' => $payload,
        ]);
    }
}
