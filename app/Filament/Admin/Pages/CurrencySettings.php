<?php

namespace App\Filament\Admin\Pages;

use App\Models\CardProduct;
use App\Models\Setting;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class CurrencySettings extends Page implements HasForms
{
    use HasPageShield;
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;

    protected static string|UnitEnum|null $navigationGroup = 'Настройки';

    protected static ?string $navigationLabel = 'Валютная система';

    protected static ?string $title = 'Валютная система';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.admin.pages.currency-settings';

    /**
     * @var array<string, mixed>
     */
    public ?array $data = [];

    public const MARKUP_KEYS = [
        'currency_markup_usd_percent',
        'currency_markup_eur_percent',
        'currency_markup_gbp_percent',
    ];

    /**
     * Поля блока «Калькулятор» — значения по умолчанию только для судобства повторного
     * использования (тест-сценарий), реальный расчёт не завязан на эти константы.
     */
    public const CALCULATOR_KEYS = [
        'calc_card_price_rub',
        'calc_topup_amount_usd',
        'calc_accept_fee_percent',
        'calc_withdrawal_fee_percent',
        'calc_master_topup_fee_percent',
        'calc_provider_issue_cost_usd',
        'calc_card_topup_fee_percent',
    ];

    public function mount(): void
    {
        $this->form->fill([
            ...Setting::getMany(self::MARKUP_KEYS),
            ...Setting::getMany(self::CALCULATOR_KEYS),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Наценка на покупку валюты')
                    ->description('Наценка добавляется к текущему курсу ЦБ при расчёте стоимости валюты для клиентов.')
                    ->columns(3)
                    ->schema([
                        TextInput::make('currency_markup_usd_percent')
                            ->label('Наценка % на $')
                            ->numeric()
                            ->suffix('%')
                            ->live()
                            ->helperText(fn (mixed $state) => $this->costHelperText('currency_rate_usd', '$', $state)),
                        TextInput::make('currency_markup_eur_percent')
                            ->label('Наценка % на EUR')
                            ->numeric()
                            ->suffix('%')
                            ->live()
                            ->helperText(fn (mixed $state) => $this->costHelperText('currency_rate_eur', '€', $state)),
                        TextInput::make('currency_markup_gbp_percent')
                            ->label('Наценка % на GBP')
                            ->numeric()
                            ->suffix('%')
                            ->live()
                            ->helperText(fn (mixed $state) => $this->costHelperText('currency_rate_gbp', '£', $state)),
                    ]),

                Section::make('Калькулятор')
                    ->description('Расчёт маржи по одной карте: сколько денег реально останется у нас после всех комиссий и себестоимости у провайдера. Курс USD (ЦБ + наценка сверху) берётся из блока «Наценка на покупку валюты».')
                    ->columns(2)
                    ->schema([
                        TextInput::make('calc_card_price_rub')
                            ->label('Стоимость выпуска карты, руб')
                            ->numeric()
                            ->suffix('₽')
                            ->live(),
                        TextInput::make('calc_topup_amount_usd')
                            ->label('Сумма для пополнения, $')
                            ->numeric()
                            ->suffix('$')
                            ->live(),
                        TextInput::make('calc_accept_fee_percent')
                            ->label('Комиссия за приём платежа, %')
                            ->numeric()
                            ->suffix('%')
                            ->live(),
                        TextInput::make('calc_withdrawal_fee_percent')
                            ->label('Комиссия за вывод средств, %')
                            ->numeric()
                            ->suffix('%')
                            ->live(),
                        TextInput::make('calc_master_topup_fee_percent')
                            ->label('Комиссия пополнения мастер-счёта провайдера, %')
                            ->numeric()
                            ->suffix('%')
                            ->live(),
                        TextInput::make('calc_provider_issue_cost_usd')
                            ->label('Стоимость выпуска карты у провайдера, $')
                            ->numeric()
                            ->suffix('$')
                            ->live(),
                        TextInput::make('calc_card_topup_fee_percent')
                            ->label('Комиссия пополнения карты, %')
                            ->numeric()
                            ->suffix('%')
                            ->live(),
                    ]),
            ])
            ->statePath('data');
    }

    /**
     * Строка вида «1 $ = 95,50 ₽ (курс 92,72 ₽ + наценка 3%)» под полем наценки.
     */
    protected function costHelperText(string $rateKey, string $symbol, mixed $markupPercent): string
    {
        $rate = Setting::get($rateKey);

        if ($rate === null) {
            return 'Курс ещё не загружен — дождитесь обновления курсов валют.';
        }

        $rate = (float) $rate;
        $markup = (float) ($markupPercent ?? 0);
        $cost = $rate * (1 + $markup / 100);

        return sprintf(
            '1 %s = %s ₽ (курс %s ₽ + наценка %s%%)',
            $symbol,
            number_format($cost, 2, ',', ' '),
            number_format($rate, 2, ',', ' '),
            number_format($markup, 2, ',', ' '),
        );
    }

    public function save(): void
    {
        Setting::setMany($this->form->getState());

        Notification::make()->title('Настройки валютной системы сохранены')->success()->send();
    }

    /**
     * Реальный курс ЦБ (без наценки) — по нему считается фактическая себестоимость наших
     * долларовых расходов у провайдера (шаги 4 и 5 калькулятора).
     */
    protected function calculatorRawRateUsd(): float
    {
        return (float) (Setting::get('currency_rate_usd') ?? 0);
    }

    /**
     * Курс продажи клиенту (курс ЦБ + наценка из блока «Наценка на покупку валюты» этой же
     * страницы) — по нему клиент фактически платит за доллары пополнения (шаг 1 калькулятора).
     */
    protected function calculatorSellRateUsd(): float
    {
        $markup = (float) ($this->data['currency_markup_usd_percent'] ?? 0);

        return $this->calculatorRawRateUsd() * (1 + $markup / 100);
    }

    /**
     * Пошаговый расчёт маржи по одной карте: сколько денег поступает от клиента и что от них
     * остаётся после всех комиссий и себестоимости у провайдера. Общая формула:
     *
     * Стап 1 (приём) и шаг 2 (вывод) — простые проценты от текущего остатка. Формула
     * шага 3 (комиссия пополнения мастер-счёта) отличается — она берёт в расчёт не текущий
     * остаток, а сумму в долларах, которую нужно загрузить на мастер-счёт для выпуска
     * карты и пополнения:
     *
     * fмастер_руб = (Cпровайдер_усд + Tусд·fпополн/100) · fмастер/100 · Rraw
     *
     * то есть база для этой комиссии — стоимость выпуска карты у провайдера (Cпровайдер_усд) плюс
     * только фиа-составляющая пополнения карты (Tусд·fпополн/100), а не вся сумма пополнения —
     * сам трансит основной суммы на карту этой комиссией не облагается.
     *
     * @return array<int, array{label: string, rub: float, usd: float, remainderRub: float, remainderUsd: float, percentOfReceived: float}>
     */
    public function calculatorRows(): array
    {
        $d = $this->data;

        return $this->computeRows(
            cardPriceRub: (float) ($d['calc_card_price_rub'] ?? 0),
            providerIssueCostUsd: (float) ($d['calc_provider_issue_cost_usd'] ?? 0),
        );
    }

    /**
     * То же пошаговое разложение маржи, что и {@see calculatorRows()}, но по каждому активному
     * карточному продукту: стоимость карты (`price_rub`) и себестоимость выпуска у провайдера
     * (`provider_issue_cost_usd`) берутся из самого продукта, а все остальные величины (сумма
     * типового пополнения, комиссии приёма/вывода/мастер-счёта/пополнения карты, курс с наценкой)
     * — из полей блока «Калькулятор» этой же страницы.
     *
     * @return array<int, array{product: CardProduct, rows: array<int, array<string, mixed>>}>
     */
    public function cardProductRows(): array
    {
        return CardProduct::query()
            ->where('active', true)
            ->orderByDesc('sort')
            ->get()
            ->map(fn (CardProduct $product) => [
                'product' => $product,
                'rows' => $this->computeRows(
                    cardPriceRub: (float) $product->price_rub,
                    providerIssueCostUsd: (float) $product->provider_issue_cost_usd,
                ),
            ])
            ->all();
    }

    /**
     * @return array<int, array{label: string, rub: float, usd: float, remainderRub: float, remainderUsd: float, percentOfReceived: float}>
     */
    protected function computeRows(float $cardPriceRub, float $providerIssueCostUsd): array
    {
        $rawRate = $this->calculatorRawRateUsd();
        $sellRate = $this->calculatorSellRateUsd();
        $toUsd = fn (float $rub): float => $rawRate > 0 ? $rub / $rawRate : 0.0;

        $d = $this->data;
        $topupUsd = (float) ($d['calc_topup_amount_usd'] ?? 0);
        $acceptFeePercent = (float) ($d['calc_accept_fee_percent'] ?? 0);
        $withdrawalFeePercent = (float) ($d['calc_withdrawal_fee_percent'] ?? 0);
        $masterTopupFeePercent = (float) ($d['calc_master_topup_fee_percent'] ?? 0);
        $cardTopupFeePercent = (float) ($d['calc_card_topup_fee_percent'] ?? 0);

        $rows = [];

        // fee-часть пополнения карты считается заранее: она входит в сумму, которую реально платит клиент
        // (шаг 0, точно так же, как в OrderController::convertTopup()), и служит базой для комиссии шага 3.
        $topupFeeUsd = $topupUsd * $cardTopupFeePercent / 100;

        // Шаг 0: клиент платит цену карты + сумму пополнения вместе с комиссией провайдера за пополнение
        // карты (точно так же, как в OrderController::convertTopup(): totalRub = (topupUsd + feeUsd) * sellRate),
        // купленную у нас по курсу с наценкой.
        $balance = $cardPriceRub + ($topupUsd + $topupFeeUsd) * $sellRate;
        $rows[] = ['label' => 'Поступление к нам', 'rub' => $balance, 'usd' => $toUsd($balance), 'remainderRub' => $balance, 'remainderUsd' => $toUsd($balance)];

        // Шаг 1: комиссия платёжной системы за приём этого платежа.
        $fee = $balance * $acceptFeePercent / 100;
        $balance -= $fee;
        $rows[] = ['label' => 'Комиссия за приём платежа', 'rub' => $fee, 'usd' => $toUsd($fee), 'remainderRub' => $balance, 'remainderUsd' => $toUsd($balance)];

        // Шаг 2: комиссия за вывод оставшихся денег с баланса платёжной системы.
        $fee = $balance * $withdrawalFeePercent / 100;
        $balance -= $fee;
        $rows[] = ['label' => 'Комиссия за вывод средств', 'rub' => $fee, 'usd' => $toUsd($fee), 'remainderRub' => $balance, 'remainderUsd' => $toUsd($balance)];

        // Себестоимость выпуска карты у провайдера считается в долларах, как база для комиссии шага 3 и
        // не зависит от текущего остатка баланса.
        $costRub = $providerIssueCostUsd * $rawRate;

        // Шаг 3: комиссия за конвертацию рублей в доллары и пополнение мастер-счёта у провайдера:
        // берёт не % от текущего остатка, а % от суммы, которую нужно загрузить на мастер-счёт
        // для выпуска карты и пополнения (себестоимость выпуска + fee-часть пополнения, без
        // самой суммы пополнения).
        $masterTopupFeeUsd = ($providerIssueCostUsd + $topupFeeUsd) * $masterTopupFeePercent / 100;
        $fee = $masterTopupFeeUsd * $rawRate;
        $balance -= $fee;
        $rows[] = ['label' => 'Комиссия пополнения мастер-счёта', 'rub' => $fee, 'usd' => $masterTopupFeeUsd, 'remainderRub' => $balance, 'remainderUsd' => $toUsd($balance)];

        // Шаг 4: фиксированная себестоимость выпуска карты у провайдера, по реальному курсу.
        $balance -= $costRub;
        $rows[] = ['label' => 'Стоимость выпуска карты у провайдера', 'rub' => $costRub, 'usd' => $providerIssueCostUsd, 'remainderRub' => $balance, 'remainderUsd' => $toUsd($balance)];

        // Шаг 5: сумма, которая реально уходит на карту с мастер-счёта (сама сумма пополнения
        // плюс комиссия провайдера за пополнение карты), тоже по реальному курсу.
        $topupCostUsd = $topupUsd + $topupFeeUsd;
        $topupCostRub = $topupCostUsd * $rawRate;
        $balance -= $topupCostRub;
        $rows[] = ['label' => 'Стоимость пополнения карты', 'rub' => $topupCostRub, 'usd' => $topupCostUsd, 'remainderRub' => $balance, 'remainderUsd' => $toUsd($balance)];

        // % от поступления: доля суммы каждой строки ('rub') от общего поступления (шаг 0).
        // Для самого шага 0 это всегда 100% — он и есть всё поступление.
        $receivedRub = $rows[0]['rub'];

        foreach ($rows as &$row) {
            $row['percentOfReceived'] = $receivedRub > 0 ? $row['rub'] / $receivedRub * 100 : 0.0;
        }
        unset($row);

        return $rows;
    }
}
