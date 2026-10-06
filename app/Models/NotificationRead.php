<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Факт прочтения общего уведомления (Notification::user_id === null) конкретным
 * пользователем — по одной строке на пару (уведомление, пользователь). Для личных
 * уведомлений (user_id заполнен) прочтение хранится проще — прямо в
 * Notification::read_at, эта таблица для них не используется.
 */
class NotificationRead extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'notification_id',
        'user_id',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    public function notification(): BelongsTo
    {
        return $this->belongsTo(Notification::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
