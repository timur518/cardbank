<?php

namespace App\Http\Controllers\Api\Webhooks;

use App\Enums\MessageProcessingStatus;
use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Models\PaymentMethodMessage;
use App\Services\Payments\PaymentGatewayContract;
use App\Services\Payments\PaymentWebhookHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Принимает вебхуки платёжной системы о смене статуса оплаты (успешно/отказано)
 * для заказов выпуска карты и пополнения (OrderController), чтобы запустить
 * фактический выпуск/пополнение карты на стороне провайдера.
 *
 * URL для настройки в личном кабинете платёжной системы:
 *   POST {APP_URL}/api/webhooks/payment/{id способа оплаты из «Способы оплаты»}
 *   ?token={значение settlement_config.webhook_secret способа оплаты, если оно задано}
 *
 * Реальный провайдер пока не подключён — работает через {@see \App\Services\Payments\StubPaymentGateway}
 * (см. привязку в AppServiceProvider). Формат тела запроса — наш собственный
 * придуманный контракт для тестирования, см. докблок StubPaymentGateway. При
 * подключении реальной платёжной системы этот контроллер менять не нужно — только
 * реализацию PaymentGatewayContract.
 *
 * Контроллер намеренно тонкий: проверяет подпись/токен, всегда сохраняет сырое тело в
 * PaymentMethodMessage и передаёт разбор события в PaymentWebhookHandler.
 */
class PaymentWebhookController extends Controller
{
    public function __invoke(Request $request, PaymentMethod $paymentMethod, PaymentGatewayContract $gateway): JsonResponse
    {
        if (! $gateway->verifyWebhookSignature($request, $paymentMethod)) {
            return response()->json(['error' => 'invalid_signature'], Response::HTTP_FORBIDDEN);
        }

        $payload = (array) $request->json()->all();
        $event = $gateway->parseWebhookPayload($payload);

        $message = PaymentMethodMessage::create([
            'payment_method_id' => $paymentMethod->id,
            'event_type' => $event['status'],
            'payload' => $payload,
            'received_at' => now(),
            'status' => MessageProcessingStatus::Pending,
        ]);

        try {
            app(PaymentWebhookHandler::class)->handle($event);
            $message->update(['status' => MessageProcessingStatus::Processed, 'processed_at' => now()]);
        } catch (Throwable $e) {
            $message->update(['status' => MessageProcessingStatus::Failed, 'processed_at' => now()]);
            report($e);

            return response()->json(['error' => 'processing_failed'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json(['status' => 'ok']);
    }
}
