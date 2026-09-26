<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Рейтинг успешных платежей (0..1) карточного продукта у мерчанта — синхронизируется
 * раз в сутки из CardsPro, см. App\Console\Commands\Providers\SyncMerchantProductRates.
 */
class MerchantProductRate extends Model
{
    protected $fillable = [
        'merchant_id',
        'card_product_id',
        'rate',
        'synced_at',
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
}
