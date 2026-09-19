<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\CardProductResource;
use App\Models\CardProduct;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CardProductController extends Controller
{
    /**
     * GET /api/v1/card-products — см. CABINET_API_SPEC.md, п. 11.
     */
    public function index(): AnonymousResourceCollection
    {
        $products = CardProduct::query()
            ->where('active', true)
            ->orderBy('sort')
            ->get();

        return CardProductResource::collection($products);
    }
}
