<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\CardTransactionResource;
use App\Models\Card;
use App\Models\CardTransaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TransactionController extends Controller
{
    /**
     * GET /api/v1/transactions — см. CABINET_API_SPEC.md, п. 17.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = CardTransaction::query()
            ->whereHas('card', fn (Builder $q) => $q->where('user_id', $request->user()->id));

        if ($cardId = $request->integer('card_id')) {
            $query->where('card_id', $cardId);
        }

        return $this->paginate($query, $request);
    }

    /**
     * GET /api/v1/cards/{card}/transactions — см. CABINET_API_SPEC.md, п. 18.
     */
    public function forCard(Request $request, Card $card): AnonymousResourceCollection
    {
        abort_if($card->user_id !== $request->user()->id, 403);

        return $this->paginate($card->transactions(), $request);
    }

    private function paginate(Builder|\Illuminate\Database\Eloquent\Relations\HasMany $query, Request $request): AnonymousResourceCollection
    {
        if ($type = $request->string('type')->toString()) {
            $query->where('type', $type);
        }

        if ($dateFrom = $request->string('date_from')->toString()) {
            $query->whereDate('occurred_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->string('date_to')->toString()) {
            $query->whereDate('occurred_at', '<=', $dateTo);
        }

        $perPage = min((int) $request->input('per_page', 15), 100) ?: 15;

        return CardTransactionResource::collection(
            $query->latest('occurred_at')->paginate($perPage)
        );
    }
}
