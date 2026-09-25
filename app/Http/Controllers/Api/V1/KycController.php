<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Integrations\Didit\DiditService;
use App\Services\Integrations\Didit\Exceptions\DiditException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Запуск верификации личности из ЛК (блок «Верификация личности» на странице
 * профиля, отображается перед блоком «Мои данные») — открывает попап с iframe
 * Didit на `url` из ответа. Итоговый результат приходит асинхронно через вебхук
 * (см. DiditWebhookController), этот эндпоинт только создаёт/переиспользует сессию.
 */
class KycController extends Controller
{
    public function start(Request $request, DiditService $service): JsonResponse
    {
        try {
            $session = $service->startVerification($request->user());
        } catch (DiditException $e) {
            report($e);

            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['data' => $session]);
    }
}
