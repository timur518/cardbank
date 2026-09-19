<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\CardStatus;
use App\Enums\IncomePaymentStatus;
use App\Enums\IncomeType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\IssueOrderRequest;
use App\Http\Requests\Api\V1\TopupOrderRequest;
use App\Models\Card;
use App\Models\CardProduct;
use App\Models\Income;
use App\Services\CurrencyRateService;
use App\Services\Payments\PaymentGatewayContract;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Реализует шаги 1–2 из CARD_ORDER_AND_ISSUANCE_FLOW.md (расчёт и оформление заказа).
 * Обращение к платёжной системе — через PaymentGatewayContract, сейчас это
 * StubPaymentGateway (реальный провайдер ещё не подключён). Дальнейшая обработка —
 * вебхук платёжной системы (шаг 3) и вебхук CardsPro (шаги 4–5) — вне этого
 * контроллера.
 */
class OrderController extends Controller
{
    public function __construct(
        private readonly CurrencyRateService $rates,
        private readonly PaymentGatewayContract $gateway,
    ) {
        //
    }

    /**
     * POST /api/v1/orders/issue — см. CABINET_API_SPEC.md, п. 13.
     */
    public function issue(IssueOrderRequest $request): JsonResponse
    {
        $data = $request->validated();
        $idempotencyKey = $data['idempotency_key'] ?? (string) Str::uuid();

        if ($existing = Income::where('idempotency_key', $idempotencyKey)->first()) {
            return $this->issueResponse($existing->card, $existing, $idempotencyKey);
        }

        $product = CardProduct::findOrFail($data['card_product_id']);
        [$topupUsd, $topupRub] = $this->convertTopup((float) $data['topup_amount'], $data['topup_currency']);

        if ($topupUsd < (float) $product->topup_min_amount || $topupUsd > (float) $product->topup_max_amount) {
            throw ValidationException::withMessages([
                'topup_amount' => 'Сумма пополнения вне допустимых границ для этой карты.',
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

        $payment = $this->gateway->initiate($totalRub, "Заказ на выпуск карты #{$card->id}", (string) $income->id);
        $income->update(['payment_transaction_id' => $payment['transaction_id']]);

        return $this->issueResponse($card, $income, $idempotencyKey);
    }

    /**
     * POST /api/v1/orders/topup — см. CABINET_API_SPEC.md, п. 14.
     */
    public function topup(TopupOrderRequest $request): JsonResponse
    {
        $data = $request->validated();
        $idempotencyKey = $data['idempotency_key'] ?? (string) Str::uuid();

        if ($existing = Income::where('idempotency_key', $idempotencyKey)->first()) {
            return $this->topupResponse($existing, $idempotencyKey);
        }

        $card = Card::with('cardProduct')->findOrFail($data['card_id']);

        abort_if($card->user_id !== $request->user()->id, 403);
        abort_if($card->status !== CardStatus::Active, 422, 'Карта недоступна для пополнения.');

        [$topupUsd, $topupRub] = $this->convertTopup((float) $data['amount'], $data['currency']);

        $product = $card->cardProduct;

        if ($topupUsd < (float) $product->topup_min_amount || $topupUsd > (float) $product->topup_max_amount) {
            throw ValidationException::withMessages([
                'amount' => 'Сумма пополнения вне допустимых границ для этой карты.',
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

        $payment = $this->gateway->initiate($topupRub, "Пополнение карты #{$card->id}", (string) $income->id);
        $income->update(['payment_transaction_id' => $payment['transaction_id']]);

        return $this->topupResponse($income, $idempotencyKey);
    }

    /**
     * @return array{0: float, 1: float} [topup_usd, topup_rub]
     */
    private function convertTopup(float $amount, string $currency): array
    {
        $sellRateUsd = $this->rates->sellRate('usd');

        if ($currency === 'USD') {
            $topupUsd = round($amount, 2);
            $topupRub = round($topupUsd * $sellRateUsd, 2);
        } else {
            $topupRub = round($amount, 2);
            $topupUsd = $sellRateUsd > 0 ? round($topupRub / $sellRateUsd, 2) : 0.0;
        }

        return [$topupUsd, $topupRub];
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
                'payment_url' => $this->rebuildPaymentUrl($income),
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
                'payment_url' => $this->rebuildPaymentUrl($income),
                'idempotency_key' => $idempotencyKey,
            ],
        ], 201);
    }

    /**
     * Заглушка платёжной системы не хранит ссылку на оплату — при идемпотентном
     * повторе просто пересобираем её по уже сохранённому payment_transaction_id.
     */
    private function rebuildPaymentUrl(Income $income): ?string
    {
        return $income->payment_transaction_id
            ? "https://payment-gateway.example/pay/{$income->payment_transaction_id}"
            : null;
    }
}
