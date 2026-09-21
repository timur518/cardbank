<?php

namespace App\Models;

use App\Enums\NotificationEvent;
use App\Enums\NotificationType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Локальное уведомление пользователя (лента «Уведомления» в личном кабинете).
 * Пока только in-app: запись здесь = уведомление уже показано в ЛК, `read_at`
 * фиксирует момент прочтения. Название таблицы совпадает с дефолтной таблицей
 * Laravel для канала 'database' у Notifiable — это не она: у нас своя схема и
 * свой смысл, поэтому {@see \App\Models\User::notifications()} явно переопределяет
 * связь из трейта, чтобы не было путаницы.
 *
 * Создаётся только через статический {@see notify()} из места бизнес-события
 * (вебхук-хендлер, контроллер и т.п.) — текст и категория берутся из
 * {@see \App\Enums\NotificationEvent}, не отдельными классами/файлами на каждое событие. Доставка
 * через push/email — следующий шаг, не эта модель.
 */
class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'body',
        'data',
        'action_url',
        'read_at',
    ];

    protected static function booted(): void
    {
        static::creating(function (Notification $notification): void {
            $notification->uuid ??= (string) Str::uuid();
        });
    }

    protected function casts(): array
    {
        return [
            'type' => NotificationType::class,
            'data' => 'array',
            'read_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull('read_at');
    }

    public function markAsRead(): void
    {
        if (! $this->read_at) {
            $this->update(['read_at' => now()]);
        }
    }

    /**
     * Единая точка создания уведомлений во всей системе — вызывается напрямую из места
     * события (без Event/Listener). `$user === null` безопасно игнорируется — вызывающий
     * код может передавать `$card->user` напрямую, не делая null-проверку самому.
     *
     * @param  array<string, mixed>  $params
     */
    public static function notify(?User $user, NotificationEvent $event, array $params = [], ?string $actionUrl = null): ?self
    {
        if (! $user) {
            return null;
        }

        return $user->notifications()->create([
            'type' => $event->category(),
            'title' => $event->title($params),
            'body' => $event->body($params),
            'data' => $params,
            'action_url' => $actionUrl,
        ]);
    }
}
