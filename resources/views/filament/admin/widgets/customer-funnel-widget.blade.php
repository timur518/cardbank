<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">Путь клиента от захода на сайт до активной карты</x-slot>
        <x-slot name="description">Заходы на сайт учитывает внешний счётчик посещаемости из настроек аналитики</x-slot>

        @php
            $steps = $this->getSteps();
            $first = $steps[0]['count'] ?: 1;
        @endphp

        <div class="space-y-3">
            @foreach ($steps as $index => $step)
                @php
                    $percentOfFirst = $step['count'] / $first * 100;
                    $previous = $steps[$index - 1]['count'] ?? null;
                    $percentOfPrevious = $previous ? $step['count'] / $previous * 100 : null;
                @endphp

                <div>
                    <div class="mb-1 flex items-center justify-between text-sm">
                        <span class="font-medium">{{ $step['label'] }}</span>
                        <span class="text-gray-500">
                            {{ number_format($step['count'], 0, ',', ' ') }}
                            @if ($percentOfPrevious !== null)
                                &middot; {{ number_format($percentOfPrevious, 1, ',', ' ') }}% от предыдущего шага
                            @endif
                        </span>
                    </div>
                    <div class="h-3 w-full rounded-full bg-gray-100 dark:bg-gray-800">
                        <div
                            class="h-3 rounded-full bg-primary-500"
                            style="width: {{ max($percentOfFirst, 2) }}%"
                        ></div>
                    </div>
                </div>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
