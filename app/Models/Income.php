<?php

namespace App\Models;

use App\Enums\IncomePaymentStatus;
use App\Enums\IncomeType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Income extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'type',
        'amount',
        'currency',
        'amount_usd',
        'topup_usd',
        'card_id',
        'user_id',
        'card_transaction_id',
        'payment_method_id',
        'payment_transaction_id',
        'payment_url',
        'payment_status',
        'idempotency_key',
        'comment',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'type' => IncomeType::class,
            'payment_status' => IncomePaymentStatus::class,
            'amount' => 'decimal:2',
            'amount_usd' => 'decimal:2',
            'topup_usd' => 'decimal:2',
        ];
    }

    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cardTransaction(): BelongsTo
    {
        return $this->belongsTo(CardTransaction::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
