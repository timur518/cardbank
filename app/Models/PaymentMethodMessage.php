<?php

namespace App\Models;

use App\Enums\MessageProcessingStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentMethodMessage extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'payment_method_id',
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

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }
}
