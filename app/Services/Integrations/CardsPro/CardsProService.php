<?php

namespace App\Services\Integrations\CardsPro;

use App\Enums\CardStatus;
use App\Enums\CardTransactionStatus;
use App\Enums\CardTransactionType;
use App\Models\CardProvider;
use App\Services\Integrations\Contracts\CardProviderIntegration;
use DateTimeInterface;
use Illuminate\Support\Str;

/**
 * Полный набор функций для работы с картами через API CardsPro: продукты, баланс
 * мастер-счёта, выпуск, пополнение, вывод средств, блокировка/заморозка, детали
 * карты и её транзакций, OTP-коды, PIN, email/телефон держателя, статусы операций.
 *
 * Используется одинаково из админки, из HTTP-контроллеров и из обработчика вебхуков
 * — просто создайте сервис для нужного провайдера и вызовите нужный метод:
 *
 *     $cardsPro = CardsProService::for($provider);
 *     $result = $cardsPro->issueCard([
 *         'productCode' => 'B0067bd6bf2b2604be25e9821b0',
 *         'amount' => 25,
 *         'currency' => 'USD',
 *     ]);
 *
 * Полное описание каждого метода и передаваемых данных — см. docs/integrations/cardspro.md.
 * Все методы бросают {@see \App\Services\Integrations\CardsPro\Exceptions\CardsProException}
 * при ошибке API или неверной настройке провайдера.
 */
class CardsProService implements CardProviderIntegration
{
    protected CardsProClient $client;

    public function __construct(protected CardProvider $provider)
    {
        $this->client = new CardsProClient($provider);
    }

    public static function for(CardProvider $provider): self
    {
        return new self($provider);
    }

    /**
     * Уникальный идентификатор запроса для issue/topup/withdraw/block (12-36 символов).
     * Используйте, если не храните свой собственный request_id для сверки.
     */
    public static function generateRequestId(): string
    {
        return (string) Str::uuid();
    }

    // ---------------------------------------------------------------------
    // Мастер-счёт и продукты
    // ---------------------------------------------------------------------

    /**
     * GET /account/balance — баланс мастер-счёта (одной валюты, одного account
     * или всех сразу, если не передавать ни одного параметра).
     *
     * @return array<int, array{total: float, currency: string}>
     */
    public function getAccountBalance(?string $currency = null, ?string $account = null): array
    {
        return $this->client->get('/account/balance', [
            'currency' => $currency,
            'account' => $account,
        ]);
    }

    /**
     * GET /products — список доступных карточных продуктов с лимитами.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getCardProducts(): array
    {
        return $this->client->get('/products');
    }

    /**
     * GET /products/search-by-merchant — ставки продуктов для конкретного мерчанта.
     *
     * @return array<int, array{productCode: string, rate: float}>
     */
    public function getCardProductRatesByMerchant(string $merchant): array
    {
        return $this->client->get('/products/search-by-merchant', ['merchant' => $merchant]);
    }

    // ---------------------------------------------------------------------
    // Жизненный цикл карты
    // ---------------------------------------------------------------------

    /**
     * POST /issue — выпуск новой карты. request_id генерируется автоматически,
     * если не передан явно.
     *
     * @param  array{
     *     productCode: string,
     *     request_id?: string,
     *     amount: float|int,
     *     currency: string,
     *     cardValidityYears?: int,
     *     preferredAccount?: string,
     *     cardName?: string,
     *     email?: string,
     *     firstName?: string,
     *     lastName?: string,
     *     kycUuid?: string,
     * }  $data
     * @return array{type: string, status: string, docid: int, request_id: string}
     */
    public function issueCard(array $data): array
    {
        $data['request_id'] ??= self::generateRequestId();

        return $this->client->post('/issue', $data);
    }

    /**
     * GET /{san}/details — детали карты (баланс, срок действия, CVV и т.д.).
     *
     * @return array<string, mixed>
     */
    public function getCardDetails(string $san): array
    {
        return $this->client->get("/{$san}/details");
    }

    /**
     * GET /list — список карт пользователя (аккаунта провайдера) с пагинацией.
     *
     * @return array{count: int, cards: array<int, array<string, mixed>>}
     */
    public function getCardList(int $limit = 20, int $offset = 0): array
    {
        return $this->client->get('/list', ['limit' => $limit, 'offset' => $offset]);
    }

    /**
     * POST /{san}/topup — пополнение карты со счёта.
     *
     * @return array{type: string, status: string, docid: int, request_id: string, topup: array<string, mixed>}
     */
    public function topUpCard(string $san, float $amount, string $currency, ?string $requestId = null): array
    {
        return $this->client->post("/{$san}/topup", [
            'amount' => $amount,
            'currency' => $currency,
            'request_id' => $requestId ?? self::generateRequestId(),
        ]);
    }

    /**
     * POST /{san}/withdraw — вывод средств с карты обратно на счёт.
     *
     * @return array{type: string, status: string, docid: int, request_id: string, withdrawal: array<string, mixed>}
     */
    public function withdrawFromCard(string $san, float $amount, string $currency, ?string $requestId = null): array
    {
        return $this->client->post("/{$san}/withdraw", [
            'request_id' => $requestId ?? self::generateRequestId(),
            'currency' => $currency,
            'amount' => $amount,
        ]);
    }

    /**
     * POST /{san}/block — безвозвратная блокировка карты.
     *
     * @return array{type: string, status: string, docid: int, request_id: string}
     */
    public function blockCard(string $san, ?string $requestId = null): array
    {
        return $this->client->post("/{$san}/block", [
            'request_id' => $requestId ?? self::generateRequestId(),
        ]);
    }

    /**
     * GET /{san}/block-detailes — сумма, возвращённая на счёт после блокировки карты.
     *
     * @return array{san: string, releasedBalance: float, currency: string}
     */
    public function getBlockedCardDetails(string $san): array
    {
        return $this->client->get("/{$san}/block-detailes");
    }

    /**
     * POST /{san}/freeze — временная заморозка карты.
     *
     * @return array{success: bool, message: string}
     */
    public function freezeCard(string $san): array
    {
        return $this->client->post("/{$san}/freeze");
    }

    /**
     * POST /{san}/unfreeze — снятие временной заморозки карты.
     *
     * @return array{success: bool, message: string}
     */
    public function unfreezeCard(string $san): array
    {
        return $this->client->post("/{$san}/unfreeze");
    }

    /**
     * POST /{san}/update-email — смена email держателя карты.
     *
     * @return array{success: bool, message: string}
     */
    public function updateCardEmail(string $san, string $email): array
    {
        return $this->client->post("/{$san}/update-email", ['email' => $email]);
    }

    /**
     * POST /{san}/update-phone — смена телефона держателя карты (не для всех продуктов).
     *
     * @return array{success: bool, message: string}
     */
    public function updateCardPhone(string $san, string $zoneNumber, string $phoneNumber): array
    {
        return $this->client->post("/{$san}/update-phone", [
            'zoneNumber' => $zoneNumber,
            'phoneNumber' => $phoneNumber,
        ]);
    }

    /**
     * POST /{san}/set-pin — установка PIN-кода карты (первый раз).
     *
     * @return array{success: bool, message: string}
     */
    public function setCardPin(string $san, string $pin): array
    {
        return $this->client->post("/{$san}/set-pin", ['pin' => $pin]);
    }

    /**
     * POST /{san}/update-pin — смена PIN-кода карты. oldPin обязателен для части
     * продуктов (см. документацию CardsPro для конкретного продукта).
     *
     * @return array{success: bool, message: string}
     */
    public function updateCardPin(string $san, string $pin, ?string $oldPin = null): array
    {
        return $this->client->post("/{$san}/update-pin", [
            'oldPin' => $oldPin,
            'pin' => $pin,
        ]);
    }

    /**
     * POST /{san}/transactions — история транзакций по карте с пагинацией и
     * фильтром по датам (ISO-8601, UTC).
     *
     * @return array{total: int, list: array<int, array<string, mixed>>}
     */
    public function getCardTransactions(string $san, int $page = 0, int $size = 20, ?string $from = null, ?string $to = null): array
    {
        return $this->client->post("/{$san}/transactions", [
            'page' => $page,
            'size' => $size,
            'from' => $from,
            'to' => $to,
        ]);
    }

    /**
     * GET /{san}/otp-codes — последние до 10 OTP(3DS) кодов карты.
     *
     * @return array<int, array{code: string, date: string}>
     */
    public function getCardOtpCodes(string $san): array
    {
        return $this->client->get("/{$san}/otp-codes");
    }

    // ---------------------------------------------------------------------
    // Статусы асинхронных операций (issue/topup/withdraw/block)
    // ---------------------------------------------------------------------

    /**
     * GET /request/status — статус операции по request_id или docid (нужен хотя бы один).
     *
     * @return array<string, mixed>
     */
    public function getRequestStatus(?string $requestId = null, ?int $docId = null): array
    {
        return $this->client->get('/request/status', [
            'request_id' => $requestId,
            'docid' => $docId,
        ]);
    }

    /**
     * GET /{san}/requests — список операций по конкретной карте с пагинацией.
     *
     * @return array{count: int, requests: array<int, array<string, mixed>>}
     */
    public function getCardRequestsList(string $san, int $limit = 20, int $offset = 0): array
    {
        return $this->client->get("/{$san}/requests", ['limit' => $limit, 'offset' => $offset]);
    }

    /**
     * GET /requests — список операций по всем картам аккаунта с пагинацией.
     *
     * @return array{count: int, requests: array<int, array<string, mixed>>}
     */
    public function getUserRequestsList(int $limit = 20, int $offset = 0): array
    {
        return $this->client->get('/requests', ['limit' => $limit, 'offset' => $offset]);
    }

    // ---------------------------------------------------------------------
    // CardProviderIntegration — нормализованный интерфейс для фоновых команд
    // синхронизации (App\Console\Commands\Providers\*). Вся разборка сырого
    // ответа CardsPro и словарь его статусов остаются здесь.
    // ---------------------------------------------------------------------

    public function fetchMasterBalanceUsd(): float
    {
        $accounts = $this->getAccountBalance();

        return collect($accounts)
            ->filter(fn (array $account) => strtoupper((string) ($account['currency'] ?? '')) === 'USD')
            ->sum(fn (array $account) => (float) ($account['total'] ?? 0));
    }

    public function fetchCardSnapshot(string $providerCardId): array
    {
        $details = $this->getCardDetails($providerCardId);

        return [
            'balance' => (float) ($details['balance'] ?? 0),
            'status' => self::mapCardStatus((string) ($details['status'] ?? '')),
            'currency' => (string) ($details['currency'] ?? ''),
            'card_number' => $details['cardNumber'] ?? null,
            'expiry' => self::formatExpiry($details),
        ];
    }

    public function fetchCardTransactions(string $providerCardId, ?DateTimeInterface $since = null): array
    {
        $transactions = [];
        $page = 0;
        $size = 100;
        // CardsPro требует миллисекунды в ISO-8601 ("2026-01-01T00:00:00.000Z") — без
        // них /{san}/transactions отвечает 400 validation "Invalid date format for
        // field 'from'".
        $from = $since ? gmdate('Y-m-d\TH:i:s', $since->getTimestamp()) . '.000Z' : null;

        do {
            $response = $this->getCardTransactions($providerCardId, $page, $size, $from);
            $batch = $response['list'] ?? [];
            $transactions = array_merge($transactions, $batch);
            $total = $response['total'] ?? count($transactions);
            $page++;
        } while ($batch !== [] && count($transactions) < $total);

        return array_map(self::normalizePolledTransaction(...), $transactions);
    }

    public function fetchProductCatalog(): array
    {
        return array_map(fn (array $product) => [
            'code' => (string) ($product['productCode'] ?? ''),
            'name' => $product['name'] ?? null,
            'currency' => $product['currency'] ?? null,
            'issue_min_amount' => isset($product['issueMinAmount']) ? (float) $product['issueMinAmount'] : null,
            'issue_max_amount' => isset($product['issueMaxAmount']) ? (float) $product['issueMaxAmount'] : null,
            'topup_min_amount' => isset($product['topUpMinAmount']) ? (float) $product['topUpMinAmount'] : null,
            'topup_max_amount' => isset($product['topUpMaxAmount']) ? (float) $product['topUpMaxAmount'] : null,
            'raw' => $product,
        ], $this->getCardProducts());
    }

    public function fetchOperationStatus(string $requestId): array
    {
        $raw = $this->getRequestStatus($requestId);

        $status = match ($raw['status'] ?? null) {
            'EXECUTED' => 'completed',
            'DECLINED' => 'failed',
            default => 'pending',
        };

        return ['status' => $status, 'raw' => $raw];
    }

    /**
     * Статус карты у CardsPro (`GET /{san}/details`) → наш {@see CardStatus}.
     */
    public static function mapCardStatus(string $cardsProStatus): CardStatus
    {
        return match ($cardsProStatus) {
            'active' => CardStatus::Active,
            'frozen' => CardStatus::Frozen,
            'blocking', 'blocked', 'expired' => CardStatus::Closed,
            default => CardStatus::Pending,
        };
    }

    /**
     * Одна операция из вебхука CARD_TRANSACTION (см. docs.cardspro.com/api/operations-callbacks) —
     * `txId`, `txType`, `billAmount`, `billCurrency`, `merchantName`, `declineReason`,
     * `txDate`, `fee`. Для ответа `GET /{san}/transactions` см. {@see normalizePolledTransaction()}
     * — там другие имена полей.
     *
     * `billAmount` — сумма до комиссии (по аналогии с `transactionValue` в `GET /{san}/transactions`,
     * где это явно задокументировано), а `fee` — отдельная комиссия, списываемая
     * с той же карты вместе с операцией. `amount` здесь — итоговая сумма, списанная с карты
     * (`billAmount + fee`), а не сам по billAmount — иначе оборот и баланс карты занижаются
     * на величину комиссии.
     *
     * @param  array<string, mixed>  $payload
     * @return array{provider_tx_id: string, type: CardTransactionType, status: CardTransactionStatus, amount: float, commission_amount: ?float, currency: string, merchant: ?string, decline_reason: ?string, occurred_at: DateTimeInterface, balance_delta: float}
     */
    public static function normalizeTransactionPayload(array $payload): array
    {
        [$type, $status, $balanceSign] = self::mapTransactionType((string) ($payload['txType'] ?? ''));
        $fee = isset($payload['fee']) ? (float) $payload['fee'] : 0.0;
        $amount = ((float) ($payload['billAmount'] ?? $payload['txAmount'] ?? 0)) + $fee;

        return [
            'provider_tx_id' => (string) ($payload['txId'] ?? ''),
            'type' => $type,
            'status' => $status,
            'amount' => $amount,
            'commission_amount' => isset($payload['fee']) ? $fee : null,
            'currency' => (string) ($payload['billCurrency'] ?? ''),
            'merchant' => $payload['merchantName'] ?? null,
            'decline_reason' => $payload['declineReason'] ?? null,
            'occurred_at' => new \DateTimeImmutable((string) ($payload['txDate'] ?? 'now')),
            'balance_delta' => $balanceSign * $amount,
        ];
    }

    /**
     * Одна операция из ответа `GET /{san}/transactions` (см. docs.cardspro.com/api/cards/card-transactions).
     * Поля здесь ДРУГИЕ, чем в вебхуке CARD_TRANSACTION (`transactionId` вместо
     * `txId`, `status` вместо `txType`, `transactionValue`/`transactionCommission`
     * вместо `billAmount`/`fee`, `cardCurrency` вместо `billCurrency`,
     * `transactionRecipient` вместо `merchantName`, `date` вместо `txDate`) — это два
     * независимых источника одних и тех же операций, а не один и тот же формат.
     *
     * `transactionValue` — сумма до комиссии, `transactionCommission` — комиссия, `transactionSum` — итоговая
     * сумма с комиссией уже готовая (так и задокументировано в API). `amount` берём из
     * `transactionSum` — это реально списанная с карты сумма, именно она должна идти в
     * оборот и в баланс карты, а не `transactionValue` без комиссии.
     *
     * @param  array<string, mixed>  $payload
     * @return array{provider_tx_id: string, type: CardTransactionType, status: CardTransactionStatus, amount: float, commission_amount: ?float, currency: string, merchant: ?string, decline_reason: ?string, occurred_at: DateTimeInterface, balance_delta: float}
     */
    public static function normalizePolledTransaction(array $payload): array
    {
        [$type, $status, $balanceSign] = self::mapTransactionType((string) ($payload['status'] ?? ''));
        $amount = (float) ($payload['transactionSum'] ?? $payload['transactionValue'] ?? $payload['txAmount'] ?? 0);

        return [
            'provider_tx_id' => (string) ($payload['transactionId'] ?? ''),
            'type' => $type,
            'status' => $status,
            'amount' => $amount,
            'commission_amount' => isset($payload['transactionCommission']) ? (float) $payload['transactionCommission'] : null,
            'currency' => (string) ($payload['cardCurrency'] ?? ''),
            'merchant' => $payload['transactionRecipient'] ?? null,
            'decline_reason' => $payload['declineReason'] ?? null,
            'occurred_at' => new \DateTimeImmutable((string) ($payload['date'] ?? 'now')),
            'balance_delta' => $balanceSign * $amount,
        ];
    }

    /**
     * Соответствие `txType` CardsPro → тип/статус транзакции в нашей базе и знак,
     * на который меняется баланс карты (0 — баланс не двигаем, это просто холд).
     *
     * @return array{0: CardTransactionType, 1: CardTransactionStatus, 2: int}
     */
    protected static function mapTransactionType(string $txType): array
    {
        return match ($txType) {
            'expense' => [CardTransactionType::Purchase, CardTransactionStatus::Success, -1],
            'authorization' => [CardTransactionType::Purchase, CardTransactionStatus::Pending, 0],
            'authorization_decline' => [CardTransactionType::Decline, CardTransactionStatus::Declined, 0],
            'reversal' => [CardTransactionType::Refund, CardTransactionStatus::Reversed, 1],
            'refund' => [CardTransactionType::Refund, CardTransactionStatus::Success, 1],
            'verification' => [CardTransactionType::Purchase, CardTransactionStatus::Pending, 0],
            'verification_decline' => [CardTransactionType::Decline, CardTransactionStatus::Declined, 0],
            'verification_expense' => [CardTransactionType::Purchase, CardTransactionStatus::Success, -1],
            'maintenance_fee' => [CardTransactionType::Fee, CardTransactionStatus::Success, -1],
            default => [CardTransactionType::Purchase, CardTransactionStatus::Pending, 0],
        };
    }

    /**
     * @param  array<string, mixed>  $details
     */
    public static function formatExpiry(array $details): ?string
    {
        $month = $details['expMonth'] ?? null;
        $year = $details['expYear'] ?? null;

        if (! $month || ! $year) {
            return null;
        }

        return sprintf('%02d/%s', $month, substr((string) $year, -2));
    }
}
