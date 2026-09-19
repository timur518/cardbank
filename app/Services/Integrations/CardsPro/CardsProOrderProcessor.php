<?php

namespace App\Services\Integrations\CardsPro;

use App\Enums\CardProviderOperationStatus;
use App\Enums\CardProviderOperationType;
use App\Models\Card;
use App\Models\CardProvider;
use App\Models\CardProviderOperation;
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
