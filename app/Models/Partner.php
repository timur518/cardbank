<?php

namespace App\Models;

use App\Enums\PartnerStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Partner extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'code',
        'invite_link',
        'referrals_count',
        'paying_count',
        'available_usd',
        'hold_usd',
        'requested_usd',
        'paid_usd',
        'lifetime_usd',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => PartnerStatus::class,
            'available_usd' => 'decimal:2',
            'hold_usd' => 'decimal:2',
            'requested_usd' => 'decimal:2',
            'paid_usd' => 'decimal:2',
            'lifetime_usd' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payoutRequests(): HasMany
    {
        return $this->hasMany(PayoutRequest::class);
    }
}
