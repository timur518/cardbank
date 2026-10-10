<!DOCTYPE html>
<html lang="ru" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Заголовок/описание сформулированы по реальному содержимому лендинга (секции #hero, #how,
    #products ниже): 3 карты Visa/Mastercard, оформление онлайн за 3 минуты, пополнение из России
    через СБП. --}}
    <title>Виртуальные карты для оплаты зарубежных сервисов - Можно</title>
    <meta name="description" content="Виртуальные карты Visa и Mastercard для оплаты ИИ-сервисов, подписок и покупок за рубежом. Оформление онлайн за 3 минуты, пополнение из России через СБП.">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="https://mojno.cc/">

    <link rel="icon" href="assets/images/favicon.svg" sizes="32x32" type="image/svg+xml">
    <link rel="icon" href="assets/images/favicon.svg" sizes="16x16" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=sofia-sans:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <meta name="mailru-domain" content="m7BvodbH4AQGo7RI" />

    {{-- Open Graph / Twitter Card — превью ссылки при шеринге главной страницы в соцсетях и
    мессенджерах, на видимость самой страницы не влияют. --}}
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Mojno">
    <meta property="og:locale" content="ru_RU">
    <meta property="og:url" content="https://mojno.cc/">
    <meta property="og:title" content="Виртуальные карты для оплаты зарубежных сервисов - Можно">
    <meta property="og:description" content="Виртуальные карты Visa и Mastercard для оплаты ИИ-сервисов, подписок и покупок за рубежом. Оформление онлайн за 3 минуты, пополнение из России через СБП.">
    <meta property="og:image" content="{{ asset('assets/images/herobg.png') }}">
    <meta property="og:image:width" content="1713">
    <meta property="og:image:height" content="918">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Виртуальные карты для оплаты зарубежных сервисов - Можно">
    <meta name="twitter:description" content="Виртуальные карты Visa и Mastercard для оплаты ИИ-сервисов, подписок и покупок за рубежом.">
    <meta name="twitter:image" content="{{ asset('assets/images/herobg.png') }}">

    {{-- JSON-LD (Organization + WebSite) — структурированные данные для поисковиков, на вёрстку
    страницы не влияют. --}}
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": "Можно",
        "alternateName": "Mojno",
        "url": "https://mojno.cc/",
        "logo": "{{ asset('assets/images/logo.png') }}",
        "email": "info@mojno.cc",
        "description": "Виртуальные карты для оплаты сервисов, подписок и покупок по всему миру."
    }
    </script>
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebSite",
        "name": "Можно",
        "url": "https://mojno.cc/"
    }
    </script>

    {{-- Код счётчика и пикселей/прочих скриптов из админки (Настройки -> Аналитика и внешние
    сервисы), вставляется как есть (сырой HTML, включая <script>) — тот же код подтягивается
    в личном кабинете через utils/analytics.ts. --}}
    {!! $analyticsCodes['analytics_counter_id'] ?? '' !!}
    {!! $analyticsCodes['analytics_pixel_ids'] ?? '' !!}

</head>
<body class="font-sans antialiased">

@include('partials.site-header')

<main id="top" class="bg-[#f3f0ee]">
    {{-- ================= HERO ================= --}}
    <section id="hero" class="px-6 pt-[20px] lg:px-10 lg:-mt-[115px]">
        <div class="mx-auto max-w-[1400px]">
            <div class="hero-frame relative isolate flex h-[460px] items-end overflow-hidden rounded-[32px] sm:h-[560px] lg:h-[750px] lg:rounded-[40px]">
                <video
                    src="{{ asset('assets/images/herobg.mp4') }}"
                    class="hero-video absolute inset-0 h-full w-full object-cover"
                    autoplay
                    loop
                    muted
                    playsinline
                ></video>
                <div class="absolute inset-0 bg-[linear-gradient(to_top,_#000_0px,_#000_40px,_rgba(0,0,0,0.4)_50%,_rgba(0,0,0,0)_100%)]"></div>

                <div class="hero-copy relative w-full px-6 pb-10 sm:px-10 sm:pb-12 lg:px-14 lg:pb-16">
                    <div data-reveal class="max-w-[620px]">
                        <span class="eyebrow !text-white/80">МОЖНО</span>
                        <h1 class="mt-5 text-[clamp(2.5rem,5.5vw,4.5rem)] font-medium leading-[0.98] tracking-[-0.03em] text-white">
                            Платить без границ
                        </h1>
                        <p class="mt-6 max-w-lg text-lg leading-relaxed text-white/80">
                            Виртуальные банковские карты для оплаты сервисов, подписок и покупок за рубежом -
                            оформляется за минуты и пополняется из России через СБП.
                        </p>

                        <div class="mt-8 flex flex-wrap items-center gap-4">
                            <a href="#products" class="btn btn-hero-orange">Оформить карту онлайн</a>
                            <a href="https://mne.mojno.cc/" class="btn btn-hero-black">Войти в аккаунт</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ================= LIFESTYLE / OVAL CAROUSEL ================= --}}
    <section id="lifestyle" class="mt-[150px] overflow-hidden" data-carousel>
        <div class="mx-auto max-w-[1400px] px-6 lg:px-10">
            <div class="grid gap-8 lg:grid-cols-[1.15fr_.85fr] lg:items-start">
                <div data-reveal>
                    <h2 class="max-w-4xl text-[clamp(2.8rem,5vw,5.4rem)] font-semibold leading-[.94] tracking-[-.045em]">Виртуальные карты для платежей и покупок</h2>
                </div>
                <div data-reveal>
                    <p class="mt-4 max-w-lg text-[20px] font-normal leading-relaxed text-[#141414]">Удобный способ совершать покупки из России и СНГ</p>
                    <p class="max-w-lg text-base leading-relaxed text-[#3A3C40]/45">От подписки на ИИ-сервис до отеля в отпуске - просто</p>
                </div>
            </div>
        </div>

        @php
            $scenes = [
                ['tag'=>'ИИ-сервисы', 'title'=>'Оплата ИИ-сервисов и подписок', 'img'=>'assets/images/chatgpt.png'],
                ['tag'=>'Онлайн-шопинг', 'title'=>'Покупки в зарубежных онлайн-магазинах', 'img'=>'assets/images/ecomm.png'],
                ['tag'=>'Путешествия', 'title'=>'Отели, билеты и поездки за границей', 'img'=>'assets/images/booking.png'],
                ['tag'=>'На кассе', 'title'=>'Оплата телефоном в кафе и ресторанах', 'img'=>'assets/images/cafe.png'],
                ];
        @endphp
        <div class="oval-carousel mt-20" data-carousel-track tabindex="0" aria-label="Сценарии использования">
            @foreach ($scenes as $i => $scene)
                <article data-carousel-item data-reveal style="--reveal-delay: {{ $i * 80 }}ms" class="oval-slide group">
                    <img src="{{ $scene['img'] }}" alt="{{ $scene['title'] }}" loading="lazy" class="absolute inset-0 h-full w-full object-cover transition duration-1000 ease-out group-hover:scale-105">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/10 to-black/5"></div>
                    <div class="oval-slide-badges">
                        <span class="rounded-full bg-white/90 px-4 py-2 text-xs font-bold uppercase tracking-[.08em] text-[#3A3C40]">{{ $scene['tag'] }}</span>
                        <span class="oval-slide-title rounded-full bg-white px-6 py-3 text-center text-lg font-semibold leading-snug tracking-[-.01em] text-[#3A3C40] sm:text-xl">{{ $scene['title'] }}</span>
                    </div>
                </article>
            @endforeach
        </div>
        <div class="mx-auto flex max-w-[1400px] items-center justify-between px-6 lg:px-10">
            <div class="carousel-dots" data-carousel-dots></div>
            <div class="flex gap-2">
                <button type="button" data-carousel-prev aria-label="Предыдущий слайд" class="carousel-arrow">←</button>
                <button type="button" data-carousel-next aria-label="Следующий слайд" class="carousel-arrow">→</button>
            </div>
        </div>
    </section>

    {{-- ================= PRODUCTS ================= --}}
    <section id="products" class="mt-[150px] px-6 lg:px-10">
        <div class="mx-auto max-w-[1400px]">
            <div data-reveal class="max-w-4xl">
                <span class="eyebrow">Только три карты</span>
                <h2 class="mt-5 text-[clamp(2.8rem,5vw,5.4rem)] font-medium leading-[.94] tracking-[-.045em]">Карты, созданные <br> под основные задачи</h2>
            </div>

            @php
                // Цены берём из админки (CardProduct.price_rub по key), картинка/текст остаются маркетинговым контентом страницы.
                $priceFor = fn (string $key) => isset($cardProducts[$key])
                    ? number_format((float) $cardProducts[$key]->price_rub, 0, ',', ' ')
                    : '—';
                // Маркированный список преимуществ тоже из админки (CardProduct.advantages, вкладка «Карта» в карточном продукте) —
                // готовый HTML из RichEditor, выводится как есть, стили в .product-advantages (app.css).
                $advantagesFor = fn (string $key) => $cardProducts[$key]->advantages ?? null;

                $products = [
                    [
                        'key'=>'black', 'class'=>'product-black', 'eyebrow'=>'Карта BLACK', 'title'=>'Для интернета. Подписок. Сервисов.',
                        'desc'=>'Главная карта для онлайн платежей. Оплата ИИ-сервисов, облачных платформ, рекламы, подписок и зарубежных интернет-магазинов',
                        'advantages'=>$advantagesFor('black'),
                        'bgImage'=>'blackcardbg.png',
                        'cta'=>'Оформить карту Black',
                        'price' => $priceFor('black'),
                    ],
                    [
                        'key'=>'orange', 'class'=>'product-orange', 'eyebrow'=>'Карта ORANGE', 'title'=>'Для путешествий. Телефона. Покупок.',
                        'desc'=>'Карта для жизни вне экрана. Добавляйте в Apple Pay и Google Pay, оплачивайте покупки телефоном или часами в кафе, ресторанах, отелях и магазинах.',
                        'advantages'=>$advantagesFor('orange'),
                        'bgImage'=>'orangecardbg.png',
                        'cta'=>'Оформить карту Orange',
                        'price' => $priceFor('orange'),
                    ],
                    [
                        'key'=>'white', 'class'=>'product-white', 'eyebrow'=>'Карта WHITE', 'title'=>'Универсальная',
                        'desc'=>'Универсальная платежная карта с минимальным пополнением. Добавляйте в Apple Pay, Google Pay, Samsung Pay, оплачивайте покупки телефоном или часами или обычным методом для онлайн сервисов и магазинов.',
                        'advantages'=>$advantagesFor('white'),
                        'bgImage'=>'whitecardbg.png', 'cta'=>'Оформить карту White',
                        'price' => $priceFor('white'),
                    ],
                ];

                // Порядок карточек на лендинге — как в админке (CardProduct.sort), а не как в этом массиве.
                // $cardProducts уже отсортирован по sort в routes/web.php, просто переставляем маркетинговый массив под него.
                $sortOrder = $cardProducts->keys()->flip();
                usort($products, fn (array $a, array $b) => ($sortOrder[$a['key']] ?? PHP_INT_MAX) <=> ($sortOrder[$b['key']] ?? PHP_INT_MAX));
            @endphp

            <div class="mt-16 space-y-8 lg:mt-24">
                @foreach ($products as $i => $product)
                    <article data-reveal style="--reveal-delay: {{ $i * 120 }}ms" class="product-panel {{ $product['class'] }}">
                        <div class="product-visual">
                            <img src="{{ asset('assets/images/'.$product['bgImage']) }}" alt="{{ $product['title'] }}" loading="lazy" class="absolute inset-0 h-full w-full object-cover">
                        </div>
                        <div class="product-copy">
                            <span class="eyebrow">{{ $product['eyebrow'] }}</span>
                            <h3 class="mt-5 text-[clamp(2.4rem,4.2vw,4.8rem)] font-medium leading-[.94] tracking-[-.045em]">{{ $product['title'] }}</h3>
                            <p class="mt-7 max-w-2xl text-lg leading-relaxed opacity-70">{{ $product['desc'] }}</p>
                            @if ($product['advantages'])
                                <div class="product-advantages mt-8">{!! $product['advantages'] !!}</div>
                            @endif
                            <div class="mt-10 flex flex-wrap items-center gap-5 border-t border-current/15 pt-7">
                                <a href="#apply" data-select-card="{{ $product['key'] }}" class="btn {{ in_array($product['key'], ['black', 'white']) ? 'btn-hero-orange' : 'btn-dark-on-orange' }}">{{ $product['cta'] }}</a>
                                <span class="product-price-note text-sm opacity-50">{{ $product['price'] }} ₽ за выпуск · 0 ₽ в месяц</span>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ================= HOW ================= --}}
    <section id="how" class="editorial-dark mt-[150px] px-6 py-28 lg:px-10 lg:py-40">
        <div class="mx-auto max-w-[1400px]">
            <div class="grid gap-10 lg:grid-cols-[1fr_.65fr] lg:items-end">
                <div data-reveal><span class="eyebrow !text-[#f37338]">Онлайн за 3 минуты</span><h2 class="mt-5 text-[clamp(2.8rem,5vw,5.4rem)] font-medium leading-[.94] tracking-[-.045em] text-white">Простое и быстрое оформление карт</h2></div>
                <div data-reveal>
                    <p class="text-lg leading-relaxed text-white/55">Никаких офисов и пластика. Всё необходимое - здесь.</p>
                    <a href="https://mne.mojno.cc/register" class="btn btn-hero-orange mt-8">Зарегистрироваться</a>
                </div>
            </div>
            <div class="mt-20 grid gap-px overflow-hidden rounded-[32px] bg-white/10 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ([['01','Зарегистрируйтесь','Создайте аккаунт или авторизуйтесь в один клик'],['02','Выберите карту','Выберите и выпустите карту, которая подходит под ваш запрос'],['03','Пополните баланс','Пополните баланс через СБП или картой российского банка.'],['04','Платите по миру','Оплачивайте зарубежные покупки <br>и сервисы уже сейчас']] as $i => $step)
                    <div data-reveal style="--reveal-delay: {{ $i * 90 }}ms" class="how-card"><span>{{ $step[0] }}</span><h3>{{ $step[1] }}</h3><p>{!! $step[2] !!}</p></div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ================= SERVICES ================= --}}
    <section id="features" class="mt-[150px] px-6 lg:px-10">
        <div class="mx-auto max-w-[1400px]">
            <div data-reveal class="max-w-4xl"><span class="eyebrow">Для любых ситуаций</span><h2 class="mt-5 text-[clamp(2.8rem,5vw,5.4rem)] font-medium leading-[.94] tracking-[-.045em]">Что оплатить?</h2></div>

            <div data-reveal class="service-wall mt-8 lg:mt-12">
                <ul>
                    @foreach (\App\Support\PaymentServiceLogos::all() as $service)
                        <li class="service-cell" style="--svc: {{ $service[0] }}">
                            <a>
                                <span class="service-cell-icon">
                                    <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">{!! $service[2] !!}</svg>
                                </span>
                                <span>{{ $service[1] }}</span>
                            </a>
                        </li>
                    @endforeach
                    <li class="service-cell service-more-cell">
                        <div class="service-more-inner">
                            <span class="service-cell-icon">
                                <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M4.5 3.75a3 3 0 0 0-3 3v.75h21v-.75a3 3 0 0 0-3-3h-15Z" clip-rule="evenodd"/>
                                    <path fill-rule="evenodd" d="M22.5 9.75h-21v7.5a3 3 0 0 0 3 3h15a3 3 0 0 0 3-3v-7.5Zm-18 3.75a.75.75 0 0 1 .75-.75h6a.75.75 0 0 1 0 1.5h-6a.75.75 0 0 1-.75-.75Zm.75 2.25a.75.75 0 0 0 0 1.5h3a.75.75 0 0 0 0-1.5h-3Z" clip-rule="evenodd"/>
                                </svg>
                            </span>
                            <span class="service-more-text">А также любой сайт в мире, где принимают Mastercard и Visa</span>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </section>

    {{-- ================= APP ================= --}}
    <section class="mt-[150px] px-6 lg:px-10">
        <div class="app-panel mx-auto max-w-[1400px] overflow-hidden rounded-[40px] bg-white">
            <div class="grid lg:grid-cols-[1fr_.75fr]">
                <div data-reveal class="px-5 py-10 sm:p-14 lg:p-20"><span class="eyebrow">Личный кабинет</span><h2 class="mt-5 max-w-2xl text-[clamp(2.8rem,5vw,5rem)] font-medium leading-[.94] tracking-[-.045em]">Удобный и простой личный кабинет</h2><p class="mt-7 max-w-xl text-lg leading-relaxed text-[#3A3C40]/60">Выпускайте карты в пару кликов, отслеживайте актуальный баланс и историю своих платежей из одного места.</p><div class="mt-9 grid gap-4 sm:grid-cols-2">@foreach(['Быстрый выпуск карт','История операций','Пополнение баланса из России','Push-уведомления'] as $item)<div class="rounded-2xl bg-[#f3f0ee] p-5 text-sm font-semibold">{{ $item }}</div>@endforeach</div><div class="mt-8 flex flex-wrap gap-4"><a href="https://mne.mojno.cc/register" class="btn btn-hero-orange">Зарегистрироваться</a><a href="https://mne.mojno.cc/" class="btn btn-hero-black">Авторизоваться</a></div></div>
                <div data-reveal class="app-visual" style="background-image:url('{{ asset('assets/images/iphone.png') }}');background-position:center;background-repeat:no-repeat;background-size: cover;"></div>
            </div>
        </div>
    </section>

    {{-- ================= APPLY ================= --}}
    <section id="apply" class="mt-[150px] px-6 lg:px-10">
        <div class="mx-auto max-w-[1400px]">
            <div data-reveal class="max-w-4xl">
                <span class="eyebrow">Получить карту</span>
                <h2 class="mt-5 text-[clamp(2.8rem,5vw,5.4rem)] font-medium leading-[.94] tracking-[-.045em]">Оформление карты онлайн</h2>
                <p class="mt-6 text-lg leading-relaxed text-[#3A3C40]/60">Можно использовать карту сразу</p>
            </div>

            @php
                $cardOptions = [
                    [
                        'key' => 'black', 'name' => 'Black', 'thumb' => 'blackcard.png', 'currency' => '$ USD',
                        'desc' => 'Для онлайн платежей, подписок и сервисов.',
                        'wallets' => null,
                    ],
                    [
                        'key' => 'orange', 'name' => 'Orange', 'thumb' => 'orangecard.png', 'currency' => '$ USD',
                        'desc' => 'Для оффлайн и онлайн покупок.',
                        'wallets' => 'Apple Pay · Google Pay',
                    ],
                    [
                        'key' => 'white', 'name' => 'White', 'thumb' => 'whitecard.png', 'currency' => '$ USD',
                        'desc' => 'Универсальная карта с повышенными лимитами. Для оффлайн и онлайн покупок.',
                        'wallets' => 'Apple Pay · Google Pay',
                    ],
                ];

                // Порядок карточек в форме оформления — тоже как в админке (CardProduct.sort), так же как в блоке #products выше.
                $applySortOrder = $cardProducts->keys()->flip();
                usort($cardOptions, fn (array $a, array $b) => ($applySortOrder[$a['key']] ?? PHP_INT_MAX) <=> ($applySortOrder[$b['key']] ?? PHP_INT_MAX));

                $tips = [
                    ['Мгновенный выпуск карты', 'Можно пользоваться сразу после пополнения баланса'],
                    ['Карты с ApplePay / GooglePay', 'Оплачивайте покупки телефоном или смарт-часами'],
                    ['Пополнение баланса из России', 'Мгновенное пополнение баланса российскими картами или через СБП.'],
                ];
            @endphp


            <div data-reveal class="apply-panel mt-16 grid lg:grid-cols-[1fr_2fr] lg:mt-16">
                <aside class="apply-sidebar">
                    <div class="apply-card-list" data-card-selector>
                        @foreach ($cardOptions as $i => $card)
                            @php $cardProduct = $cardProducts[$card['key']] ?? null; @endphp
                            <label class="apply-card-option {{ $i === 0 ? 'is-active' : '' }}">
                                <input
                                    type="radio"
                                    name="card_product"
                                    value="{{ $card['key'] }}"
                                    class="sr-only"
                                    {{ $i === 0 ? 'checked' : '' }}
                                    data-price-rub="{{ $cardProduct->price_rub ?? 0 }}"
                                    data-fee-percent="{{ $cardProduct->provider_topup_fee_percent ?? 0 }}"
                                    data-product-id="{{ $cardProduct->id ?? '' }}"
                                    data-issue-min="{{ $cardProduct->issue_min_amount ?? '' }}"
                                    data-issue-max="{{ $cardProduct->issue_max_amount ?? '' }}"
                                    data-product-name="{{ $card['name'] }}"
                                >
                                <span class="apply-card-thumb {{ $card['thumb'] ? '' : 'apply-card-thumb-white' }}">
                                    @if ($card['thumb'])
                                        <img src="{{ asset('assets/images/'.$card['thumb']) }}" alt="Карта {{ $card['name'] }}" loading="lazy">
                                    @endif
                                </span>
                                <span class="apply-card-info">
                                    <span class="apply-card-name">Карта {{ $card['name'] }} <span class="apply-card-badge">{{ $card['currency'] }}</span></span>
                                    <span class="apply-card-desc">{{ $card['desc'] }}</span>
                                    @if ($card['wallets'])
                                        <span class="apply-card-wallets">{{ $card['wallets'] }}</span>
                                    @endif
                                    <span class="apply-card-price">{{ $priceFor($card['key']) }} ₽ / 0 ₽ в мес.</span>
                                </span>
                            </label>
                        @endforeach
                    </div>

                    <div class="apply-tip" data-rotating-tip>
                        @foreach ($tips as $i => $tip)
                            <div class="apply-tip-item {{ $i === 0 ? 'is-active' : '' }}" data-tip>
                                <span class="apply-tip-title">{{ $tip[0] }}</span>
                                <p class="apply-tip-text">{{ $tip[1] }}</p>
                            </div>
                        @endforeach
                    </div>
                </aside>

                <div class="apply-form-wrap" data-apply-wrap>
                    <form data-apply-form novalidate class="apply-step is-current" data-apply-step="register">
                        <div class="apply-field">
                            <label for="apply-fio">Введите ваше ФИО</label>
                            <input type="text" id="apply-fio" name="fio" data-translit-input autocomplete="name" placeholder="Ivanov Ivan Ivanovich" required>
                            <p class="apply-hint">Можно на русском. Автоматический перевод.</p>
                        </div>
                        <div class="apply-grid-2">
                            <div class="apply-field">
                                <label for="apply-dob">Дата рождения</label>
                                <input type="text" id="apply-dob" name="birth_date" inputmode="numeric" data-dob-input placeholder="дд.мм.гггг" maxlength="10" required>
                            </div>
                            <div class="apply-field">
                                <label for="apply-phone">Мобильный телефон</label>
                                <input type="tel" id="apply-phone" name="phone" inputmode="numeric" data-phone-input placeholder="+7 (___) ___-__-__" maxlength="18" required>
                            </div>
                        </div>
                        <div class="apply-field">
                            <label for="apply-email">Электронная почта</label>
                            <input type="email" id="apply-email" name="email" autocomplete="email" placeholder="ivan@gmail.com" required>
                        </div>
                        <label class="apply-consent">
                            <input type="checkbox" name="consent" required>
                            <span>Регистрируясь, я соглашаюсь с <a href="{{ route('legal.offer') }}" target="_blank" rel="noopener">публичной офертой</a>, <a href="{{ route('legal.privacy-policy') }}" target="_blank" rel="noopener">политикой конфиденциальности</a> и <a href="{{ route('legal.kyc-aml') }}" target="_blank" rel="noopener">политикой KYC/AML</a>, а также даю <a href="{{ route('legal.personal-data-consent') }}" target="_blank" rel="noopener">согласие на обработку моих данных</a></span>
                        </label>
                        <p class="apply-error" data-apply-error hidden></p>
                        <button type="submit" class="btn btn-hero-orange apply-submit">
                            Зарегистрироваться и пополнить
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
                        </button>
                    </form>

                    <div class="apply-step apply-loading" data-apply-step="loading" role="status" aria-live="polite">
                        <span class="apply-loading-spinner" aria-hidden="true"></span>
                        <p class="apply-loading-title">Регистрируем аккаунт</p>
                        <p class="apply-loading-text">Сейчас откроем личный кабинет для выпуска карты...</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ================= FAQ ================= --}}
    <section id="faq" class="mt-[150px] px-6 lg:px-10">
        <div class="mx-auto max-w-[1100px]">
            <div data-reveal class="max-w-4xl">
                <span class="eyebrow">Вопросы</span>
                <h2 class="mt-5 text-[clamp(2.8rem,5vw,5.2rem)] font-medium leading-[.94] tracking-[-.045em]">Перед выпуском карты</h2>
            </div>
            @php
                $faqs=[
                    ['На чье имя выпускаются карты?','Все карты выпускаются на ваше имя, указанное в анкете при регистрации аккаунта.'],
                    ['Сколько стоит обслуживание карты?','Обслуживание всех наших карт - бесплатное.'],
                    ['Как оформить карту?','Зарегистрируйте аккаунт и выпускайте карты прямо из личного кабинета в режиме онлайн. Без посещений офиса и личных встреч.'],
                    ['Как быстро я смогу начать пользоваться картой?','Сразу после выпуска карты и пополнения баланса. Реквизиты карты вы увидите в личном кабинете.'],
                    ['Как пополнять карту из России?','Через СБП или любой картой российского банка. Рубли автоматически сконвертируются в валюту карты по актуальному курсу.'],
                    ['Можно платить телефоном внутри РФ?','Нет. Российские банки не принимают карты международных платежных систем. Бесконтактная оплата телефоном или смарт-часами будет отлично работать в поездках за границей. Список санкционных стран будет доступен в подробной информации о карте в вашем личном кабинете.'],
                    ['Сколько карт можно оформить?','Ограничений на количество карт нет. Вы можете выпустить несколько карт для разных задач на ваше усмотрение.']
                    ];
                @endphp
            <div class="mt-14 divide-y divide-[#3A3C40]/10 border-y border-[#3A3C40]/10">
                @foreach($faqs as $faq)
                    <div data-faq-item data-open="false" class="faq-item">
                        <button type="button" data-faq-button aria-expanded="false" class="flex w-full items-center justify-between gap-6 py-7 text-left">
                            <span class="text-xl font-semibold tracking-[-.02em]">{{ $faq[0] }}</span>
                            <span class="faq-plus">+</span>
                        </button>
                        <div class="faq-answer">
                            <p class="max-w-3xl pb-7 pr-12 text-base leading-relaxed text-[#3A3C40]/55">{{ $faq[1] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
</main>

@include('partials.site-footer')
</body>
</html>
