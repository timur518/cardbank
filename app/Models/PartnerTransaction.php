<?php

namespace App\Models;

use App\Enums\PartnerTransactionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Реестр начислений партнёрской программы: кому (partner), за кого (buyer) и за что
 * (type/income) начислено вознаграждение, см. App\Services\Referral\ReferralService.
 * Создаётся автоматически при регистрации по реферальной ссылке и при оплате
 * приглашённым пользователем выпуска/пополнения карты.
 */
class PartnerTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'partner_user_id',
        'buyer_user_id',
        'type',
        'income_id',
        'rate',
        'commission_amount',
    ];

    protected function casts(): array
    {
        return [
            'type' => PartnerTransactionType::class,
            'rate' => 'decimal:2',
            'commission_amount' => 'decimal:2',
        ];
    }

    /**
     * Партнёр, которому начислено вознаграждение (обычный User — не обязательно
     * сейчас проходит по критерию «есть приглашённые» модели Partner).
     */
    public function partner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'partner_user_id');
    }

    /**
     * Приглашённый пользователь, чьё действие (регистрация/оплата) вызвало начисление.
     */
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_user_id');
    }

    public function income(): BelongsTo
    {
        return $this->belongsTo(Income::class);
    }
}
