<?php

namespace App\Models;

use App\Enums\CardTransactionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Рейтинг успешных платежей (0..1) карточного продукта у мерчанта — `rate`
 * синхронизируется раз в сутки из CardsPro (см. App\Console\Commands\Providers\SyncMerchantProductRates),
 * `success_count`/`decline_count` — собственная статистика по фактическим транзакциям клиентов,
 * обновляется в момент завершения каждой покупки (см. {@see self::recordOutcome()} и
 * {@see CardTransaction::upsertFromProvider()}).
 */
class MerchantProductRate extends Model
{
    protected $fillable = [
        'merchant_id',
        'card_product_id',
        'rate',
        'synced_at',
        'success_count',
        'decline_count',
    ];

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:4',
            'synced_at' => 'datetime',
        ];
    }

    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }

    public function cardProduct(): BelongsTo
    {
        return $this->belongsTo(CardProduct::class);
    }

    /**
     * Собственный рейтинг успеха (0..1) по фактическим транзакциям клиентов, в отличие
     * от `rate` — рейтинга от самого CardsPro. `null`, пока ни одной операции по этой паре
     * ещё не было.
     */
    public function getOwnRateAttribute(): ?float
    {
        $total = $this->success_count + $this->decline_count;

        return $total > 0 ? $this->success_count / $total : null;
    }

    /**
     * Фиксирует исход одной завершённой покупки по паре «мерчант × карточный продукт» —
     * вызывается из {@see CardTransaction::upsertFromProvider()} ровно один раз на каждый
     * переход транзакции в терминальный статус (защита от двойного учёта при
     * повторной доставке вебхука/опроса — на стороне вызывающего).
     */
    public static function recordOutcome(int $merchantId, int $cardProductId, CardTransactionStatus $status): void
    {
        $column = match ($status) {
            CardTransactionStatus::Success => 'success_count',
            CardTransactionStatus::Declined => 'decline_count',
            default => null,
        };

        if ($column === null) {
            return;
        }

        static::firstOrCreate(['merchant_id' => $merchantId, 'card_product_id' => $cardProductId])
            ->increment($column);
    }
}
