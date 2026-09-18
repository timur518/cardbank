@php
    $settings = \App\Models\Setting::getMany([
        'currency_rate_usd',
        'currency_markup_usd_percent',
        'currency_rate_eur',
        'currency_markup_eur_percent',
    ]);

    $ourPrice = fn (?string $rate, ?string $markupPercent) => $rate === null
        ? null
        : ((float) $rate) * (1 + ((float) ($markupPercent ?? 0)) / 100);

    $usdRate = $settings['currency_rate_usd'];
    $eurRate = $settings['currency_rate_eur'];
    $usdOurPrice = $ourPrice($usdRate, $settings['currency_markup_usd_percent']);
    $eurOurPrice = $ourPrice($eurRate, $settings['currency_markup_eur_percent']);

    $url = \App\Filament\Admin\Pages\CurrencySettings::getUrl();
@endphp

@if ($usdRate !== null || $eurRate !== null)
    <div class="flex flex-wrap items-center gap-2 px-6 pb-3 pt-4">
        @if ($usdRate !== null)
            <x-filament::badge
                tag="a"
                href="{{ $url }}"
                color="success"
                tooltip="Курс ЦБ {{ number_format((float) $usdRate, 2, ',', ' ') }} ₽ + наценка = наша цена продажи"
            >
                $ | {{ number_format((float) $usdRate, 1, ',', ' ') }} | {{ number_format($usdOurPrice, 1, ',', ' ') }}
            </x-filament::badge>
        @endif

        @if ($eurRate !== null)
            <x-filament::badge
                tag="a"
                href="{{ $url }}"
                color="info"
                tooltip="Курс ЦБ {{ number_format((float) $eurRate, 2, ',', ' ') }} ₽ + наценка = наша цена продажи"
            >
                EUR | {{ number_format((float) $eurRate, 1, ',', ' ') }} | {{ number_format($eurOurPrice, 1, ',', ' ') }}
            </x-filament::badge>
        @endif
    </div>
@endif
