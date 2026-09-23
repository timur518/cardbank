<?php

namespace App\Filament\Admin\Pages;

use App\Models\Setting;
use BackedEnum;
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
     * Остаток = (Pруб + Tусд·Rsell)
     *           · (1 − fприём/100) · (1 − fвывод/100) · (1 − fмастер/100)
     *           − Cпровайдер_усд·Rraw − Tусд·(1 + fпополн/100)·Rraw
     *
     * где Rsell = Rraw·(1 + наценка/100). Разница между Rsell и Rraw на сумме пополнения —
     * это курсовая часть маржи, комиссии и себестоимость выпуска/пополнения — остальная часть.
     *
     * @return array<int, array{label: string, rub: float, usd: float, remainderRub: float, remainderUsd: float, percentOfReceived: float}>
     */
    public function calculatorRows(): array
    {
        $rawRate = $this->calculatorRawRateUsd();
        $sellRate = $this->calculatorSellRateUsd();
        $toUsd = fn (float $rub): float => $rawRate > 0 ? $rub / $rawRate : 0.0;

        $d = $this->data;
        $cardPriceRub = (float) ($d['calc_card_price_rub'] ?? 0);
        $topupUsd = (float) ($d['calc_topup_amount_usd'] ?? 0);
        $acceptFeePercent = (float) ($d['calc_accept_fee_percent'] ?? 0);
        $withdrawalFeePercent = (float) ($d['calc_withdrawal_fee_percent'] ?? 0);
        $masterTopupFeePercent = (float) ($d['calc_master_topup_fee_percent'] ?? 0);
        $providerIssueCostUsd = (float) ($d['calc_provider_issue_cost_usd'] ?? 0);
        $cardTopupFeePercent = (float) ($d['calc_card_topup_fee_percent'] ?? 0);

        $rows = [];

        // Шаг 0: клиент платит цену карты + сумму пополнения, купленную у нас по курсу с наценкой.
        $balance = $cardPriceRub + $topupUsd * $sellRate;
        $rows[] = ['label' => 'Поступление к нам', 'rub' => $balance, 'usd' => $toUsd($balance), 'remainderRub' => $balance, 'remainderUsd' => $toUsd($balance)];

        // Шаг 1: комиссия платёжной системы за приём этого платежа.
        $fee = $balance * $acceptFeePercent / 100;
        $balance -= $fee;
        $rows[] = ['label' => 'Комиссия за приём платежа', 'rub' => $fee, 'usd' => $toUsd($fee), 'remainderRub' => $balance, 'remainderUsd' => $toUsd($balance)];

        // Шаг 2: комиссия за вывод оставшихся денег с баланса платёжной системы.
        $fee = $balance * $withdrawalFeePercent / 100;
        $balance -= $fee;
        $rows[] = ['label' => 'Комиссия за вывод средств', 'rub' => $fee, 'usd' => $toUsd($fee), 'remainderRub' => $balance, 'remainderUsd' => $toUsd($balance)];

        // Шаг 3: комиссия за конвертацию рублей в доллары и пополнение мастер-счёта у провайдера.
        $fee = $balance * $masterTopupFeePercent / 100;
        $balance -= $fee;
        $rows[] = ['label' => 'Комиссия пополнения мастер-счёта', 'rub' => $fee, 'usd' => $toUsd($fee), 'remainderRub' => $balance, 'remainderUsd' => $toUsd($balance)];

        // Шаг 4: фиксированная себестоимость выпуска карты у провайдера, по реальному курсу.
        $costRub = $providerIssueCostUsd * $rawRate;
        $balance -= $costRub;
        $rows[] = ['label' => 'Стоимость выпуска карты у провайдера', 'rub' => $costRub, 'usd' => $providerIssueCostUsd, 'remainderRub' => $balance, 'remainderUsd' => $toUsd($balance)];

        // Шаг 5: сумма, которая реально уходит на карту с мастер-счёта (сама сумма пополнения
        // плюс комиссия провайдера за пополнение карты), тоже по реальному курсу.
        $topupCostUsd = $topupUsd * (1 + $cardTopupFeePercent / 100);
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

    /**
     * Данные для графика «как маржа съедается по шагам»: столбик-диаграмма (staircase),
     * где общая высота всегда равна полному поступлению (шаг 0, receivedRub) и делится на две
     * заливки: зелёная (остаток после шага, снизу) и красная (уже съеденная сумма = receivedRub −
     * остаток, сверху). У каждой точки граница между заливками держится полкой до следующего
     * шага, а там резко прыгает вниз на величину израсходованного в этот шаг — такие ступени
     * наглядно показывают, где расход большой (большой скачок), а где маленький.
     * Сначала (шаг 0, Поступление) зелёная занимает весь график, красная — ноль; к концу
     * (шаг 5, Осталось) наоборот — красная занимает большую часть, а зелёная остаётся тонкой
     * полоской чистой прибыли.
     *
     * @return array{
     *     width: int,
     *     height: int,
     *     paddingX: int,
     *     paddingY: int,
     *     points: array<int, array{x: float, y: float, remainderRub: float, stepCostRub: float, label: string}>,
     *     incomeAreaPoints: string,
     *     expenseAreaPoints: string,
     * }
     */
    public function calculatorChartSvg(): array
    {
        $rows = $this->calculatorRows();
        $count = count($rows);

        $width = 640;
        $height = 240;
        $paddingX = 16;
        $paddingY = 16;
        $innerHeight = $height - 2 * $paddingY;

        $receivedRub = $rows[0]['remainderRub'] ?? 0.0;
        $domainMax = $receivedRub > 0 ? $receivedRub : 1.0;

        // большее значение → выше на графике (меньше y).
        $y = fn (float $value): float => round($paddingY + (1 - max(0.0, min($value, $domainMax)) / $domainMax) * $innerHeight, 1);

        $points = [];

        foreach ($rows as $i => $row) {
            $x = $count > 1 ? round($paddingX + ($i / ($count - 1)) * ($width - 2 * $paddingX), 1) : (float) $paddingX;
            $points[] = [
                'x' => $x,
                'y' => $y($row['remainderRub']),
                'remainderRub' => $row['remainderRub'],
                'stepCostRub' => $i === 0 ? 0.0 : $row['rub'],
                'label' => $row['label'],
            ];
        }

        // Ступенчатая граница между заливками: от точки i горизонтально до x_{i+1}, потом
        // вертикальный скачок до y_{i+1} ровно в точке x_{i+1} — величина скачка наглядно равна
        // stepCostRub этой точки.
        $staircase = [];

        foreach ($points as $i => $point) {
            $staircase[] = ['x' => $point['x'], 'y' => $point['y']];

            if ($i < $count - 1) {
                $staircase[] = ['x' => $points[$i + 1]['x'], 'y' => $point['y']];
            }
        }

        $topLeft = $paddingX . ',' . $paddingY;
        $topRight = ($width - $paddingX) . ',' . $paddingY;
        $bottomLeft = $paddingX . ',' . ($height - $paddingY);
        $bottomRight = ($width - $paddingX) . ',' . ($height - $paddingY);

        $staircaseForward = collect($staircase)->map(fn (array $p): string => "{$p['x']},{$p['y']}")->implode(' ');
        $staircaseBackward = collect($staircase)->reverse()->map(fn (array $p): string => "{$p['x']},{$p['y']}")->implode(' ');

        // Зелёная зона «Поступление» (остаток): от ступенчатой границы вниз, до низа графика.
        $incomeAreaPoints = $staircaseForward . ' ' . $bottomRight . ' ' . $bottomLeft;

        // Красная зона «Расходы» (уже израсходовано): от верха графика до ступенчатой границы.
        $expenseAreaPoints = $topLeft . ' ' . $topRight . ' ' . $staircaseBackward;

        return [
            'width' => $width,
            'height' => $height,
            'paddingX' => $paddingX,
            'paddingY' => $paddingY,
            'points' => $points,
            'incomeAreaPoints' => $incomeAreaPoints,
            'expenseAreaPoints' => $expenseAreaPoints,
        ];
    }
}
