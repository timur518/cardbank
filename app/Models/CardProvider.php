<?php

namespace App\Models;

use App\Enums\ActiveStatus;
use App\Enums\ProviderEnvironment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CardProvider extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'status',
        'environment',
        'api_base_url',
        'api_key',
        'webhook_secret',
        'issue_fee_tiers',
        'topup_fee_percent',
        'extra_channel_fees',
        'min_topup_usd',
        'reserve_balance_usd',
    ];

    protected function casts(): array
    {
        return [
            'status' => ActiveStatus::class,
            'environment' => ProviderEnvironment::class,
            'issue_fee_tiers' => 'array',
            'extra_channel_fees' => 'array',
            'topup_fee_percent' => 'decimal:2',
            'min_topup_usd' => 'decimal:2',
            'reserve_balance_usd' => 'decimal:2',
        ];
    }

    protected $hidden = [
        'api_key',
        'webhook_secret',
    ];

    public function cardProducts(): HasMany
    {
        return $this->hasMany(CardProduct::class, 'provider_id');
    }

    public function cards(): HasMany
    {
        return $this->hasMany(Card::class, 'provider_id');
    }

    public function reserveTopups(): HasMany
    {
        return $this->hasMany(ProviderReserveTopup::class, 'provider_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ProviderMessage::class, 'provider_id');
    }

    public function discrepancies(): HasMany
    {
        return $this->hasMany(ProviderDiscrepancy::class, 'provider_id');
    }
}
