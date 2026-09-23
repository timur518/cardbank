<x-filament-panels::page>
    @livewire(\App\Filament\Admin\Pages\CurrencySettings\CurrencyRatesWidget::class)

    <form wire:submit="save">
        {{ $this->form }}

        <div style="margin-top: 24px;">
            <x-filament::button type="submit">
                Сохранить
            </x-filament::button>
        </div>
    </form>

    @php
        $calculatorRows = $this->calculatorRows();
        $calculatorReceived = $calculatorRows[0] ?? null;
        $calculatorRemaining = end($calculatorRows) ?: null;
        $formatMoney = fn (float $value, string $symbol) => number_format($value, 2, ',', ' ') . ' ' . $symbol;
        $formatPercent = fn (float $value) => number_format($value, 2, ',', ' ') . '%';
        $calculatorChart = $this->calculatorChartSvg();
    @endphp

    <x-filament::section heading="Расчёт калькулятора" style="margin-top: 24px;">
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <thead>
                    <tr style="text-align: left; font-size: 11px; font-weight: 600; color: #6b7280; border-bottom: 1px solid #e5e7eb;">
                        <th style="padding: 8px 12px; text-align: left;">Предмет</th>
                        <th style="padding: 8px 12px; text-align: right;">Сумма руб</th>
                        <th style="padding: 8px 12px; text-align: right;">Сумма $</th>
                        <th style="padding: 8px 12px; text-align: right;">Остаток руб</th>
                        <th style="padding: 8px 12px; text-align: right;">Остаток $</th>
                        <th style="padding: 8px 12px; text-align: right;">% от поступления</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($calculatorRows as $row)
                        <tr style="border-bottom: 1px solid #f0f0f0;">
                            <td style="padding: 8px 12px;">{{ $row['label'] }}</td>
                            <td style="padding: 8px 12px; text-align: right;">{{ $formatMoney($row['rub'], '₽') }}</td>
                            <td style="padding: 8px 12px; text-align: right;">{{ $formatMoney($row['usd'], '$') }}</td>
                            <td style="padding: 8px 12px; text-align: right; font-weight: 600;">{{ $formatMoney($row['remainderRub'], '₽') }}</td>
                            <td style="padding: 8px 12px; text-align: right; font-weight: 600;">{{ $formatMoney($row['remainderUsd'], '$') }}</td>
                            <td style="padding: 8px 12px; text-align: right; color: #6b7280;">{{ $formatPercent($row['percentOfReceived']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="border-top: 2px solid #e5e7eb; font-weight: 700;">
                        <td style="padding: 8px 12px;">Поступило</td>
                        <td style="padding: 8px 12px; text-align: right;" colspan="2">{{ $formatMoney($calculatorReceived['rub'] ?? 0, '₽') }}</td>
                        <td style="padding: 8px 12px; text-align: right;" colspan="2">{{ $formatMoney($calculatorReceived['usd'] ?? 0, '$') }}</td>
                        <td style="padding: 8px 12px; text-align: right;">{{ $formatPercent($calculatorReceived['percentOfReceived'] ?? 0) }}</td>
                    </tr>
                    <tr style="font-weight: 700;">
                        <td style="padding: 8px 12px;">Осталось (чистая прибыль)</td>
                        <td style="padding: 8px 12px; text-align: right;" colspan="2">{{ $formatMoney($calculatorRemaining['remainderRub'] ?? 0, '₽') }}</td>
                        <td style="padding: 8px 12px; text-align: right;" colspan="2">{{ $formatMoney($calculatorRemaining['remainderUsd'] ?? 0, '$') }}</td>
                        <td style="padding: 8px 12px; text-align: right; color: #16a34a;">
                            {{ $formatPercent(($calculatorReceived['rub'] ?? 0) > 0 ? ($calculatorRemaining['remainderRub'] ?? 0) / $calculatorReceived['rub'] * 100 : 0) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </x-filament::section>

    <x-filament::section heading="Как маржа съедается по шагам" style="margin-top: 24px;">
        <div style="display: flex; align-items: center; gap: 20px; font-size: 12px; margin-bottom: 12px;">
            <span style="display: inline-flex; align-items: center; gap: 6px;">
                <span style="display: inline-block; width: 12px; height: 12px; border-radius: 3px; background: #22c55e;"></span>
                Поступление (остаток)
            </span>
            <span style="display: inline-flex; align-items: center; gap: 6px;">
                <span style="display: inline-block; width: 12px; height: 12px; border-radius: 3px; background: #ef4444;"></span>
                Расходы (уже списано)
            </span>
        </div>

        <svg viewBox="0 0 {{ $calculatorChart['width'] }} {{ $calculatorChart['height'] }}" style="width: 100%; height: 240px; display: block;">
            <polygon points="{{ $calculatorChart['incomeAreaPoints'] }}" fill="#22c55e" fill-opacity="0.55" />
            <polygon points="{{ $calculatorChart['expenseAreaPoints'] }}" fill="#ef4444" fill-opacity="0.55" />

            @foreach ($calculatorChart['points'] as $point)
                <line x1="{{ $point['x'] }}" y1="{{ $calculatorChart['paddingY'] }}" x2="{{ $point['x'] }}" y2="{{ $calculatorChart['height'] - $calculatorChart['paddingY'] }}" stroke="#ffffff" stroke-width="1" stroke-opacity="0.6" />
                <circle cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="4" fill="#111827" />

                @if ($point['stepCostRub'] > 0)
                    <text x="{{ $point['x'] }}" y="{{ $calculatorChart['paddingY'] + 10 }}" text-anchor="middle" font-size="10" fill="#7f1d1d">-{{ number_format($point['stepCostRub'], 0, ',', ' ') }}₽</text>
                @endif
            @endforeach
        </svg>

        <div style="display: grid; grid-template-columns: repeat({{ count($calculatorChart['points']) }}, minmax(0, 1fr)); margin-top: 8px; font-size: 11px; color: #6b7280;">
            @foreach ($calculatorChart['points'] as $point)
                <div style="text-align: center; padding: 0 4px;">{{ $point['label'] }}</div>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-panels::page>
