<?php

namespace App\Services\Integrations\Contracts;

use App\Enums\CardStatus;
use DateTimeInterface;

/**
 * Общий контракт для фоновых команд синхронизации («Карты» → «Провайдеры карт» и
 * далее), одинаковый для любого провайдера карт. Каждый метод возвращает данные уже в
 * нашем формате (нормализованные) — вся провайдер-специфичная разборка сырого ответа
 * (структура JSON, названия полей, словарь статусов) остаётся внутри реализации
 * ({@see \App\Services\Integrations\CardsPro\CardsProService}), а команды из
 * `App\Console\Commands\Providers\*` ничего не знают о конкретном провайдере — только
 * зовут `ProviderIntegrationResolver::for($provider)` и работают с результатом.
 */
interface CardProviderIntegration
{
    /**
     * Баланс мастер-счёта провайдера в долларах — источник истины для
     * `CardProvider::reserve_balance_usd`.
     */
    public function fetchMasterBalanceUsd(): float;

    /**
     * Актуальные баланс и статус одной карты у провайдера.
     *
     * @return array{balance: float, status: CardStatus, currency: string, card_number: ?string, cvv: ?string, expiry: ?string}
     */
    public function fetchCardSnapshot(string $providerCardId): array;

    /**
     * Новые операции по карте с указанной даты (или вся доступная история, если null),
     * уже в терминах наших enum'ов и полей `CardTransaction`.
     *
     * @return array<int, array{
     *     provider_tx_id: string,
     *     origin_tx_id: ?string,
     *     type: \App\Enums\CardTransactionType,
     *     status: \App\Enums\CardTransactionStatus,
     *     amount: float,
     *     cost_amount: ?float,
     *     commission_amount: ?float,
     *     currency: string,
     *     merchant: ?string,
     *     decline_reason: ?string,
     *     occurred_at: DateTimeInterface,
     *     balance_delta: float,
     * }>
     */
    public function fetchCardTransactions(string $providerCardId, ?DateTimeInterface $since = null): array;

    /**
     * Каталог продуктов, которые провайдер сейчас готов выпускать.
     *
     * @return array<int, array{code: string, name: ?string, currency: ?string, issue_min_amount: ?float, issue_max_amount: ?float, topup_min_amount: ?float, topup_max_amount: ?float, raw: array<string, mixed>}>
     */
    public function fetchProductCatalog(): array;

    /**
     * Итог асинхронной операции (выпуск/пополнение/вывод/блокировка) по её request_id.
     *
     * @return array{status: 'pending'|'completed'|'failed', raw: array<string, mixed>}
     */
    public function fetchOperationStatus(string $requestId): array;
}
