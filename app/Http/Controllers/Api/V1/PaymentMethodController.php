<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ActiveStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\PaymentMethodResource;
use App\Models\PaymentMethod;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PaymentMethodController extends Controller
{
    /**
     * Список активных способов оплаты для выбора на шаге оплаты заказа/пополнения.
     */
    public function index(): AnonymousResourceCollection
    {
        $methods = PaymentMethod::query()
            ->where('status', ActiveStatus::Active)
            ->get();

        return PaymentMethodResource::collection($methods);
    }
}
