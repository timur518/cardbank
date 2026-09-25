<?php

namespace App\Http\Controllers\Api\Webhooks;

use App\Enums\MessageProcessingStatus;
use App\Http\Controllers\Controller;
use App\Models\DiditWebhookMessage;
use App\Services\Integrations\Didit\DiditWebhookHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Принимает вебхуки Didit (status.updated/data.updated) о результате верификации
 * личности — см. https://docs.didit.me/integration/webhooks. Один вебхук на всё
 * приложение (Didit в системе один, в отличие от карточных провайдеров/платёжных
 * систем — там маршрут параметризован моделью).
 *
 * URL для настройки в консоли Didit (API & Webhooks → Add destination,
 * webhook_version=v3, событие status.updated): {APP_URL}/api/webhooks/didit
 *
 * Контроллер намеренно тонкий: проверяет подпись, всегда сохраняет сырое тело в
 * DiditWebhookMessage и передаёт разбор в DiditWebhookHandler.
 */
class DiditWebhookController extends Controller
{
    public function __invoke(Request $request, DiditWebhookHandler $handler): JsonResponse
    {
        if (! $handler->verifySignature($request)) {
            return response()->json(['error' => 'invalid_signature'], Response::HTTP_FORBIDDEN);
        }

        $payload = (array) $request->json()->all();

        $message = DiditWebhookMessage::create([
            'event_id' => $payload['event_id'] ?? null,
            'webhook_type' => $payload['webhook_type'] ?? null,
            'session_id' => $payload['session_id'] ?? null,
            'payload' => $payload,
            'received_at' => now(),
            'status' => MessageProcessingStatus::Pending,
        ]);

        try {
            $handler->handle($payload);
            $message->update(['status' => MessageProcessingStatus::Processed, 'processed_at' => now()]);
        } catch (Throwable $e) {
            $message->update(['status' => MessageProcessingStatus::Failed, 'processed_at' => now()]);
            report($e);

            return response()->json(['error' => 'processing_failed'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json(['status' => 'ok']);
    }
}
