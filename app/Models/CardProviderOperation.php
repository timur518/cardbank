<?php

namespace App\Models;

use App\Enums\CardProviderOperationStatus;
use App\Enums\CardProviderOperationType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Асинхронная операция, которую мы инициировали у провайдера карт (выпуск, пополнение,
 * вывод, блокировка) и статус которой ещё не подтверждён окончательно. `payload` хранит
 * то, что провайдер сам не помнит о нас — например, для выпуска карты это будущий
 * владелец и карточный продукт, без которых нельзя завести Card по одному только
 * ответу провайдера. Закрывается либо вебхуком, либо
 * {@see \App\Console\Commands\Providers\SyncPendingOperations} как страховкой на случай
 * потерянного вебхука.
 */
class CardProviderOperation extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'provider_id',
        'card_id',
        'type',
        'request_id',
        'docid',
        'status',
        'payload',
        'result',
        'error',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => CardProviderOperationType::class,
            'status' => CardProviderOperationStatus::class,
            'payload' => 'array',
            'result' => 'array',
            'resolved_at' => 'datetime',
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
}
