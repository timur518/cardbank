<?php

namespace App\Models;

use App\Enums\DecisionStatus;
use App\Enums\KycVerificationType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KycVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'status',
        'decline_reason',
        'documents',
        'submitted_at',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => KycVerificationType::class,
            'status' => DecisionStatus::class,
            'documents' => 'array',
            'submitted_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
