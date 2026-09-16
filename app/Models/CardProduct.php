<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CardProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'name',
        'description',
        'skin',
        'currency',
        'provider_id',
        'provider_product_code',
        'provider_kyc_required',
        'provider_issue_cost_usd',
        'issue_min_amount',
        'issue_max_amount',
        'topup_min_amount',
        'topup_max_amount',
        'price_rub',
        'wallet_enabled',
        'wallet_activation',
        'billing_country',
        'billing_city',
        'billing_region',
        'billing_address',
        'billing_post_code',
        'active',
        'coming_soon',
        'sort',
    ];

    protected function casts(): array
    {
        return [
            'provider_kyc_required' => 'boolean',
            'provider_issue_cost_usd' => 'decimal:2',
            'issue_min_amount' => 'decimal:2',
            'issue_max_amount' => 'decimal:2',
            'topup_min_amount' => 'decimal:2',
            'topup_max_amount' => 'decimal:2',
            'price_rub' => 'decimal:2',
            'wallet_enabled' => 'boolean',
            'active' => 'boolean',
            'coming_soon' => 'boolean',
        ];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(CardProvider::class, 'provider_id');
    }

    public function cards(): HasMany
    {
        return $this->hasMany(Card::class);
    }

    /**
     * Расчётная прибыль с одного выпуска.
     * TODO: учитывать курс USD/RUB, когда появится модуль курсов валют — пока приблизительная оценка.
     */
    public function getEstimatedProfitAttribute(): string
    {
        return bcsub((string) $this->price_rub, (string) $this->provider_issue_cost_usd, 2);
    }
}
