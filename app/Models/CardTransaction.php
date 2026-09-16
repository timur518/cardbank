<?php

namespace App\Models;

use App\Enums\CardTransactionStatus;
use App\Enums\CardTransactionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CardTransaction extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'card_id',
        'type',
        'amount',
        'currency',
        'merchant',
        'status',
        'decline_reason',
        'provider_tx_id',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => CardTransactionType::class,
            'status' => CardTransactionStatus::class,
            'amount' => 'decimal:2',
            'occurred_at' => 'datetime',
        ];
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
