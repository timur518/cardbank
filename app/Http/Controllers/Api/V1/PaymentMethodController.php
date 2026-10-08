<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ActiveStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\PaymentMethodResource;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PaymentMethodController extends Controller
{
    /**
     * Список активных способов оплаты для выбора на шаге оплаты заказа/пополнения.
     * Маршрут публичный (доступен и гостю — до входа в ЛК), но `statefulApi()`
     * (см. bootstrap/app.php) резолвит `$request->user()` и для гостевых cookie-сессий,
     * поэтому для залогиненного пользователя список дополнительно фильтруется по
     * `allowedPaymentMethods` (см. BITBANKER_INTEGRATION_PLAN.md раздел 6.4): пустой
     * список означает «без ограничений» (показываются все активные способы, как и для
     * гостя), непустой — пересекаем с ним. BitBanker появляется в списке ровно тогда,
     * когда попадает в этот список (BitbankerClientService::syncAllowedPaymentMethod()).
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $methods = PaymentMethod::query()
            ->where('status', ActiveStatus::Active)
            ->get();

        $user = $request->user();

        if ($user && $user->allowedPaymentMethods()->exists()) {
            $allowedIds = $user->allowedPaymentMethods()->pluck('payment_methods.id');
            $methods = $methods->whereIn('id', $allowedIds)->values();
        }

        return PaymentMethodResource::collection($methods);
    }
}
