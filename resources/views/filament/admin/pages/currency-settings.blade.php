<x-filament-panels::page>
    @livewire(\App\Filament\Admin\Pages\CurrencySettings\CurrencyRatesWidget::class)

    <form wire:submit="save">
        {{ $this->form }}

        <div class="mt-6">
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
        $calculatorRubPoints = collect($calculatorChart['rub'])->map(fn (array $p) => "{$p['x']},{$p['y']}")->implode(' ');
        $calculatorUsdPoints = collect($calculatorChart['usd'])->map(fn (array $p) => "{$p['x']},{$p['y']}")->implode(' ');
    @endphp

    <x-filament::section heading="Расчёт калькулятора" class="mt-6">
        <div class="fi-ta-ctn overflow-x-auto">
            <table class="fi-ta-table w-full text-sm">
                <thead>
                    <tr class="text-start text-xs font-medium text-gray-500 dark:text-gray-400">
                        <th class="px-3 py-2 text-start">Предмет</th>
                        <th class="px-3 py-2 text-end">Сумма руб</th>
                        <th class="px-3 py-2 text-end">Сумма $</th>
                        <th class="px-3 py-2 text-end">Остаток руб</th>
                        <th class="px-3 py-2 text-end">Остаток $</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @foreach ($calculatorRows as $row)
                        <tr>
                            <td class="px-3 py-2">{{ $row['label'] }}</td>
                            <td class="px-3 py-2 text-end">{{ $formatMoney($row['rub'], '₽') }}</td>
                            <td class="px-3 py-2 text-end">{{ $formatMoney($row['usd'], '$') }}</td>
                            <td class="px-3 py-2 text-end font-medium">{{ $formatMoney($row['remainderRub'], '₽') }}</td>
                            <td class="px-3 py-2 text-end font-medium">{{ $formatMoney($row['remainderUsd'], '$') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t border-gray-200 font-semibold dark:border-white/10">
                        <td class="px-3 py-2">Поступило</td>
                        <td class="px-3 py-2 text-end" colspan="2">{{ $formatMoney($calculatorReceived['rub'] ?? 0, '₽') }}</td>
                        <td class="px-3 py-2 text-end" colspan="2">{{ $formatMoney($calculatorReceived['usd'] ?? 0, '$') }}</td>
                    </tr>
                    <tr class="font-semibold">
                        <td class="px-3 py-2">Осталось</td>
                        <td class="px-3 py-2 text-end" colspan="2">{{ $formatMoney($calculatorRemaining['remainderRub'] ?? 0, '₽') }}</td>
                        <td class="px-3 py-2 text-end" colspan="2">{{ $formatMoney($calculatorRemaining['remainderUsd'] ?? 0, '$') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </x-filament::section>

    <x-filament::section heading="Как маржа съедается по шагам" class="mt-6">
        <div class="flex items-center gap-4 text-xs mb-3">
            <span class="flex items-center gap-1">
                <span class="inline-block w-3 h-3 rounded-full" style="background:#22c55e"></span>
                Остаток, ₽
            </span>
            <span class="flex items-center gap-1">
                <span class="inline-block w-3 h-3 rounded-full" style="background:#ef4444"></span>
                Остаток, $
            </span>
        </div>

        <svg viewBox="0 0 {{ $calculatorChart['width'] }} {{ $calculatorChart['height'] }}" class="w-full" style="height: 220px">
            <polyline points="{{ $calculatorRubPoints }}" fill="none" stroke="#22c55e" stroke-width="2.5" />
            <polyline points="{{ $calculatorUsdPoints }}" fill="none" stroke="#ef4444" stroke-width="2.5" />

            @foreach ($calculatorChart['rub'] as $point)
                <circle cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="3.5" fill="#22c55e" />
            @endforeach

            @foreach ($calculatorChart['usd'] as $point)
                <circle cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="3.5" fill="#ef4444" />
            @endforeach
        </svg>

        <div class="grid mt-2 text-[11px] text-gray-500 dark:text-gray-400" style="grid-template-columns: repeat({{ count($calculatorChart['labels']) }}, minmax(0, 1fr))">
            @foreach ($calculatorChart['labels'] as $label)
                <div class="text-center px-1">{{ $label }}</div>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-panels::page>
