<?php

namespace App\Models;

use App\Enums\CardCountry;
use App\Enums\CardNetwork;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CardProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'name',
        'description',
        'skin',
        'currency',
        'network',
        'card_country',
        'bin',
        'provider_id',
        'provider_product_code',
        'provider_kyc_required',
        'provider_issue_cost_usd',
        'provider_topup_fee_percent',
        'successful_payment_fee_usd',
        'decline_fee_usd',
        'non_usd_payment_fee',
        'risk_operation_fee_usd',
        'three_ds_supported',
        'issue_min_amount',
        'issue_max_amount',
        'topup_min_amount',
        'topup_max_amount',
        'price_rub',
        'apple_pay_enabled',
        'google_pay_enabled',
        'wallet_activation',
        'billing_country',
        'billing_city',
        'billing_region',
        'billing_address',
        'billing_post_code',
        'restricted_merchants',
        'full_terms',
        'active',
        'coming_soon',
        'sort',
    ];

    protected function casts(): array
    {
        return [
            'network' => CardNetwork::class,
            'card_country' => CardCountry::class,
            'provider_kyc_required' => 'boolean',
            'provider_issue_cost_usd' => 'decimal:2',
            'provider_topup_fee_percent' => 'decimal:2',
            'successful_payment_fee_usd' => 'decimal:2',
            'decline_fee_usd' => 'decimal:2',
            'risk_operation_fee_usd' => 'decimal:2',
            'three_ds_supported' => 'boolean',
            'issue_min_amount' => 'decimal:2',
            'issue_max_amount' => 'decimal:2',
            'topup_min_amount' => 'decimal:2',
            'topup_max_amount' => 'decimal:2',
            'price_rub' => 'decimal:2',
            'apple_pay_enabled' => 'boolean',
            'google_pay_enabled' => 'boolean',
            'active' => 'boolean',
            'coming_soon' => 'boolean',
        ];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(CardProvider::class, 'provider_id');
    }

    public function cards(): HasMany
    {
        return $this->hasMany(Card::class);
    }

    /**
     * Комиссия CardsPro за пополнение (`provider_topup_fee_percent`) в долларах от суммы
     * пополнения без комиссии — одина формула для OrderController::convertTopup() (что платит
     * клиент), CardProviderOperationResolver::recordTopupExpense() (внутренний расход банка) и
     * CardTransaction.commission_amount (что видно в админке по конкретной транзакции) — без этого
     * хелпера три места считали ёё независимо друг от друга, и в некоторых из них она вообще забывалась.
     */
    public function topupCommissionUsd(float $topupUsd): float
    {
        return round($topupUsd * (float) $this->provider_topup_fee_percent / 100, 2);
    }

    /**
     * Сумма типового пополнения при продаже карты (Setting::calc_topup_amount_usd) и комиссия за пополнение
     * карты (Setting::calc_card_topup_fee_percent) — оба значения берутся из блока «Калькулятор» страницы
     * «Валютная система», а не из полей самого карточного продукта.
     */
    protected static function topupWithFeeUsd(): float
    {
        $topupUsd = (float) (Setting::get('calc_topup_amount_usd') ?? 0);
        $feePercent = (float) (Setting::get('calc_card_topup_fee_percent') ?? 0);

        return $topupUsd * (1 + $feePercent / 100);
    }

    /**
     * Сырой курс ЦБ, без наценки — по нему считаются фактические расходы банка в долларах (стоимость
     * выпуска и пополнения у провайдера) — мы платим их без наценки, она есть только в том, что мы
     * берём с клиента.
     */
    public static function rawRateUsd(): float
    {
        return (float) (Setting::get('currency_rate_usd') ?? 0);
    }

    /**
     * Курс USD с нашей наценкой (курс ЦБ × (1 + наценка%)) — по нему клиент фактически платит за
     * доллары пополнения, см. CurrencySettings::calculatorSellRateUsd().
     */
    public static function sellRateUsd(): float
    {
        $markup = (float) (Setting::get('currency_markup_usd_percent') ?? 0);

        return self::rawRateUsd() * (1 + $markup / 100);
    }

    /**
     * Общая сумма к оплате клиентом при покупке карты с типовым пополнением: цена продажи (price_rub)
     * плюс сумма пополнения с комиссией, купленная у нас по курсу с наценкой (сколько клиент
     * реально платит за весь заказ, см. OrderController::convertTopup()).
     */
    public function getTotalPayableRubAttribute(): string
    {
        return number_format((float) $this->price_rub + self::topupWithFeeUsd() * self::sellRateUsd(), 2, '.', '');
    }

    /**
     * Фактические расходы банка в рублях: стоимость выпуска у провайдера плюс сумма пополнения
     * с комиссией, переведённые по сырому курсу (без наценки, потому что это наши
     * реальные валютные затраты, а не то, что мы берём с клиента).
     */
    public function getExpensesRubAttribute(): string
    {
        $expensesUsd = (float) $this->provider_issue_cost_usd + self::topupWithFeeUsd();

        return number_format($expensesUsd * self::rawRateUsd(), 2, '.', '');
    }

    /**
     * Расчётная прибыль с одной продажи: общая сумма к оплате (карта + пополнение) минус
     * фактические расходы банка.
     */
    public function getEstimatedProfitAttribute(): string
    {
        return number_format((float) $this->total_payable_rub - (float) $this->expenses_rub, 2, '.', '');
    }
}
