<?php

namespace App\Models;

use App\Enums\CardNetwork;
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
        'network',
        'card_country',
        'bin',
        'provider_id',
        'provider_product_code',
        'provider_kyc_required',
        'provider_issue_cost_usd',
        'provider_topup_fee_percent',
        'successful_payment_fee_usd',
        'decline_fee_usd',
        'non_usd_payment_fee',
        'risk_operation_fee_usd',
        'three_ds_supported',
        'issue_min_amount',
        'issue_max_amount',
        'topup_min_amount',
        'topup_max_amount',
        'price_rub',
        'apple_pay_enabled',
        'google_pay_enabled',
        'wallet_activation',
        'billing_country',
        'billing_city',
        'billing_region',
        'billing_address',
        'billing_post_code',
        'restricted_merchants',
        'full_terms',
        'active',
        'coming_soon',
        'sort',
    ];

    protected function casts(): array
    {
        return [
            'network' => CardNetwork::class,
            'provider_kyc_required' => 'boolean',
            'provider_issue_cost_usd' => 'decimal:2',
            'provider_topup_fee_percent' => 'decimal:2',
            'successful_payment_fee_usd' => 'decimal:2',
            'decline_fee_usd' => 'decimal:2',
            'risk_operation_fee_usd' => 'decimal:2',
            'three_ds_supported' => 'boolean',
            'issue_min_amount' => 'decimal:2',
            'issue_max_amount' => 'decimal:2',
            'topup_min_amount' => 'decimal:2',
            'topup_max_amount' => 'decimal:2',
            'price_rub' => 'decimal:2',
            'apple_pay_enabled' => 'boolean',
            'google_pay_enabled' => 'boolean',
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
