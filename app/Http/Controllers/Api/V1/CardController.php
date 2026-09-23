<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\CardStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\CardDetailResource;
use App\Http\Resources\Api\V1\CardRequisitesResource;
use App\Http\Resources\Api\V1\CardResource;
use App\Models\Card;
use App\Services\Integrations\ProviderIntegrationResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Log;
use Throwable;

class CardController extends Controller
{
    /**
     * Список карт текущего клиента (активные и архивные), новые впереди.
     * Карты со статусом "Отменён" и "Ошибка выпуска" в ЛК не показываем —
     * это неудавшиеся выпуски, клиенту в них смотреть незачем.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $cards = $request->user()->cards()
            ->with('cardProduct')
            ->whereNotIn('status', [CardStatus::Cancelled, CardStatus::Failed])
            ->latest()
            ->get();

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

    /**
     * OTP(3DS)-коды по карте (вкладка «3DS коды» на странице карты) — живой запрос к
     * провайдеру на каждое открытие вкладки/кнопку «Обновить» — коды одноразовые и
     * короткоживущие, отдельного хранилища в нашей базе нет. Карта без provider_card_id или
     * недоступный провайдер молча возвращают пустой список — фронтенд показывает ту же
     * заглушку «кодов пока не было», что и при действительном отсутствии кодов.
     */
    public function otpCodes(Request $request, Card $card): JsonResponse
    {
        abort_if($card->user_id !== $request->user()->id, 403);

        $codes = [];

        if ($card->provider_card_id) {
            try {
                $codes = ProviderIntegrationResolver::for($card->provider)->fetchOtpCodes($card->provider_card_id);
            } catch (Throwable $e) {
                Log::warning('Не удалось получить OTP-коды карты у провайдера', [
                    'card_id' => $card->id,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        $data = collect($codes)
            ->sortByDesc(fn (array $entry) => $entry['occurred_at'])
            ->values()
            ->map(fn (array $entry) => [
                'code' => $entry['code'],
                'occurred_at' => $entry['occurred_at']->toIso8601String(),
            ]);

        return response()->json(['data' => $data]);
    }
}
