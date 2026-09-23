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
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="border-top: 2px solid #e5e7eb; font-weight: 700;">
                        <td style="padding: 8px 12px;">Поступило</td>
                        <td style="padding: 8px 12px; text-align: right;" colspan="2">{{ $formatMoney($calculatorReceived['rub'] ?? 0, '₽') }}</td>
                        <td style="padding: 8px 12px; text-align: right;" colspan="2">{{ $formatMoney($calculatorReceived['usd'] ?? 0, '$') }}</td>
                    </tr>
                    <tr style="font-weight: 700;">
                        <td style="padding: 8px 12px;">Осталось</td>
                        <td style="padding: 8px 12px; text-align: right;" colspan="2">{{ $formatMoney($calculatorRemaining['remainderRub'] ?? 0, '₽') }}</td>
                        <td style="padding: 8px 12px; text-align: right;" colspan="2">{{ $formatMoney($calculatorRemaining['remainderUsd'] ?? 0, '$') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </x-filament::section>

    <x-filament::section heading="Как маржа съедается по шагам" style="margin-top: 24px;">
        <div style="display: flex; align-items: center; gap: 20px; font-size: 12px; margin-bottom: 12px;">
            <span style="display: inline-flex; align-items: center; gap: 6px;">
                <span style="display: inline-block; width: 12px; height: 12px; border-radius: 3px; background: #ef4444;"></span>
                Будет съедено дальше (комиссии и себестоимость)
            </span>
            <span style="display: inline-flex; align-items: center; gap: 6px;">
                <span style="display: inline-block; width: 12px; height: 12px; border-radius: 3px; background: #22c55e;"></span>
                Чистая прибыль
            </span>
        </div>

        <svg viewBox="0 0 {{ $calculatorChart['width'] }} {{ $calculatorChart['height'] }}" style="width: 100%; height: 240px; display: block;">
            <polygon points="{{ $calculatorChart['profitAreaPoints'] }}" fill="#22c55e" fill-opacity="0.35" />
            <polygon points="{{ $calculatorChart['expenseAreaPoints'] }}" fill="#ef4444" fill-opacity="0.35" />

            <line x1="{{ $calculatorChart['paddingX'] }}" y1="{{ $calculatorChart['profitY'] }}" x2="{{ $calculatorChart['width'] - $calculatorChart['paddingX'] }}" y2="{{ $calculatorChart['profitY'] }}" stroke="#16a34a" stroke-width="1.5" stroke-dasharray="4 4" />

            <polyline points="{{ collect($calculatorChart['points'])->map(fn ($p) => "{$p['x']},{$p['y']}")->implode(' ') }}" fill="none" stroke="#111827" stroke-width="2.5" />

            @foreach ($calculatorChart['points'] as $point)
                <circle cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="4" fill="#111827" />
            @endforeach
        </svg>

        <div style="display: grid; grid-template-columns: repeat({{ count($calculatorChart['points']) }}, minmax(0, 1fr)); margin-top: 8px; font-size: 11px; color: #6b7280;">
            @foreach ($calculatorChart['points'] as $point)
                <div style="text-align: center; padding: 0 4px;">{{ $point['label'] }}</div>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-panels::page>
