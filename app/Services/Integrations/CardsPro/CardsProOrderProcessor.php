<?php

namespace App\Services\Integrations\CardsPro;

use App\Enums\CardProviderOperationStatus;
use App\Enums\CardProviderOperationType;
use App\Enums\CardTransactionStatus;
use App\Enums\CardTransactionType;
use App\Enums\NotificationEvent;
use App\Models\Card;
use App\Models\CardProvider;
use App\Models\CardProviderOperation;
use App\Models\CardTransaction;
use App\Models\Notification;
use App\Services\CardProviderOperationResolver;

/**
 * Запускает у CardsPro выпуск карты или пополнение уже выпущенной карты после того, как
 * заказ оплачен ({@see \App\Services\Payments\PaymentWebhookHandler} вызывает этот класс
 * после успешного вебхука оплаты). `Card` в обоих случаях уже существует к этому моменту.
 *
 * Если CardsPro отвечает `INPROCESS`/`EXECUTED`, заводится `CardProviderOperation` в
 * статусе `Pending`, и итог узнаётся позже — по вебхуку CardsPro (`CardsProWebhookHandler`)
 * или фоновой командой `providers:sync-pending-operations`. Если же CardsPro сразу отказывает
 * (`DECLINED`), отказ фиксируется сразу через `CardProviderOperationResolver`.
 */
class CardsProOrderProcessor
{
    public function __construct(protected CardProviderOperationResolver $resolver)
    {
        //
    }

    /**
     * Запрашивает у CardsPro выпуск новой карты с одновременным первым пополнением
     * баланса на `$topupUsd`.
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

        // Синхронный DECLINED выше уже отправил своё уведомление (CardIssueFailed, см.
        // CardProviderOperationResolver::recordDeclinedIssue()) — здесь только про case, когда заявка
        // действительно принята в обработку (INPROCESS/EXECUTED).
        if (($raw['status'] ?? null) !== 'DECLINED') {
            Notification::notify($card->user, NotificationEvent::CardOrderAccepted, [], '/cards/' . $card->uuid);
        }
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
     * Создаёт запись CardTransaction (тип Topup, статус Pending) сразу после ответа
     * CardsPro на `orders/topup`, чтобы клиент сразу видел «пополнение в обработке» в
     * истории, не дожидаясь вебхука CARD_TOPUP. `provider_tx_id` берётся из `docid`/
     * `request_id` того же ответа — по нему же дальше найдёт эту запись и переведёт её в
     * Success/Declined либо {@see \App\Services\Integrations\CardsPro\CardsProWebhookHandler::handleTopup()}
     * (вебхук), либо CardProviderOperationResolver::resolvePendingTopupTransaction() (фоновая проверка
     * providers:sync-pending-operations, если вебхук не пришёл).
     *
     * `amount` равен $topupUsd без комиссии — это точно та сумма, на которую реально
     * увеличится баланс карты (комиссию CardsPro списывает с нашего мастер-счёта, не с
     * карты), и ровно то, что видит клиент в ЛК. Комиссия хранится отдельно в
     * `commission_amount` — внутреннее поле только для админки, клиенту оно не показывается.
     * `cost_amount` здесь не заполняется, потому что весь $topupUsd целиком и есть сумма до
     * комиссии.
     *
     * @param  array<string, mixed>  $raw
     */
    protected function recordPendingTransaction(Card $card, array $raw, float $topupUsd): void
    {
        $providerTxId = isset($raw['docid']) ? (string) $raw['docid'] : (string) ($raw['request_id'] ?? '');

        if ($providerTxId === '') {
            return;
        }

        $feeUsd = $card->cardProduct?->topupCommissionUsd($topupUsd) ?? 0.0;

        CardTransaction::create([
            'card_id' => $card->id,
            'type' => CardTransactionType::Topup,
            'amount' => $topupUsd,
            'commission_amount' => $feeUsd > 0 ? $feeUsd : null,
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
                : $this->resolver->recordDeclinedTopup($card, $provider, $requestId, $docid, $raw, (float) ($payload['amount'] ?? 0));

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
