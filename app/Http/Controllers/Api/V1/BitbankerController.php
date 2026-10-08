<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ActiveStatus;
use App\Enums\PaymentGatewayCode;
use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Models\Setting;
use App\Services\Integrations\Bitbanker\BitbankerClientService;
use App\Services\Integrations\Bitbanker\Exceptions\BitbankerException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Состояние подключения BitBanker для текущего пользователя и принятие его
 * оферты из ЛК — см. BITBANKER_INTEGRATION_PLAN.md раздел 1.2/7.1. Фактическая
 * оплата через BitBanker идёт отдельно, через общий OrderController/PaymentGatewayResolver,
 * этот контроллер только про доступ к способу оплаты (а не про сами платежи).
 */
class BitbankerController extends Controller
{
    /**
     * Текущее состояние для блока выбора способа оплаты в ЛК: принята ли оферта,
     * текст оферты (для попапа принятия) и статус регистрации в BitBanker, если
     * она уже была. Фронт использует, чтобы решить, какую из плиток/кнопок
     * показать (раздел 10.2).
     */
    public function status(Request $request): JsonResponse
    {
        $user = $request->user();
        $client = $user->bitbankerClient;

        return response()->json([
            'data' => [
                'offer_accepted' => $user->bitbanker_offer_accepted_at !== null,
                'offer_text' => (string) Setting::get('bitbanker_offer_text', ''),
                'is_verified_for_sbp' => (bool) ($client?->is_verified_for_sbp ?? false),
                'check_status' => $client?->check_status,
            ],
        ]);
    }

    /**
     * Принятие оферты BitBanker из попапа в ЛК: фиксирует момент принятия
     * (идемпотентно — повторный вызов не перезаписывает уже проставленную дату)
     * и сразу регистрирует пользователя в BitBanker.
     */
    public function accept(Request $request, BitbankerClientService $service): JsonResponse
    {
        $user = $request->user();

        $paymentMethod = PaymentMethod::query()
            ->where('gateway_code', PaymentGatewayCode::Bitbanker)
            ->where('status', ActiveStatus::Active)
            ->first();

        if (! $paymentMethod) {
            return response()->json(['message' => 'Пополнение через BitBanker временно недоступно — обратитесь в поддержку.'], 422);
        }

        $user->bitbanker_offer_accepted_at ??= now();
        $user->save();

        try {
            $client = $service->register($user, $paymentMethod);
        } catch (BitbankerException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'data' => [
                'is_verified_for_sbp' => $client->is_verified_for_sbp,
                'check_status' => $client->check_status,
            ],
        ]);
    }
}
