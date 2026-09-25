<?php

namespace App\Models;

use App\Enums\MessageProcessingStatus;
use App\Http\Controllers\Api\Webhooks\DiditWebhookController;
use Illuminate\Database\Eloquent\Model;

/**
 * Сырой лог одного вебхука Didit (status.updated/data.updated) — см.
 * {@see DiditWebhookController}. Тот же
 * жизненный цикл обработки (pending/processed/failed), что и у ProviderMessage
 * и PaymentMethodMessage.
 */
class DiditWebhookMessage extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'event_id',
        'webhook_type',
        'session_id',
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
}
