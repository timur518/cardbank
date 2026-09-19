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
     * GET /api/v1/payment-methods — см. CABINET_API_SPEC.md, п. 12.
     */
    public function index(): AnonymousResourceCollection
    {
        $methods = PaymentMethod::query()
            ->where('status', ActiveStatus::Active)
            ->get();

        return PaymentMethodResource::collection($methods);
    }
}
