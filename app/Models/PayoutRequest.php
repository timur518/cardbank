<?php

namespace App\Models;

use App\Enums\PayoutDestination;
use App\Enums\PayoutRequestStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayoutRequest extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'partner_id',
        'amount_usd',
        'destination',
        'bank_card_number',
        'bank_card_holder',
        'bank_name',
        'status',
        'admin_note',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'destination' => PayoutDestination::class,
            'status' => PayoutRequestStatus::class,
            'amount_usd' => 'decimal:2',
            'resolved_at' => 'datetime',
        ];
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }
}
