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

    @php
        $cardProductRows = $this->cardProductRows();
    @endphp

    @if (count($cardProductRows) > 0)
        <x-filament::section heading="Расчёт маржи по карточным продуктам" description="Та же формула, что и в калькуляторе выше, но стоимость карты и стоимость выпуска у провайдера берутся из самого активного карточного продукта." style="margin-top: 24px;">
            <div style="display: flex; flex-direction: column; gap: 24px;">
                @foreach ($cardProductRows as $entry)
                    @php
                        $product = $entry['product'];
                        $rows = $entry['rows'];
                        $received = $rows[0] ?? null;
                        $remaining = end($rows) ?: null;
                    @endphp

                    <div style="border: 1px solid #e5e7eb; border-radius: 12px; padding: 16px;">
                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
                            @if ($product->skin)
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($product->skin) }}" alt="{{ $product->name }}" style="width: 64px; height: auto; border-radius: 6px;" />
                            @endif
                            <div>
                                <div style="font-weight: 700; font-size: 14px;">{{ $product->name }}</div>
                                <div style="font-size: 12px; color: #6b7280;">{{ $formatMoney((float) $product->price_rub, '₽') }}</div>
                            </div>
                        </div>

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
                                    @foreach ($rows as $row)
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
                                        <td style="padding: 8px 12px; text-align: right;" colspan="2">{{ $formatMoney($received['rub'] ?? 0, '₽') }}</td>
                                        <td style="padding: 8px 12px; text-align: right;" colspan="2">{{ $formatMoney($received['usd'] ?? 0, '$') }}</td>
                                        <td style="padding: 8px 12px; text-align: right;">{{ $formatPercent($received['percentOfReceived'] ?? 0) }}</td>
                                    </tr>
                                    <tr style="font-weight: 700;">
                                        <td style="padding: 8px 12px;">Осталось (чистая прибыль)</td>
                                        <td style="padding: 8px 12px; text-align: right;" colspan="2">{{ $formatMoney($remaining['remainderRub'] ?? 0, '₽') }}</td>
                                        <td style="padding: 8px 12px; text-align: right;" colspan="2">{{ $formatMoney($remaining['remainderUsd'] ?? 0, '$') }}</td>
                                        <td style="padding: 8px 12px; text-align: right; color: #16a34a;">
                                            {{ $formatPercent(($received['rub'] ?? 0) > 0 ? ($remaining['remainderRub'] ?? 0) / $received['rub'] * 100 : 0) }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-filament::section>
    @endif
</x-filament-panels::page>
