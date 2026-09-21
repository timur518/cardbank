<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\NotificationResource;
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
     * Последние уведомления пользователя + счётчик непрочитанных (по всем, а не
     * только по текущей странице) — опрашивается фронтом раз в 20 секунд для
     * бейджа на кнопке «Уведомления» в шапке ЛК.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = min((int) $request->input('per_page', 20), 50) ?: 20;

        $paginator = $request->user()->notifications()
            ->latest('created_at')
            ->paginate($perPage);

        $unreadCount = $request->user()->notifications()->unread()->count();

        return NotificationResource::collection($paginator)->additional([
            'meta' => ['unread_count' => $unreadCount],
        ]);
    }

    /**
     * Отмечает все уведомления пользователя прочитанными — вызывается фронтом
     * при открытии попапа уведомлений.
     */
    public function markRead(Request $request): JsonResponse
    {
        $request->user()->notifications()->unread()->update(['read_at' => now()]);

        return response()->json(['status' => 'ok']);
    }
}
