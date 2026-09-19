<?php

namespace App\Http\Controllers\Api\Webhooks;

use App\Enums\CardsProCallbackType;
use App\Enums\MessageProcessingStatus;
use App\Http\Controllers\Controller;
use App\Models\CardProvider;
use App\Models\ProviderMessage;
use App\Services\Integrations\CardsPro\CardsProWebhookHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Принимает колбэки CardsPro о событиях по картам/транзакциям (выпуск, пополнение,
 * списание и т.п.) и применяет их к состоянию карт через CardsProWebhookHandler.
 *
 * URL для настройки в личном кабинете CardsPro:
 *   POST {APP_URL}/api/webhooks/cardspro/{код провайдера из «Карты» → «Провайдеры карт»}
 *   ?token={значение webhook_secret этого провайдера, если оно задано}
 *
 * Контроллер намеренно тонкий: он только проверяет токен, всегда сохраняет сырое
 * тело в ProviderMessage и передаёт разбор события в CardsProWebhookHandler —
 * этот же обработчик можно переиспользовать, например, из консольной команды
 * повторной обработки зависших сообщений.
 */
class CardsProWebhookController extends Controller
{
    public function __invoke(Request $request, CardProvider $provider): JsonResponse
    {
        if (! $this->tokenIsValid($request, $provider)) {
            return response()->json(['error' => 'invalid_token'], Response::HTTP_FORBIDDEN);
        }

        $rawType = (string) $request->header('X-CP-Callback-Type', '');
        $payload = (array) $request->json()->all();

        $message = ProviderMessage::create([
            'provider_id' => $provider->id,
            'event_type' => $rawType !== '' ? $rawType : 'UNKNOWN',
            'payload' => $payload,
            'received_at' => now(),
            'status' => MessageProcessingStatus::Pending,
        ]);

        $callbackType = CardsProCallbackType::tryFrom($rawType);

        if ($callbackType === null) {
            $message->update(['status' => MessageProcessingStatus::Processed, 'processed_at' => now()]);

            return response()->json(['status' => 'ignored_unknown_type']);
        }

        try {
            (new CardsProWebhookHandler($provider))->handle($callbackType, $payload);
            $message->update(['status' => MessageProcessingStatus::Processed, 'processed_at' => now()]);
        } catch (Throwable $e) {
            $message->update(['status' => MessageProcessingStatus::Failed, 'processed_at' => now()]);
            report($e);

            return response()->json(['error' => 'processing_failed'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json(['status' => 'ok']);
    }

    /**
     * У CardsPro нет задокументированной подписи входящих колбэков, поэтому если у
     * провайдера заполнен webhook_secret — требуем его в query-параметре ?token=.
     * Если webhook_secret пуст, проверка не проводится (обратная совместимость).
     */
    protected function tokenIsValid(Request $request, CardProvider $provider): bool
    {
        if (blank($provider->webhook_secret)) {
            return true;
        }

        return hash_equals((string) $provider->webhook_secret, (string) $request->query('token'));
    }
}
