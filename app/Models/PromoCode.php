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

    /**
     * Скидка в читаемом виде — в зависимости от области действия промокода (для отображения в таблице).
     */
    public function getDiscountLabelAttribute(): string
    {
        return match ($this->scope) {
            PromoCodeScope::CardIssue => $this->fixed_discount_rub !== null
                ? number_format((float) $this->fixed_discount_rub, 0, ',', ' ') . ' ₽'
                : '—',
            PromoCodeScope::CardTopup => $this->topup_discount_percent !== null
                ? rtrim(rtrim(number_format((float) $this->topup_discount_percent, 2, ',', ' '), '0'), ',') . '%'
                : '—',
            default => '—',
        };
    }

    /**
     * Срок действия промокода в читаемом виде (для отображения в таблице).
     */
    public function getValidityLabelAttribute(): string
    {
        return match (true) {
            $this->valid_from && $this->valid_until => $this->valid_from->format('d.m.Y') . ' — ' . $this->valid_until->format('d.m.Y'),
            (bool) $this->valid_from => 'С ' . $this->valid_from->format('d.m.Y'),
            (bool) $this->valid_until => 'До ' . $this->valid_until->format('d.m.Y'),
            default => 'Бессрочно',
        };
    }
}
