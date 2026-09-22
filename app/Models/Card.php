<?php

namespace App\Models;

use App\Enums\CardStatus;
use App\Services\Integrations\ProviderIntegrationResolver;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Card extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::creating(function (Card $card): void {
            $card->uuid ??= (string) Str::uuid();
        });
    }

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

//    protected $hidden = [
//        'card_number',
//        'expiry',
//        'cvv',
//    ];

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

    /**
     * Единственный способ, которым `balance` должен меняться. Намеренно не считаем
     * баланс локально дельтой операций (increment/decrement по sign/type вебхука) — любая
     * ошибка в знаке/типе операции или неучтённая нами комиссия провайдера
     * тихо и навсегда расходится с реальным балансом у провайдера. Вместо этого после
     * любого события, двигающего деньги на карте (вебхук или резервный опрос),
     * перезапрашиваем у провайдера актуальный баланс и просто ставим его как есть
     * (тот же `fetchCardSnapshot()`, что и в `providers:sync-card-balances`).
     */
    public function refreshBalanceFromProvider(): void
    {
        if (! $this->provider_card_id) {
            return;
        }

        $snapshot = ProviderIntegrationResolver::for($this->provider)->fetchCardSnapshot($this->provider_card_id);

        $this->update([
            'balance' => $snapshot['balance'],
            'balance_checked_at' => now(),
        ]);
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
     * Последние 4 цифры номера карты — безопасный аксессор для API личного кабинета поверх
     * скрытого `card_number` (полный номер/CVV по API никогда не отдаются). Нулл, если
     * номер ещё не присвоен провайдером (карта в статусе waiting/pending).
     */
    public function getCardLast4Attribute(): ?string
    {
        $number = (string) $this->card_number;

        return mb_strlen($number) >= 4 ? mb_substr($number, -4) : null;
    }
}
