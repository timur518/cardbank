<x-filament-panels::page>
    @php
        $products = $this->getActiveProducts();
        $rows = $this->getRows();
    @endphp

    @if ($products->isEmpty())
        <x-filament::section>
            <p style="color: #6b7280;">Нет активных карточных продуктов — добавьте их в «Карточных продуктах», чтобы здесь появились колонки рейтинга.</p>
        </x-filament::section>
    @elseif ($rows->isEmpty())
        <x-filament::section>
            <p style="color: #6b7280;">Справочник мерчантов пуст.</p>
        </x-filament::section>
    @else
        <x-filament::section>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                    <thead>
                        <tr style="text-align: left; font-size: 11px; font-weight: 600; color: #6b7280; border-bottom: 1px solid #e5e7eb;">
                            <th style="padding: 8px 12px;"></th>
                            <th style="padding: 8px 12px; text-align: left;">Мерчант</th>
                            @foreach ($products as $product)
                                <th style="padding: 8px 12px; text-align: right;">{{ $product->name }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $row)
                            <tr style="border-bottom: 1px solid #f0f0f0;">
                                <td style="padding: 8px 12px; width: 44px;">
                                    @include('filament.tables.columns.merchant-logo', ['record' => $row['merchant']])
                                </td>
                                <td style="padding: 8px 12px; font-weight: 500;">{{ $row['merchant']->name }}</td>
                                @foreach ($products as $product)
                                    <td style="padding: 8px 12px; text-align: right; white-space: nowrap;">
                                        @php $stat = $row['rates'][$product->id] ?? null; @endphp
                                        <div>
                                            @if ($stat === null || $stat->rate === null)
                                                <span style="color: #9ca3af;">—</span>
                                            @else
                                                {{ number_format($stat->rate * 100, 0) }}%
                                            @endif
                                        </div>
                                        @if ($stat && ($stat->success_count > 0 || $stat->decline_count > 0))
                                            <div style="font-size: 11px; color: #6b7280;" title="Успешно / Отказ / Собственный рейтинг">
                                                {{ $stat->success_count }}/{{ $stat->decline_count }}/{{ number_format($stat->own_rate * 100, 0) }}%
                                            </div>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    @endif
</x-filament-panels::page>
