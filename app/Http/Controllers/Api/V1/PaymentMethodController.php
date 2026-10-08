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
     * `allowedPaymentMethods` через PaymentMethod::isAllowedFor() — BitBanker появляется
     * в списке только после попадания в него
     * (BitbankerClientService::syncAllowedPaymentMethod()), для остальных способов
     * пустой список по-прежнему означает «без ограничений».
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $methods = PaymentMethod::query()
            ->where('status', ActiveStatus::Active)
            ->get();

        $user = $request->user();
        $allowedMethods = $user?->allowedPaymentMethods()->get();

        $methods = $methods->filter(fn (PaymentMethod $method) => $method->isAllowedFor($user, $allowedMethods))->values();

        return PaymentMethodResource::collection($methods);
    }
}
