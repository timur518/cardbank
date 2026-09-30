<!DOCTYPE html>
<html lang="ru" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Тарифы и условия карт — Mojno</title>
    <link rel="icon" href="/assets/images/favicon.svg" sizes="32x32" type="image/png">
    <link rel="icon" href="/assets/images/favicon.svg" sizes="16x16" type="image/png">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=sofia-sans:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">

@include('partials.site-header')

{{--
    Страница полностью автономна: контент собирается циклом по активным CardProduct
    ($cardProducts, см. routes/web.php), никакой карточный продукт здесь не захардкожен.
    Добавление/изменение/отключение продукта в админке (Карточные продукты) сразу же
    отражается на этой странице без правок кода.

    Показываются только поля, уже являющиеся публичными в API ЛК (CardProductResource /
    CardDetailResource) плюс комиссия пополнения (провайдерская, реально оплачивается
    клиентом) — себестоимость выпуска у провайдера (provider_issue_cost_usd) и любые другие
    внутренние/закупочные поля здесь намеренно не показываются.
--}}
<main class="bg-[#f3f0ee]">
    <section class="px-6 pb-24 pt-[100px] lg:px-10">
        <div class="mx-auto max-w-[900px]">
            <h1 class="text-3xl font-extrabold tracking-tight text-[#3A3C40] lg:text-4xl">Тарифы и условия карт</h1>
            <p class="mt-3 text-base leading-relaxed text-[#3A3C40]/60">
                Полные условия выпуска, использования, лимиты и ограничения по каждой активной карте.
            </p>

            <div class="mt-10">
            @forelse ($cardProducts as $product)
                @php
                    $usd = fn (mixed $value) => $value !== null ? '$' . number_format((float) $value, 2) : '—';
                @endphp
                <article class="tariff-card">
                    <div class="tariff-card-header">
                        <h2 class="text-2xl font-bold tracking-tight text-[#3A3C40]">{{ $product->name }}</h2>
                        <span class="tariff-price">{{ number_format((float) $product->price_rub, 0, ',', ' ') }} ₽ за выпуск</span>
                    </div>

                    @if ($product->description)
                        <p class="mt-3 text-base leading-relaxed text-[#3A3C40]/70">{{ $product->description }}</p>
                    @endif

                    @if ($product->advantages)
                        <div class="legal-doc-content mt-6">{!! $product->advantages !!}</div>
                    @endif

                    <h3 class="tariff-section-title">Основные параметры</h3>
                    <dl class="tariff-grid">
                        <div><dt>Платёжная система</dt><dd>{{ $product->network?->getLabel() ?? '—' }}</dd></div>
                        <div><dt>Страна выпуска карты</dt><dd>{{ $product->card_country?->getLabel() ?? '—' }}</dd></div>
                        <div><dt>Валюта карты</dt><dd>{{ $product->currency }}</dd></div>
                        <div><dt>3DS-коды подтверждения</dt><dd>{{ $product->three_ds_supported ? 'Поддерживаются' : 'Не поддерживаются' }}</dd></div>
                        <div><dt>Apple Pay</dt><dd>{{ $product->apple_pay_enabled ? 'Доступен' : 'Недоступен' }}</dd></div>
                        <div><dt>Google Pay</dt><dd>{{ $product->google_pay_enabled ? 'Доступен' : 'Недоступен' }}</dd></div>
                        @if ($product->wallet_activation)
                            <div><dt>Подключение кошелька</dt><dd>{{ $product->wallet_activation }}</dd></div>
                        @endif
                    </dl>

                    <h3 class="tariff-section-title">Лимиты выпуска и пополнения</h3>
                    <dl class="tariff-grid">
                        <div><dt>Мин. сумма выпуска</dt><dd>{{ $usd($product->issue_min_amount) }}</dd></div>
                        <div><dt>Макс. сумма выпуска</dt><dd>{{ $usd($product->issue_max_amount) }}</dd></div>
                        <div><dt>Мин. сумма пополнения</dt><dd>{{ $usd($product->topup_min_amount) }}</dd></div>
                        <div><dt>Макс. сумма пополнения</dt><dd>{{ $usd($product->topup_max_amount) }}</dd></div>
                    </dl>

                    <h3 class="tariff-section-title">Комиссии</h3>
                    <dl class="tariff-grid">
                        <div><dt>Пополнение баланса карты</dt><dd>{{ $product->provider_topup_fee_percent !== null ? number_format((float) $product->provider_topup_fee_percent, 2) . '%' : '—' }}</dd></div>
                        <div><dt>Успешная оплата</dt><dd>{{ $usd($product->successful_payment_fee_usd) }}</dd></div>
                        <div><dt>Отказ в оплате</dt><dd>{{ $usd($product->decline_fee_usd) }}</dd></div>
                        <div><dt>Рисковая операция</dt><dd>{{ $usd($product->risk_operation_fee_usd) }}</dd></div>
                        <div><dt>Оплата не в долларах (FX)</dt><dd>{{ $product->non_usd_payment_fee ?: '—' }}</dd></div>
                    </dl>


                    @if ($product->restricted_merchants)
                        <h3 class="tariff-section-title">Ограничения по мерчантам</h3>
                        <div class="legal-doc-content">{!! $product->restricted_merchants !!}</div>
                    @endif

                    @if ($product->full_terms)
                        <h3 class="tariff-section-title">Полные условия использования</h3>
                        <div class="legal-doc-content">{!! $product->full_terms !!}</div>
                    @endif
                </article>
            @empty
                <p class="mt-6 text-base leading-relaxed text-[#3A3C40]/60">
                    Активных карточных продуктов пока нет. Загляните позже.
                </p>
            @endforelse
            </div>
        </div>
    </section>
</main>

@include('partials.site-footer')
</body>
</html>
