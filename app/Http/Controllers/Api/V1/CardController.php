<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\CardResource;
use App\Models\Card;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CardController extends Controller
{
    /**
     * GET /api/v1/cards — см. CABINET_API_SPEC.md, п. 15.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $cards = $request->user()->cards()->with('cardProduct')->latest()->get();

        return CardResource::collection($cards);
    }

    /**
     * GET /api/v1/cards/{card} — см. CABINET_API_SPEC.md, п. 16.
     */
    public function show(Request $request, Card $card): JsonResponse
    {
        abort_if($card->user_id !== $request->user()->id, 403);

        return (new CardResource($card->load('cardProduct')))->response();
    }
}
