<?php

namespace App\Models;

use App\Enums\CardTransactionStatus;
use App\Enums\CardTransactionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

/**
 * Операция по карте. CardsPro отдаёт по каждой операции три суммы:
 * - cost_amount — оригинальная сумма транзакции у эмитента, ДО комиссии CardsPro
 *   (billAmount у вебхука CARD_TRANSACTION / transactionValue у GET /{san}/transactions);
 * - commission_amount — комиссия CardsPro за операцию (fee / transactionCommission);
 * - amount — итоговая сумма списания с карты, cost_amount + commission_amount
 *   (billAmount+fee / transactionSum) — именно её видит клиент в истории платежей
 *   и именно она двигает баланс карты.
 */
class CardTransaction extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::creating(function (CardTransaction $transaction): void {
            $transaction->uuid ??= (string) Str::uuid();
        });
    }

    protected $fillable = [
        'card_id',
        'type',
        'amount',
        'cost_amount',
        'commission_amount',
        'currency',
        'merchant',
        'merchant_id',
        'status',
        'decline_reason',
        'provider_tx_id',
        'origin_tx_id',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => CardTransactionType::class,
            'status' => CardTransactionStatus::class,
            'amount' => 'decimal:2',
            'cost_amount' => 'decimal:2',
            'commission_amount' => 'decimal:2',
            'occurred_at' => 'datetime',
        ];
    }

    /**
     * Идемпотентно записывает операцию по карте, пришедшую от провайдера (вебхук
     * CARD_TRANSACTION или опрос GET /{san}/transactions у CardsPro — см.
     * {@see \App\Services\Integrations\CardsPro\CardsProService::normalizeTransactionPayload()}
     * и {@see \App\Services\Integrations\CardsPro\CardsProService::normalizePolledTransaction()}).
     *
     * У CardsPro авторизация (холд, `authorization`, наш {@see \App\Enums\CardTransactionStatus::Pending})
     * и её расчёт (`expense`) — одна и та же покупка под ДВУМЯ разными provider_tx_id: расчёт
     * ссылается на id холда через originTxId/originTxnId. Без учёта этой связи обычный
     * updateOrCreate по provider_tx_id заводил на такую покупку вторую строку — холд и расчёт
     * оказывались в истории как два разных дубля.
     *
     * Поэтому здесь: если провайдер уже присылал именно этот provider_tx_id — просто
     * обновляем ту же запись (защита от повторной доставки вебхука/повторного опроса, баланс
     * не двигаем повторно). Иначе, если у операции есть origin_tx_id и по нему у этой карты
     * найдётся ЕЩЁ НЕ ЗАВЕРШЁННЫЙ холд (status = Pending) — расчёт СЛИВАЕТСЯ в эту же строку
     * (включая замену provider_tx_id на новый), а не создаёт вторую. Если по origin_tx_id
     * лежит уже завершённая операция (например покупка, которую сейчас возвращают,
     * reversal/refund) — это самостоятельное новое событие, для него создаётся новая строка.
     *
     * @param  array{provider_tx_id: string, origin_tx_id: ?string, type: CardTransactionType, status: CardTransactionStatus, amount: float, cost_amount: ?float, commission_amount: ?float, currency: string, merchant: ?string, decline_reason: ?string, occurred_at: \DateTimeInterface}  $tx
     * @return array{transaction: self, isNew: bool}
     */
    public static function upsertFromProvider(int $cardId, array $tx): array
    {
        $merchantId = Merchant::matchByDescription($tx['merchant'] ?? null)?->id;

        $existingBySameId = static::where('card_id', $cardId)
            ->where('provider_tx_id', $tx['provider_tx_id'])
            ->first();

        $alreadyRecorded = $existingBySameId !== null;

        $originHold = ($alreadyRecorded || empty($tx['origin_tx_id']))
            ? null
            : static::where('card_id', $cardId)
                ->where('provider_tx_id', $tx['origin_tx_id'])
                ->where('status', CardTransactionStatus::Pending)
                ->first();

        // GET /{san}/transactions (опрос, providers:sync-card-transactions) в отличие от вебхука
        // CARD_TRANSACTION вообще не отдаёт origin-поле (см. normalizePolledTransaction()) — если
        // расчёт покупки (expense) потерял свой вебхук и его подхватывает только фоновый опрос,
        // origin_tx_id у него всегда пустой, и слить с холдом нечем: вместо обновления pending-
        // строки заводилась вторая, success, а холд навсегда оставался висеть в pending — именно
        // так выглядели дубли в проде. Раз origin_tx_id провайдер в принципе не прислал (не просто
        // "не нашли по нему холд"), ищем сам holdа по card_id + тем же cost_amount/currency среди ещё
        // не закрытых pending-покупок — это единственный доступный в этом ответе провайдера признак,
        // что это расчёт того же холда, а не новая независимая операция.
        if (! $originHold && ! $alreadyRecorded && empty($tx['origin_tx_id'])
            && $tx['status'] === CardTransactionStatus::Success
            && $tx['type'] === CardTransactionType::Purchase
            && $tx['cost_amount'] !== null
        ) {
            $originHold = static::where('card_id', $cardId)
                ->where('status', CardTransactionStatus::Pending)
                ->where('type', CardTransactionType::Purchase)
                ->where('cost_amount', $tx['cost_amount'])
                ->where('currency', $tx['currency'])
                ->orderBy('occurred_at')
                ->first();
        }

        $target = $existingBySameId ?? $originHold;

        $attributes = [
            'provider_tx_id' => $tx['provider_tx_id'],
            'origin_tx_id' => $tx['origin_tx_id'],
            'type' => $tx['type'],
            'amount' => $tx['amount'],
            'cost_amount' => $tx['cost_amount'] ?? null,
            'commission_amount' => $tx['commission_amount'],
            'currency' => $tx['currency'],
            'merchant' => $tx['merchant'],
            'merchant_id' => $merchantId,
            'status' => $tx['status'],
            'decline_reason' => $tx['decline_reason'],
            'occurred_at' => $tx['occurred_at'],
        ];

        if ($target) {
            $target->update($attributes);
            $transaction = $target;
        } else {
            $transaction = static::create($attributes + ['card_id' => $cardId]);
        }

        return ['transaction' => $transaction, 'isNew' => ! $alreadyRecorded];
    }

    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class);
    }

    /**
     * Мерчант из справочника admin/merchants (см. {@see Merchant::matchByDescription()}), если
     * он был определён при создании транзакции. Назван не `merchant()`, т.к. это имя
     * уже занято колонкой `merchant` (сырое описание операции от провайдера) —
     * при одинаковом имени Eloquent отдаёт атрибут, а не результат связи.
     */
    public function merchantRecord(): BelongsTo
    {
        return $this->belongsTo(Merchant::class, 'merchant_id');
    }

    public function income(): HasOne
    {
        return $this->hasOne(Income::class);
    }
}
