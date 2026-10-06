<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\NotificationResource;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Лента уведомлений в личном кабинете — сами уведомления заводятся в админке
 * (Filament-ресурс NotificationResource, App\Models\Notification), этот
 * контроллер — единственный способ, которым они «доезжают» до клиента.
 */
class NotificationController extends Controller
{
    /**
     * Последние уведомления, видимые пользователю (свои личные + все общие без
     * получателя, см. Notification::scopeVisibleTo()) + счётчик непрочитанных (по всем, а не
     * только по текущей странице) — опрашивается фронтом раз в 20 секунд для бейджа
     * на кнопке «Уведомления» в шапке ЛК.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();
        $perPage = min((int) $request->input('per_page', 20), 50) ?: 20;

        // reads фильтруется по этому user_id заранее, чтобы Notification::isReadBy()
        // ниже в ресурсе не делал отдельный запрос на каждую строку (N+1).
        $paginator = Notification::query()
            ->visibleTo($user->id)
            ->with(['reads' => fn ($query) => $query->where('user_id', $user->id)])
            ->latest('created_at')
            ->paginate($perPage);

        $unreadCount = Notification::query()
            ->visibleTo($user->id)
            ->where(function ($query) use ($user): void {
                $query->where(function ($q) {
                    $q->whereNotNull('user_id')->whereNull('read_at');
                })->orWhere(function ($q) use ($user) {
                    $q->whereNull('user_id')->whereDoesntHave('reads', fn ($r) => $r->where('user_id', $user->id));
                });
            })
            ->count();

        return NotificationResource::collection($paginator)->additional([
            'meta' => ['unread_count' => $unreadCount],
        ]);
    }

    /**
     * Отмечает все видимые пользователю уведомления (личные + общие) прочитанными —
     * вызывается фронтом при открытии попапа уведомлений.
     */
    public function markRead(Request $request): JsonResponse
    {
        $user = $request->user();

        // Личные непрочитанные — просто обновляем read_at массово.
        Notification::query()->where('user_id', $user->id)->unread()->update(['read_at' => now()]);

        // Общие непрочитанные этим пользователем — ставим отметки в notification_reads по одной на
        // каждую (markAsReadBy() idempotent — безопасно повторно для уже прочитанных).
        Notification::query()
            ->whereNull('user_id')
            ->whereDoesntHave('reads', fn ($q) => $q->where('user_id', $user->id))
            ->get()
            ->each(fn (Notification $notification) => $notification->markAsReadBy($user->id));

        return response()->json(['status' => 'ok']);
    }
}
