<?php

namespace App\Models;

use App\Enums\DiscrepancyStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderDiscrepancy extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_id',
        'type',
        'expected_amount',
        'actual_amount',
        'card_id',
        'status',
        'resolved_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => DiscrepancyStatus::class,
            'expected_amount' => 'decimal:2',
            'actual_amount' => 'decimal:2',
        ];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(CardProvider::class, 'provider_id');
    }

    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class);
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
