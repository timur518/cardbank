<?php

namespace App\Models;

use App\Enums\ActiveStatus;
use App\Enums\PaymentGatewayCode;
use App\Enums\PaymentMethodType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'gateway_code',
        'sandbox_mode',
        'currency',
        'status',
        'requires_kyc',
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
            'sandbox_mode' => 'boolean',
            'status' => ActiveStatus::class,
            'requires_kyc' => 'boolean',
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

    /**
     * Пользователи, у которых этот способ оплаты явно в списке разрешённых
     * (см. User::allowedPaymentMethods()).
     */
    public function allowedForUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * Клиенты BitBanker, зарегистрированные через эту кассу BitBanker — на случай
     * нескольких касс BitBanker, как у ParityPay.
     */
    public function bitbankerClients(): HasMany
    {
        return $this->hasMany(BitbankerClient::class);
    }
}
