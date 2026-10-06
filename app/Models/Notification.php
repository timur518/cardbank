<?php

namespace App\Models;

use App\Enums\NotificationEvent;
use App\Enums\NotificationType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * Локальное уведомление (лента «Уведомления» в личном кабинете). Пока только
 * in-app: запись здесь = уведомление уже показано в ЛК. Название таблицы совпадает
 * с дефолтной таблицей Laravel для канала 'database' у Notifiable — это не она: у
 * нас своя схема и свой смысл, поэтому {@see \App\Models\User::notifications()}
 * явно переопределяет связь из трейта, чтобы не было путаницы.
 *
 * Два вида уведомлений по `user_id`:
 * - Личное (`user_id` заполнен) — видно только этому пользователю, прочтение —
 *   просто `read_at` в этой же строке (как и раньше).
 * - Общее / рассылка (`user_id === null`) — видно всем пользователям одной и
 *   той же строкой. `read_at` тут не подходит — он один на всех, прочтение одним
 *   пользователем пометило бы его прочитанным для всех остальных. Вместо этого
 *   прочтение хранится построчно на каждого пользователя в {@see NotificationRead}.
 *
 * Создаётся через статический {@see notify()} (личное) или {@see notifyAll()}
 * (общее, см. Filament-ресурс Notifications) из места бизнес-события — текст и
 * категория личных берутся из {@see \App\Enums\NotificationEvent}, общие задаются
 * напрямую администратором в форме.
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

    public function reads(): HasMany
    {
        return $this->hasMany(NotificationRead::class);
    }

    /**
     * Личные непрочитанные (user_id заполнен) — для общих уведомлений этот скоуп не
     * подходит (см. {@see scopeVisibleTo()} и проверку прочтения через NotificationRead
     * в NotificationController).
     */
    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull('read_at');
    }

    /**
     * Уведомления, видимые пользователю: свои личные (user_id = $userId) + все общие
     * (user_id IS NULL, рассылки). Используется вместо `$user->notifications()` там,
     * где нужна объединённая лента (API в ЛК), а не только личные записи.
     */
    public function scopeVisibleTo(Builder $query, int $userId): Builder
    {
        return $query->where(function (Builder $q) use ($userId): void {
            $q->where('user_id', $userId)->orWhereNull('user_id');
        });
    }

    /**
     * Прочитано ли это уведомление данным пользователем: для личного — просто read_at,
     * для общего — наличие строки в notification_reads для этой пары (уведомление,
     * пользователь). Ожидает, что `reads` предварительно загружена фильтром по этому
     * user_id (см. NotificationController::index()), чтобы не делать запрос на каждую
     * строку.
     */
    public function isReadBy(int $userId): bool
    {
        if ($this->user_id !== null) {
            return $this->user_id === $userId && $this->read_at !== null;
        }

        return $this->relationLoaded('reads')
            ? $this->reads->isNotEmpty()
            : $this->reads()->where('user_id', $userId)->exists();
    }

    /**
     * Отмечает уведомление прочитанным конкретным пользователем — для личного
     * уведомления обновляет read_at в самой строке, для общего — добавляет строку в
     * notification_reads (идемпотентно за счёт уникального индекса notification_id+user_id).
     */
    public function markAsReadBy(int $userId): void
    {
        if ($this->user_id !== null) {
            if ($this->user_id === $userId && ! $this->read_at) {
                $this->update(['read_at' => now()]);
            }

            return;
        }

        NotificationRead::query()->firstOrCreate(
            ['notification_id' => $this->id, 'user_id' => $userId],
            ['read_at' => now()],
        );
    }

    /**
     * @deprecated Сохранена для обратной совместимости с личными уведомлениями —
     * используйте {@see markAsReadBy()}, который корректно работает и с общими
     * уведомлениями.
     */
    public function markAsRead(): void
    {
        if (! $this->read_at) {
            $this->update(['read_at' => now()]);
        }
    }

    /**
     * Единая точка создания личных уведомлений во всей системе — вызывается напрямую
     * из места события (без Event/Listener). `$user === null` безопасно игнорируется —
     * вызывающий код может передавать $card->user напрямую, не делая null-проверку
     * самому. Для общих уведомлений см. {@see notifyAll()} — она создаётся не из
     * бизнес-события, а вручную из формы в админке (NotificationResource), поэтому
     * там нет привязки к NotificationEvent.
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

    /**
     * Создаёт одно общее уведомление без получателя (user_id = null) — видно сразу
     * всем пользователям в ленте ЛК (см. {@see scopeVisibleTo()}), без нужды заводить
     * отдельную запись на каждого пользователя. Вызывается из Filament-формы в админке
     * (текст вводит администратор напрямую, без NotificationEvent).
     */
    public static function notifyAll(string $type, string $title, ?string $body = null, ?string $actionUrl = null): self
    {
        return static::create([
            'user_id' => null,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'action_url' => $actionUrl,
        ]);
    }
}
