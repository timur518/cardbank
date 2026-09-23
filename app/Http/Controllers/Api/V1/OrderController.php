<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\CardStatus;
use App\Enums\IncomePaymentStatus;
use App\Enums\IncomeType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\IssueOrderRequest;
use App\Http\Requests\Api\V1\TopupOrderRequest;
use App\Http\Requests\Api\V1\TopupQuoteRequest;
use App\Models\Card;
use App\Models\CardProduct;
use App\Models\Income;
use App\Models\PaymentMethod;
use App\Services\CurrencyRateService;
use App\Services\Payments\PaymentGatewayResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Оформление заказов на выпуск карты и пополнение баланса: создаёт
 * записи Card и Income со статусом ожидания оплаты и инициирует платёж через
 * шлюз, выбранный клиентом в payment_method_id ({@see PaymentGatewayResolver::for()}). Фактический
 * выпуск/пополнение карты на стороне провайдера происходит уже после подтверждения
 * оплаты вебхуками (PaymentWebhookController, затем CardsProWebhookController) — вне этого контроллера.
 */
class OrderController extends Controller
{
    public function __construct(
        private readonly CurrencyRateService $rates,
    ) {
        //
    }

    /**
     * Оформляет заказ на выпуск новой карты с одновременным первым пополнением
     * баланса: создаёт карту в статусе Waiting и одну запись Income на сумму
     * «цена карты + пополнение», идемпотентно (повторный запрос с тем же
     * idempotency_key вернёт ранее созданный заказ, не создав дубль).
     */
    public function issue(IssueOrderRequest $request): JsonResponse
    {
        $data = $request->validated();
        $idempotencyKey = $data['idempotency_key'] ?? (string) Str::uuid();

        if ($existing = Income::where('idempotency_key', $idempotencyKey)->first()) {
            return $this->issueResponse($existing->card, $existing, $idempotencyKey);
        }

        $product = CardProduct::findOrFail($data['card_product_id']);
        [$topupUsd, $topupRub] = $this->convertTopup((float) $data['topup_amount'], $data['topup_currency'], $product);

        if ($topupUsd < (float) $product->topup_min_amount || $topupUsd > (float) $product->topup_max_amount) {
            throw ValidationException::withMessages([
                'topup_amount' => 'Сумма пополнения некорректна. Проверьте условия пополнения.',
            ]);
        }

        $totalRub = round((float) $product->price_rub + $topupRub, 2);
        $rawRateUsd = $this->rates->rawRate('usd');
        $userId = $request->user()->id;

        [$card, $income] = DB::transaction(function () use ($product, $data, $topupUsd, $totalRub, $rawRateUsd, $idempotencyKey, $userId) {
            $card = Card::create([
                'user_id' => $userId,
                'card_product_id' => $product->id,
                'provider_id' => $product->provider_id,
                'currency' => $product->currency,
                'status' => CardStatus::Waiting,
                'price_rub' => $product->price_rub,
                'issue_cost_usd' => $product->provider_issue_cost_usd,
                'billing_country' => $product->billing_country,
                'billing_city' => $product->billing_city,
                'billing_region' => $product->billing_region,
                'billing_address' => $product->billing_address,
                'billing_post_code' => $product->billing_post_code,
            ]);

            $income = Income::create([
                'type' => IncomeType::CardIssue,
                'amount' => $totalRub,
                'currency' => 'RUB',
                'amount_usd' => $rawRateUsd > 0 ? round($totalRub / $rawRateUsd, 2) : 0,
                'topup_usd' => $topupUsd,
                'card_id' => $card->id,
                'user_id' => $userId,
                'payment_method_id' => $data['payment_method_id'],
                'payment_status' => IncomePaymentStatus::Pending,
                'comment' => 'Выпуск карты и пополнение баланса',
                'idempotency_key' => $idempotencyKey,
            ]);

            return [$card, $income];
        });

        $gateway = PaymentGatewayResolver::for(PaymentMethod::findOrFail($data['payment_method_id']));
        $payment = $gateway->initiate($totalRub, "Заказ на выпуск карты #{$card->id}", (string) $income->id);
        $income->update(['payment_transaction_id' => $payment['transaction_id'], 'payment_url' => $payment['payment_url']]);

        return $this->issueResponse($card, $income, $idempotencyKey);
    }

    /**
     * Оформляет пополнение уже активной карты клиента: создаёт Income и инициирует
     * оплату через PaymentGatewayResolver::for(); идемпотентно, как и issue().
     */
    public function topup(TopupOrderRequest $request): JsonResponse
    {
        $data = $request->validated();
        $idempotencyKey = $data['idempotency_key'] ?? (string) Str::uuid();

        if ($existing = Income::where('idempotency_key', $idempotencyKey)->first()) {
            return $this->topupResponse($existing, $idempotencyKey);
        }

        $card = Card::with('cardProduct')->where('uuid', $data['card_id'])->firstOrFail();

        abort_if($card->user_id !== $request->user()->id, 403);
        abort_if($card->status !== CardStatus::Active, 422, 'Карта недоступна для пополнения.');

        $product = $card->cardProduct;
        [$topupUsd, $topupRub] = $this->convertTopup((float) $data['amount'], $data['currency'], $product);

        if ($topupUsd < (float) $product->topup_min_amount || $topupUsd > (float) $product->topup_max_amount) {
            throw ValidationException::withMessages([
                'amount' => 'Сумма пополнения некорректна. Проверьте условия пополнения.',
            ]);
        }

        $rawRateUsd = $this->rates->rawRate('usd');

        $income = Income::create([
            'type' => IncomeType::CardTopup,
            'amount' => $topupRub,
            'currency' => 'RUB',
            'amount_usd' => $rawRateUsd > 0 ? round($topupRub / $rawRateUsd, 2) : 0,
            'topup_usd' => $topupUsd,
            'card_id' => $card->id,
            'user_id' => $request->user()->id,
            'payment_method_id' => $data['payment_method_id'],
            'payment_status' => IncomePaymentStatus::Pending,
            'comment' => 'Пополнение карты',
            'idempotency_key' => $idempotencyKey,
        ]);

        $gateway = PaymentGatewayResolver::for(PaymentMethod::findOrFail($data['payment_method_id']));
        $payment = $gateway->initiate($topupRub, "Пополнение карты #{$card->id}", (string) $income->id);
        $income->update(['payment_transaction_id' => $payment['transaction_id'], 'payment_url' => $payment['payment_url']]);

        return $this->topupResponse($income, $idempotencyKey);
    }

    /**
     * Переводит введённую клиентом сумму пополнения (в рублях или долларах) в долларовую сумму,
     * которая реально попадёт на карту, и в итоговую сумму к оплате в рублях. Итоговая сумма
     * включает комиссию CardsPro за пополнение (`CardProduct.provider_topup_fee_percent`), поэтому
     * клиент оплачивает ровно такую сумму, какая реально списывается с нашего мастер-счёта у
     * провайдера. Сама сумма, которая уйдёт в `topupCard()`/`issueCard()` и попадёт на карту,
     * от комиссии не зависит — меняется только то, что оплачивает клиент.
     *
     * @return array{0: float, 1: float} [topup_usd, total_rub]
     */
    private function convertTopup(float $amount, string $currency, CardProduct $product): array
    {
        $sellRateUsd = $this->rates->sellRate('usd');

        $topupUsd = $currency === 'USD'
            ? round($amount, 2)
            : ($sellRateUsd > 0 ? round($amount / $sellRateUsd, 2) : 0.0);

        $feeUsd = $product->topupCommissionUsd($topupUsd);
        $totalRub = round(($topupUsd + $feeUsd) * $sellRateUsd, 2);

        return [$topupUsd, $totalRub];
    }

    /**
     * Рассчитывает сумму к оплате без создания заказа — используется для живого превью суммы
     * на фронте (TopupModal, NewCardOrderPage), пересчитывается по мере ввода суммы. Использует
     * ту же формулу convertTopup(), что и сами заказы, поэтому показанная сумма всегда совпадает
     * с тем, что будет списано при оформлении заказа. Комиссию (`provider_topup_fee_percent`)
     * отдельной строкой не показываем — это внутренняя себестоимость, как и у остальных
     * `provider_*` полей CardProduct, клиенту она не показывается.
     */
    public function quote(TopupQuoteRequest $request): JsonResponse
    {
        $data = $request->validated();

        if (! empty($data['card_id'])) {
            $card = Card::with('cardProduct')->where('uuid', $data['card_id'])->firstOrFail();
            abort_if($card->user_id !== $request->user()->id, 403);
            $product = $card->cardProduct;
        } else {
            $product = CardProduct::findOrFail($data['card_product_id']);
        }

        [$topupUsd, $totalRub] = $this->convertTopup((float) $data['amount'], $data['currency'], $product);

        return response()->json([
            'data' => [
                'topup_usd' => number_format($topupUsd, 2, '.', ''),
                'topup_total_rub' => number_format($totalRub, 2, '.', ''),
            ],
        ]);
    }

    private function issueResponse(Card $card, Income $income, string $idempotencyKey): JsonResponse
    {
        $topupRub = round((float) $income->amount - (float) $card->price_rub, 2);

        return response()->json([
            'data' => [
                'card_id' => $card->id,
                'status' => $card->status->value,
                'price_rub' => number_format((float) $card->price_rub, 2, '.', ''),
                'topup_usd' => number_format((float) $income->topup_usd, 2, '.', ''),
                'topup_rub' => number_format($topupRub, 2, '.', ''),
                'total_rub' => number_format((float) $income->amount, 2, '.', ''),
                'payment_transaction_id' => $income->payment_transaction_id,
                'payment_url' => $income->payment_url,
                'idempotency_key' => $idempotencyKey,
            ],
        ], 201);
    }

    private function topupResponse(Income $income, string $idempotencyKey): JsonResponse
    {
        return response()->json([
            'data' => [
                'card_id' => $income->card_id,
                'topup_usd' => number_format((float) $income->topup_usd, 2, '.', ''),
                'topup_rub' => number_format((float) $income->amount, 2, '.', ''),
                'total_rub' => number_format((float) $income->amount, 2, '.', ''),
                'payment_transaction_id' => $income->payment_transaction_id,
                'payment_url' => $income->payment_url,
                'idempotency_key' => $idempotencyKey,
            ],
        ], 201);
    }
}
