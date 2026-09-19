<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\CardDetailResource;
use App\Http\Resources\Api\V1\CardRequisitesResource;
use App\Http\Resources\Api\V1\CardResource;
use App\Models\Card;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CardController extends Controller
{
    /**
     * Список карт текущего клиента (активные и архивные), новые впереди.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $cards = $request->user()->cards()->with('cardProduct')->latest()->get();

        return CardResource::collection($cards);
    }

    /**
     * Карточка одной карты клиента с балансом и параметрами продукта;
     * 403, если карта принадлежит другому пользователю.
     */
    public function show(Request $request, Card $card): JsonResponse
    {
        abort_if($card->user_id !== $request->user()->id, 403);

        return (new CardDetailResource($card->load('cardProduct')))->response();
    }

    /**
     * Полный номер карты и CVV — по отдельному запросу (кнопки «Показать реквизиты»/
     * «Показать CVV» на странице карты в ЛК), не в общей карточке show(). 404, если
     * карта ещё не выпущена провайдером (card_number пуст, статус waiting/pending).
     */
    public function requisites(Request $request, Card $card): JsonResponse
    {
        abort_if($card->user_id !== $request->user()->id, 403);
        abort_if(! $card->card_number, 404, 'Реквизиты карты ещё не готовы.');

        return (new CardRequisitesResource($card))->response();
    }
}
