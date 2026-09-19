<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\CardProductResource;
use App\Models\CardProduct;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CardProductController extends Controller
{
    /**
     * Каталог активных карточных продуктов для выбора при оформлении заявки,
     * отсортирован как в админке (поле sort).
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
