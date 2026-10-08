<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Состояние регистрации клиента в BitBanker (аналог KycVerification, но для
 * стороны BitBanker) — один клиент на пользователя. См. BitbankerClientService,
 * BITBANKER_INTEGRATION_PLAN.md раздел 4.3.
 */
class BitbankerClient extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'payment_method_id',
        'external_client_id',
        'registered_at',
        'is_verified_for_sbp',
        'check_status',
        'last_error',
        'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'registered_at' => 'datetime',
            'is_verified_for_sbp' => 'boolean',
            'last_error' => 'array',
            'last_synced_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    /**
     * Пройдены ли фоновые проверки BitBanker и разрешена ли оплата по СБП —
     * единственное условие, по которому способ оплаты BitBanker попадает/не
     * попадает в User::allowedPaymentMethods() (см. BitbankerClientService::syncAllowedPaymentMethod()).
     */
    public function isApproved(): bool
    {
        return $this->check_status === 'completed' && $this->is_verified_for_sbp;
    }
}
