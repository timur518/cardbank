<?php

namespace App\Models;

use App\Enums\PromoCodeScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PromoCode extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'code',
        'scope',
        'fixed_discount_rub',
        'topup_discount_percent',
        'allowed_product_ids',
        'single_use',
        'valid_from',
        'valid_until',
        'max_uses',
        'used_count',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'scope' => PromoCodeScope::class,
            'fixed_discount_rub' => 'decimal:2',
            'topup_discount_percent' => 'decimal:2',
            'allowed_product_ids' => 'array',
            'single_use' => 'boolean',
            'valid_from' => 'datetime',
            'valid_until' => 'datetime',
            'active' => 'boolean',
        ];
    }

    public function usages(): HasMany
    {
        return $this->hasMany(PromoCodeUsage::class);
    }

    /**
     * Ограничение по продуктам в виде читаемого списка названий (для отображения в таблице).
     */
    public function getAllowedProductsLabelAttribute(): string
    {
        if (blank($this->allowed_product_ids)) {
            return 'На все продукты';
        }

        return CardProduct::query()
            ->whereIn('id', $this->allowed_product_ids)
            ->pluck('name')
            ->implode(', ');
    }
}
