<x-filament-panels::page>
    <form wire:submit.prevent>
        {{ $this->form }}
    </form>

    @php
        $stats = $this->getStats();
        $breakdown = $this->getRuleBreakdown();
    @endphp

    <div class="grid gap-4 md:grid-cols-2">
        <x-filament::section>
            <x-slot name="heading">Количество подозрительных операций за период</x-slot>

            <div class="text-3xl font-bold">
                {{ number_format($stats['alerts_count'], 0, ',', ' ') }}
            </div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Сумма подозрительных операций за период</x-slot>

            <div class="text-3xl font-bold">
                {{ number_format($stats['suspicious_amount'], 2, ',', ' ') }}
            </div>
        </x-filament::section>
    </div>

    <x-filament::section>
        <x-slot name="heading">Разбивка по типам сработавших правил</x-slot>

        @if ($breakdown->isEmpty())
            <p class="text-sm text-gray-500">За выбранный период предупреждений не было.</p>
        @else
            <table class="fi-ta-table w-full text-start">
                <thead>
                    <tr>
                        <th class="p-2 text-start text-sm font-medium text-gray-500">Правило</th>
                        <th class="p-2 text-start text-sm font-medium text-gray-500">Количество предупреждений</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($breakdown as $row)
                        <tr class="border-t border-gray-100 dark:border-white/10">
                            <td class="p-2">{{ $row['rule'] }}</td>
                            <td class="p-2">{{ $row['count'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </x-filament::section>
</x-filament-panels::page>
