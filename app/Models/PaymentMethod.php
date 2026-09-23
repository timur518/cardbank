<?php

namespace App\Models;

use App\Enums\ActiveStatus;
use App\Enums\PaymentGatewayCode;
use App\Enums\PaymentMethodType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'gateway_code',
        'currency',
        'status',
        'fee_percent',
        'min_amount',
        'max_amount',
        'settlement_config',
    ];

    protected function casts(): array
    {
        return [
            'type' => PaymentMethodType::class,
            'gateway_code' => PaymentGatewayCode::class,
            'status' => ActiveStatus::class,
            'settlement_config' => 'array',
            'fee_percent' => 'decimal:2',
            'min_amount' => 'decimal:2',
            'max_amount' => 'decimal:2',
        ];
    }

    public function messages(): HasMany
    {
        return $this->hasMany(PaymentMethodMessage::class);
    }

    public function usages(): HasMany
    {
        return $this->hasMany(PaymentMethodUsage::class);
    }
}
