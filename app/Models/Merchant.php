<?php

namespace App\Models;

use App\Enums\MerchantCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Справочник мерчантов — по подстроке в {@see CardTransaction::merchant} определяем,
 * какой это сервис, чтобы показать клиенту понятное название, логотип и цвет вместо
 * сырой строки эмитента вида "AUGMENT CODE           PALO ALTO     USA".
 */
class Merchant extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'category',
        'logo_svg',
        'color',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'category' => MerchantCategory::class,
            'is_active' => 'boolean',
        ];
    }
}
