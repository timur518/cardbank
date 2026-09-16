<?php

namespace App\Models;

use App\Enums\RiskFlagType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiskFlag extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'comment',
        'created_by',
        'removed_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => RiskFlagType::class,
            'removed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
