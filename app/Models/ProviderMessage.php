<?php

namespace App\Models;

use App\Enums\MessageProcessingStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderMessage extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'provider_id',
        'event_type',
        'payload',
        'received_at',
        'processed_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => MessageProcessingStatus::class,
            'payload' => 'array',
            'received_at' => 'datetime',
            'processed_at' => 'datetime',
        ];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(CardProvider::class, 'provider_id');
    }
}
