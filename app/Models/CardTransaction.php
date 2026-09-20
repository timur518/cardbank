<?php

namespace App\Models;

use App\Enums\CardTransactionStatus;
use App\Enums\CardTransactionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Операция по карте. У каждой операции есть три независимых снэпшота суммы,
 * зафиксированных на момент операции:
 * - amount — сумма транзакции, которую видит клиент в истории платежей;
 * - cost_amount — себестоимость операции, реальная стоимость для компании;
 * - commission_amount — наша комиссия с операции, для подсчёта прибыли.
 */
class CardTransaction extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'card_id',
        'type',
        'amount',
        'cost_amount',
        'commission_amount',
        'currency',
        'merchant',
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
     * @param  array{provider_tx_id: string, origin_tx_id: ?string, type: CardTransactionType, status: CardTransactionStatus, amount: float, commission_amount: ?float, currency: string, merchant: ?string, decline_reason: ?string, occurred_at: \DateTimeInterface}  $tx
     * @return array{transaction: self, isNew: bool}
     */
    public static function upsertFromProvider(int $cardId, array $tx): array
    {
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

        $target = $existingBySameId ?? $originHold;

        $attributes = [
            'provider_tx_id' => $tx['provider_tx_id'],
            'origin_tx_id' => $tx['origin_tx_id'],
            'type' => $tx['type'],
            'amount' => $tx['amount'],
            'commission_amount' => $tx['commission_amount'],
            'currency' => $tx['currency'],
            'merchant' => $tx['merchant'],
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

    public function income(): HasOne
    {
        return $this->hasOne(Income::class);
    }
}
