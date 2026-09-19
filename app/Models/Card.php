<?php

namespace App\Models;

use App\Enums\CardStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Card extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'card_product_id',
        'provider_id',
        'provider_card_id',
        'card_number',
        'expiry',
        'cvv',
        'currency',
        'balance',
        'fee_debt',
        'price_rub',
        'issue_cost_usd',
        'status',
        'billing_country',
        'billing_city',
        'billing_region',
        'billing_address',
        'billing_post_code',
        'issued_at',
        'closed_at',
        'balance_checked_at',
        'history_checked_at',
    ];

    protected $hidden = [
        'card_number',
        'expiry',
        'cvv',
    ];

    protected function casts(): array
    {
        return [
            'status' => CardStatus::class,
            'balance' => 'decimal:2',
            'fee_debt' => 'decimal:2',
            'price_rub' => 'decimal:2',
            'issue_cost_usd' => 'decimal:2',
            'issued_at' => 'datetime',
            'closed_at' => 'datetime',
            'balance_checked_at' => 'datetime',
            'history_checked_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cardProduct(): BelongsTo
    {
        return $this->belongsTo(CardProduct::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(CardProvider::class, 'provider_id');
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(CardStatusHistory::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(CardTransaction::class);
    }

    public function incomes(): HasMany
    {
        return $this->hasMany(Income::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    /**
     * Замаскированный номер карты: первые и последние 4 цифры.
     */
    public function getMaskedNumberAttribute(): string
    {
        $number = (string) $this->card_number;

        if (mb_strlen($number) < 8) {
            return '•••• ••••';
        }

        return mb_substr($number, 0, 4) . ' •••• •••• ' . mb_substr($number, -4);
    }

    /**
     * Последние 4 цифры номера карты — безопасный аксессор для API ЛК поверх скрытого
     * `card_number` (полный номер/CVV по API никогда не отдаются, см. CABINET_API_SPEC.md, п. 15).
     */
    public function getCardLast4Attribute(): ?string
    {
        $number = (string) $this->card_number;

        return mb_strlen($number) >= 4 ? mb_substr($number, -4) : null;
    }
}
