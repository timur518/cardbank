<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\CardTransactionStatus;
use App\Enums\CardTransactionType;
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
     * Лента операций по всем картам клиента с фильтрами по card_id/типу/датам и
     * пагинацией — общий раздел «Транзакции» в личном кабинете.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = CardTransaction::query()
            ->whereHas('card', fn (Builder $q) => $q->where('user_id', $request->user()->id));

        if ($cardUuid = $request->string('card_id')->toString()) {
            $query->whereHas('card', fn (Builder $q) => $q->where('uuid', $cardUuid));
        }

        return $this->paginate($query, $request);
    }

    /**
     * Лента операций по конкретной карте (вкладка «История» на странице карты);
     * 403, если карта принадлежит другому пользователю.
     */
    public function forCard(Request $request, Card $card): AnonymousResourceCollection
    {
        abort_if($card->user_id !== $request->user()->id, 403);

        return $this->paginate($card->transactions(), $request);
    }

    private function paginate(Builder|\Illuminate\Database\Eloquent\Relations\HasMany $query, Request $request): AnonymousResourceCollection
    {
        $query->with(['merchantRecord', 'card.cardProduct']);

        // CardsPro иногда шлёт отдельный $0-authorization/verification по той же карте как
        // самостоятельную операцию (свой txId, свой originTxnId) перед реальной покупкой —
        // в кабинете клиента это выглядит как лишняя строка с нулевой суммой, никак не связанная
        // с реальным списанием средств. Скрываем только pending-покупки с нулевой суммой —
        // если такая холд-запись позже сливается с реальным расчётом через origin_tx_id, она
        // станет ненулевой и автоматически появится в ленте.
        $query->where(function (Builder $q) {
            $q->where('type', '!=', CardTransactionType::Purchase->value)
                ->orWhere('status', '!=', CardTransactionStatus::Pending->value)
                ->orWhere('amount', '>', 0);
        });

        // ?type=purchase или ?type[]=purchase&type[]=decline — например, для счётчика «Потрачено в
        // этом месяце» в ЛК нужны сразу и purchase, и decline (комиссия за отклонённую операцию).
        if ($type = $request->input('type')) {
            $query->whereIn('type', array_map('strval', (array) $type));
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
