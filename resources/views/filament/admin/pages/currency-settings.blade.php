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
    @endphp

    <x-filament::section heading="Расчёт калькулятора" class="mt-6">
        <div class="fi-ta-ctn overflow-x-auto">
            <table class="fi-ta-table w-full text-sm">
                <thead>
                    <tr class="text-start text-xs font-medium text-gray-500 dark:text-gray-400">
                        <th class="px-3 py-2 text-start">Предмет</th>
                        <th class="px-3 py-2 text-end">Сумма руб</th>
                        <th class="px-3 py-2 text-end">Сумма $</th>
                        <th class="px-3 py-2 text-end">Остаток после шага</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @foreach ($calculatorRows as $row)
                        <tr>
                            <td class="px-3 py-2">{{ $row['label'] }}</td>
                            <td class="px-3 py-2 text-end">{{ $formatMoney($row['rub'], '₽') }}</td>
                            <td class="px-3 py-2 text-end">{{ $formatMoney($row['usd'], '$') }}</td>
                            <td class="px-3 py-2 text-end font-medium">{{ $formatMoney($row['remainderRub'], '₽') }} / {{ $formatMoney($row['remainderUsd'], '$') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t border-gray-200 font-semibold dark:border-white/10">
                        <td class="px-3 py-2">Итого</td>
                        <td class="px-3 py-2 text-end" colspan="3">
                            Поступило: {{ $formatMoney($calculatorReceived['rub'] ?? 0, '₽') }} / {{ $formatMoney($calculatorReceived['usd'] ?? 0, '$') }}
                            &nbsp;&mdash;&nbsp;
                            Осталось: {{ $formatMoney($calculatorRemaining['remainderRub'] ?? 0, '₽') }} / {{ $formatMoney($calculatorRemaining['remainderUsd'] ?? 0, '$') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </x-filament::section>
</x-filament-panels::page>
