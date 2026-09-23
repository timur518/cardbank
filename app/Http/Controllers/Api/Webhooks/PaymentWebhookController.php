<?php

namespace App\Http\Controllers\Api\Webhooks;

use App\Enums\MessageProcessingStatus;
use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Models\PaymentMethodMessage;
use App\Services\Payments\PaymentGatewayResolver;
use App\Services\Payments\PaymentWebhookHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Принимает вебхуки любой подключённой платёжной системы о смене статуса оплаты
 * (успешно/отказано/ошибка) для заказов выпуска карты и пополнения (OrderController),
 * чтобы запустить фактический выпуск/пополнение карты на стороне провайдера.
 *
 * Один и тот же маршрут обслуживает ЛЮБУЮ платёжную систему — конкретная интеграция
 * определяется по $paymentMethod->gateway_code через {@see PaymentGatewayResolver::for()}: подключение
 * новой платёжной системы не требует изменений этого контроллера — только новую реализацию
 * PaymentGatewayContract и ветку в резолвере.
 *
 * URL для настройки в личном кабинете платёжной системы (Result/Webhook URL):
 *   POST {APP_URL}/api/webhooks/payment/{id способа оплаты из «Способы оплаты»}
 *
 * Тело вебхука читается через Request::all() вместо Request::json() нарочно, чтобы
 * одинаково понимать и JSON (StubPaymentGateway), и form-urlencoded тело вебхука (CardLink и
 * большинство других платёжных систем) без ветвления по Content-Type.
 *
 * Контроллер намеренно тонкий: проверяет подпись/токен, всегда сохраняет сырое тело в
 * PaymentMethodMessage и передаёт разбор события в PaymentWebhookHandler.
 */
class PaymentWebhookController extends Controller
{
    public function __invoke(Request $request, PaymentMethod $paymentMethod): JsonResponse
    {
        $gateway = PaymentGatewayResolver::for($paymentMethod);

        if (! $gateway->verifyWebhookSignature($request, $paymentMethod)) {
            return response()->json(['error' => 'invalid_signature'], Response::HTTP_FORBIDDEN);
        }

        $payload = $request->all();
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
