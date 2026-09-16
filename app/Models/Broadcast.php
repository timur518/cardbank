<?php

namespace App\Models;

use App\Enums\BroadcastStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Broadcast extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'template_id',
        'segment_filter',
        'channels',
        'status',
        'recipients_count',
        'delivered_count',
        'started_at',
        'finished_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => BroadcastStatus::class,
            'channels' => 'array',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(NotificationTemplate::class, 'template_id');
    }
}
