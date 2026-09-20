<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\CardTransactionStatus;
use App\Enums\CardTransactionType;
use App\Enums\IncomePaymentStatus;
use App\Enums\IncomeType;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\CardTransactionResource;
use App\Models\Card;
use App\Models\CardTransaction;
use App\Models\Income;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;

class TransactionController extends Controller
{
    /**
     * Лента операций по всем картам клиента с фильтрами по card_id/типу/датам и
     * пагинацией — общий раздел «Транзакции» в личном кабинете.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $userId = $request->user()->id;

        $transactionsQuery = CardTransaction::query()
            ->whereHas('card', fn (Builder $q) => $q->where('user_id', $userId));

        $incomesQuery = Income::query()->where('user_id', $userId);

        if ($cardId = $request->integer('card_id')) {
            $transactionsQuery->where('card_id', $cardId);
            $incomesQuery->where('card_id', $cardId);
        }

        return $this->paginate($transactionsQuery, $incomesQuery, $request);
    }

    /**
     * Лента операций по конкретной карте (вкладка «История» на странице карты);
     * 403, если карта принадлежит другому пользователю.
     */
    public function forCard(Request $request, Card $card): AnonymousResourceCollection
    {
        abort_if($card->user_id !== $request->user()->id, 403);

        return $this->paginate($card->transactions(), Income::query()->where('card_id', $card->id), $request);
    }

    /**
     * Сливает реальные операции по карте (CardTransaction — покупки/комиссии/возвраты,
     * а также пополнения, если CardsPro всё-таки прислал по ним CARD_TOPUP) с
     * пополнениями из «Поступления» (Income) — их CardsPro в истории операций по
     * карте обычно не отдаёт вовсе (это оплата нашему шлюзу через собственный платёжный
     * шлюз, а не операция по самой карте). Подмешиваются только оплаченные пополнения
     * (payment_status = Paid, IncomeType::CardTopup — не выпуск карты с первым пополнением, там
     * сумма включает цену самой карты) и только если по ним ещё нет связанной
     * CardTransaction (card_transaction_id пуст) — иначе одно и то же пополнение задвоилось бы в
     * ленте, если провайдер всё же пришлёт CARD_TOPUP позже.
     *
     * Пагинация считается в памяти после выгрузки обеих наборов целиком — для объёма
     * одного клиента/одной карты это всегда небольшой объём, а единый SQL-запрос по 2
     * разным таблицам без UNION не сделать.
     */
    private function paginate(
        Builder|\Illuminate\Database\Eloquent\Relations\HasMany $transactionsQuery,
        Builder $incomesQuery,
        Request $request
    ): AnonymousResourceCollection {
        $transactionsQuery->with('merchantRecord');

        $type = $request->string('type')->toString();
        $dateFrom = $request->string('date_from')->toString();
        $dateTo = $request->string('date_to')->toString();

        if ($type !== '') {
            $transactionsQuery->where('type', $type);
        }

        if ($dateFrom !== '') {
            $transactionsQuery->whereDate('occurred_at', '>=', $dateFrom);
        }

        if ($dateTo !== '') {
            $transactionsQuery->whereDate('occurred_at', '<=', $dateTo);
        }

        $transactions = $transactionsQuery->get();

        $incomes = collect();

        // Пополнения по своей природе всегда type=topup — при фильтре по другому типу их просто не подмешиваем.
        if ($type === '' || $type === CardTransactionType::Topup->value) {
            $incomesQuery
                ->where('type', IncomeType::CardTopup)
                ->where('payment_status', IncomePaymentStatus::Paid)
                ->whereNull('card_transaction_id')
                ->with('card');

            if ($dateFrom !== '') {
                $incomesQuery->whereDate('created_at', '>=', $dateFrom);
            }

            if ($dateTo !== '') {
                $incomesQuery->whereDate('created_at', '<=', $dateTo);
            }

            $incomes = $incomesQuery->get();
        }

        $merged = $transactions
            ->concat($incomes->map(fn (Income $income) => $this->incomeAsTransaction($income)))
            ->sortByDesc(fn ($item) => $item->occurred_at)
            ->values();

        $perPage = min((int) $request->input('per_page', 15), 100) ?: 15;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();

        $paginator = new LengthAwarePaginator(
            $merged->slice(($currentPage - 1) * $perPage, $perPage)->values(),
            $merged->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()],
        );

        return CardTransactionResource::collection($paginator);
    }

    /**
     * Представляет Income как «виртуальную» CardTransaction для общей ленты — без
     * отдельной модели, только те атрибуты, которые читает CardTransactionResource. id — с
     * минусом, чтобы не пересекаться с настоящими id реальных CardTransaction.
     */
    private function incomeAsTransaction(Income $income): \stdClass
    {
        return (object) [
            'id' => -$income->id,
            'card_id' => $income->card_id,
            'type' => CardTransactionType::Topup,
            'amount' => $income->topup_usd,
            'currency' => $income->card?->currency ?? 'USD',
            'merchant' => null,
            'merchantRecord' => null,
            'status' => CardTransactionStatus::Success,
            'occurred_at' => $income->created_at,
        ];
    }
}
