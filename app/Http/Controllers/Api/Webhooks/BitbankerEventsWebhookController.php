<?php

namespace App\Http\Controllers\Api\Webhooks;

use App\Enums\MessageProcessingStatus;
use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Models\PaymentMethodMessage;
use App\Services\Integrations\Bitbanker\BitbankerEventsWebhookHandler;
use App\Services\Integrations\Bitbanker\BitbankerSigner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Events Webhook BitBanker — не про оплату (это `/webhooks/payment/{paymentMethod}`,
 * см. PaymentWebhookController), а про смену статуса клиента (`sbp_client_permission_changed`),
 * см. BITBANKER_INTEGRATION_PLAN.md раздел 7.2. URL для настройки в личном
 * кабинете BitBanker (Профиль → API):
 *
 *   POST {APP_URL}/api/webhooks/bitbanker/{id способа оплаты из «Способы оплаты»}/events
 *
 * Подпись проверяется так же, как и у v2-вебхуков BitBanker: `full_sign` лежит
 * в самом теле запроса (не в заголовке).
 */
class BitbankerEventsWebhookController extends Controller
{
    public function __invoke(Request $request, PaymentMethod $paymentMethod): JsonResponse
    {
        $payload = $request->all();
        $apiSecret = (string) ($paymentMethod->settlement_config['api_secret'] ?? '');
        $signature = (string) ($payload['full_sign'] ?? '');

        if (! (new BitbankerSigner)->verify($request->getContent(), $signature, $apiSecret)) {
            return response()->json(['error' => 'invalid_signature'], Response::HTTP_FORBIDDEN);
        }

        $message = PaymentMethodMessage::create([
            'payment_method_id' => $paymentMethod->id,
            'event_type' => (string) ($payload['event_type'] ?? 'bitbanker_client_status_changed'),
            'payload' => $payload,
            'received_at' => now(),
            'status' => MessageProcessingStatus::Pending,
        ]);

        try {
            app(BitbankerEventsWebhookHandler::class)->handle($payload);
            $message->update(['status' => MessageProcessingStatus::Processed, 'processed_at' => now()]);
        } catch (Throwable $e) {
            $message->update(['status' => MessageProcessingStatus::Failed, 'processed_at' => now()]);
            report($e);

            return response()->json(['error' => 'processing_failed'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json(['status' => 'ok']);
    }
}
