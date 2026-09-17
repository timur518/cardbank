<!DOCTYPE html>
<html lang="ru" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>[Бренд] — карта для платежей за пределами России</title>
    <meta name="description" content="Виртуальная карта Mastercard и Visa для оплаты сервисов, подписок и покупок за рубежом. Пополнение из России через СБП, полный контроль — в приложении.">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=sofia-sans:400,500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">

    {{-- ================= ШАПКА ================= --}}
    <header data-site-header class="sticky top-0 z-50 px-4 pb-1 pt-4">
        <div class="nav-pill mx-auto flex max-w-[1180px] items-center justify-between rounded-full px-5 py-3 sm:px-7">
            <a href="#top" class="text-lg font-extrabold tracking-tight text-[#141413]">[Бренд]</a>

            <nav class="hidden items-center gap-7 text-[15px] font-medium text-[#141413]/70 lg:flex">
                <a href="#lifestyle" class="transition hover:text-[#141413]">Возможности</a>
                <a href="#products" class="transition hover:text-[#141413]">Карты</a>
                <a href="#partners" class="transition hover:text-[#141413]">Партнёрам</a>
                <a href="#faq" class="transition hover:text-[#141413]">Вопросы</a>
                <a href="#support" class="transition hover:text-[#141413]">Поддержка</a>
            </nav>

            <div class="hidden lg:flex">
                <a href="#" class="btn btn-primary text-sm">Войти в аккаунт</a>
            </div>

            <button data-menu-toggle type="button" aria-expanded="false" aria-controls="mobile-menu" class="flex h-9 w-9 items-center justify-center rounded-full text-[#141413] lg:hidden">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h16" /></svg>
            </button>
        </div>

        <div data-mobile-menu id="mobile-menu" class="nav-pill mx-auto mt-2 hidden max-w-[1180px] flex-col gap-1 rounded-3xl px-5 py-4 lg:hidden">
            <a href="#lifestyle" class="rounded-xl px-3 py-2.5 text-sm font-medium text-[#141413]">Возможности</a>
            <a href="#products" class="rounded-xl px-3 py-2.5 text-sm font-medium text-[#141413]">Карты</a>
            <a href="#partners" class="rounded-xl px-3 py-2.5 text-sm font-medium text-[#141413]">Партнёрам</a>
            <a href="#faq" class="rounded-xl px-3 py-2.5 text-sm font-medium text-[#141413]">Вопросы</a>
            <a href="#support" class="rounded-xl px-3 py-2.5 text-sm font-medium text-[#141413]">Поддержка</a>
            <a href="#" class="btn btn-primary mt-2 justify-center text-sm">Войти в аккаунт</a>
        </div>
    </header>

    <main id="top" class="bg-[#f3f0ee]">
        {{-- ================= HERO — фото внутри скруглённого блока шириной с контент, как на mastercard.com ================= --}}
        <section class="px-6 pt-0 lg:px-10">
            <div class="mx-auto max-w-[1320px]">
                <div class="relative isolate flex h-[460px] items-end overflow-hidden rounded-[32px] sm:h-[560px] lg:h-[700px] lg:rounded-[40px]">
                    <img
                        src="https://web.archive.org/web/20260913045546im_/https://www.mastercard.com/adobe/dynamicmedia/deliver/dm-aid--f200757c-5753-49b3-b3e6-ef7b8b347072/us-summer-travel-hero.jpg?quality=82&preferwebp=true"
                        alt="Платежи без границ"
                        class="absolute inset-0 h-full w-full object-cover"
                    >
                    <div class="absolute inset-0 bg-gradient-to-t from-[#141413]/90 via-[#141413]/25 to-[#141413]/0"></div>

                    <div class="relative w-full px-6 pb-10 sm:px-10 sm:pb-12 lg:px-14 lg:pb-16">
                        <div data-reveal class="max-w-[620px]">
                            <span class="eyebrow !text-white/80">[Бренд]</span>
                            <h1 class="mt-5 text-[clamp(2.5rem,5.5vw,4.5rem)] font-medium leading-[0.98] tracking-[-0.03em] text-white">
                                Платежи без границ
                            </h1>
                            <p class="mt-6 max-w-lg text-lg leading-relaxed text-white/80">
                                Mastercard и Visa для оплаты сервисов, подписок и покупок за рубежом —
                                оформляется за минуты и пополняется из России через СБП.
                            </p>

                            <div class="mt-8 flex flex-wrap items-center gap-4">
                                <a href="#products" class="btn btn-ondark">Оформить карту онлайн</a>
                                <a href="#" class="btn btn-outline-ondark">Войти в аккаунт</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ================= ОБРАЗ ЖИЗНИ — слайдер сценариев, как на mastercard.com ================= --}}
        <section id="lifestyle" class="py-28 lg:py-36" data-carousel>
            <div class="mx-auto flex max-w-[1320px] flex-col gap-6 px-6 lg:flex-row lg:items-end lg:justify-between lg:px-10">
                <div data-reveal>
                    <span class="eyebrow">Где это работает</span>
                    <h2 class="mt-4 text-[clamp(2.25rem,4vw,3.5rem)] font-medium leading-[1.05] tracking-[-0.02em] text-[#141413]">
                        Одна карта на всю жизнь за рубежом
                    </h2>
                </div>
                <div class="flex items-center gap-4">
                    <p class="max-w-xs text-base leading-relaxed text-[#141413]/55">От ИИ-подписки до отеля в отпуске — платите одинаково просто.</p>
                    <div class="hidden shrink-0 items-center gap-3 lg:flex">
                        <button type="button" data-carousel-prev aria-label="Предыдущий слайд" class="carousel-arrow">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M15 6l-6 6 6 6"/></svg>
                        </button>
                        <button type="button" data-carousel-next aria-label="Следующий слайд" class="carousel-arrow">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 6l6 6-6 6"/></svg>
                        </button>
                    </div>
                </div>
            </div>

            @php
                $scenes = [
                    [
                        'tag' => 'Онлайн-шоппинг',
                        'title' => 'Покупки в зарубежных онлайн-магазинах',
                        'img' => 'https://web.archive.org/web/20260913045549im_/https://www.mastercard.com/adobe/dynamicmedia/deliver/dm-aid--efc24a51-bf03-4d56-a493-964b269c6aeb/br45724-mastercard-websiteimageryrefresh-solutions-3-9x16.jpg?quality=82&preferwebp=true',
                    ],
                    [
                        'tag' => 'ИИ-сервисы',
                        'title' => 'Оплата любых ИИ-сервисов и подписок',
                        'img' => 'https://web.archive.org/web/20260913045546im_/https://www.mastercard.com/adobe/dynamicmedia/deliver/dm-aid--e6176780-42de-4e72-b550-906d08a82da0/agentic-commerce-standards-hero.jpg?quality=82&preferwebp=true',
                    ],
                    [
                        'tag' => 'На кассе',
                        'title' => 'Apple Pay и Google Pay на кассе',
                        'img' => 'https://web.archive.org/web/20260913045546im_/https://www.mastercard.com/adobe/dynamicmedia/deliver/dm-aid--31deb226-0992-429e-8fae-9bccb16fc665/br45724-mastercard-websiteimageryrefresh-solutions-4-1x1.jpg?quality=82&preferwebp=true',
                    ],
                    [
                        'tag' => 'Кафе и рестораны',
                        'title' => 'Платежи в кафе и ресторанах',
                        'img' => 'https://images.unsplash.com/photo-1758519289594-8e0444825b04?q=80&w=1200&auto=format&fit=crop',
                    ],
                    [
                        'tag' => 'Путешествия',
                        'title' => 'Отели и гостиницы за рубежом',
                        'img' => 'https://images.unsplash.com/photo-1773393776477-61773dfc8a09?q=80&w=1600&auto=format&fit=crop',
                    ],
                ];
            @endphp

            <div data-carousel-track class="carousel-track mt-14 flex snap-x snap-mandatory gap-6 overflow-x-auto px-6 pb-6 lg:px-10">
                @foreach ($scenes as $i => $scene)
                    <article data-reveal style="--reveal-delay: {{ $i * 90 }}ms" class="group relative isolate w-[86%] flex-shrink-0 snap-center overflow-hidden rounded-[56px] shadow-[0_60px_100px_-40px_rgba(20,20,19,0.45)] sm:w-[80%] lg:w-[72%]">
                        <div class="aspect-[4/5]">
                            <img src="{{ $scene['img'] }}" alt="{{ $scene['title'] }}" loading="lazy" class="h-full w-full object-cover transition duration-700 ease-out group-hover:scale-105">
                        </div>
                        <div class="absolute inset-0 bg-gradient-to-t from-[#141413]/85 via-[#141413]/5 to-transparent"></div>
                        <span class="absolute left-6 top-6 rounded-full bg-white/90 px-4 py-1.5 text-xs font-semibold text-[#141413] backdrop-blur">{{ $scene['tag'] }}</span>
                        <p class="absolute inset-x-6 bottom-6 text-2xl font-semibold leading-snug text-white">{{ $scene['title'] }}</p>
                    </article>
                @endforeach
            </div>
        </section>

        {{-- ================= ДВА ПРОДУКТА: CARD BLACK / CARD ORANGE ================= --}}
        <section id="products" class="px-6 py-28 lg:px-10 lg:py-36">
            <div class="mx-auto max-w-[1320px]">
                <div data-reveal class="max-w-3xl">
                    <span class="eyebrow">Карты</span>
                    <h2 class="mt-4 text-[clamp(2.25rem,4vw,3.5rem)] font-medium leading-[1.05] tracking-[-0.02em] text-[#141413]">Виртуальные карты для платежей и покупок</h2>
                    <p class="mt-5 text-xl font-medium leading-snug text-[#141413]/80">Удобный способ оплачивать подписки и покупки по всему миру</p>
                    <p class="mt-3 text-base leading-relaxed text-[#141413]/55">От подписки на ИИ-сервис до отеля в отпуске — карты работают одинаково просто</p>
                </div>

                <div class="mt-14 grid gap-8 lg:grid-cols-2">
                    @php
                        $products = [
                            [
                                'eyebrow' => 'Card Black',
                                'title' => 'Для оплаты в интернете',
                                'gradient' => 'from-[#2b2a28] to-[#141413]',
                                'text' => 'text-white',
                                'desc' => 'Подписки, облачные и ИИ-сервисы, зарубежные онлайн-магазины, реклама и любые другие сайты, принимающие карты — без физического пластика, реквизиты сразу в приложении.',
                                'points' => [
                                    'Выпуск за минуты, полностью онлайн',
                                    'Мгновенное пополнение через СБП',
                                    'Отдельная карта под каждую задачу',
                                ],
                                'network' => 'Mastercard',
                                'digits' => '4821',
                                'badges' => null,
                                'cta' => 'Оформить Card Black',
                            ],
                            [
                                'eyebrow' => 'Card Orange',
                                'title' => 'Для онлайн- и офлайн-покупок с Apple Pay и Google Pay',
                                'gradient' => 'from-[#f37338] to-[#9a3a0a]',
                                'text' => 'text-white',
                                'desc' => 'Добавляется в Apple Pay и Google Pay — прикладываете телефон или часы к терминалу в кафе, ресторане или отеле за границей, как обычной картой.',
                                'points' => [
                                    'Бесконтактная оплата по NFC за границей',
                                    'Работает с Apple Watch и Wear OS',
                                    'Продавец получает токен, а не номер карты',
                                ],
                                'network' => 'VISA',
                                'digits' => '5678',
                                'badges' => ['Pay', 'G Pay'],
                                'cta' => 'Оформить Card Orange',
                            ],
                        ];
                    @endphp

                    @foreach ($products as $i => $product)
                        <article data-reveal style="--reveal-delay: {{ $i * 120 }}ms" class="card-lift group overflow-hidden rounded-[32px] bg-white shadow-[0_50px_100px_-40px_rgba(20,20,19,0.3)]">
                            <div class="relative flex h-72 items-center justify-center overflow-hidden bg-gradient-to-br {{ $product['gradient'] }} p-8">
                                <div class="w-full max-w-[300px] rounded-[24px] bg-white/10 p-6 {{ $product['text'] }} backdrop-blur-sm transition duration-500 ease-out group-hover:-translate-y-1.5 group-hover:scale-[1.02]">
                                    <div class="flex items-start justify-between">
                                        <div class="h-8 w-11 rounded-md bg-gradient-to-br from-amber-100 to-amber-300"></div>
                                        @if ($product['badges'])
                                            <div class="flex gap-1.5">
                                                @foreach ($product['badges'] as $badge)
                                                    <span class="rounded-full bg-white/20 px-2.5 py-1 text-[11px] font-semibold backdrop-blur"> {{ $badge }}</span>
                                                @endforeach
                                            </div>
                                        @else
                                            <p class="text-sm font-bold tracking-wide">{{ $product['network'] }}</p>
                                        @endif
                                    </div>
                                    <p class="mt-10 font-mono text-lg tracking-widest">•••• •••• •••• {{ $product['digits'] }}</p>
                                    @if ($product['badges'])
                                        <p class="mt-6 text-right text-sm font-bold italic text-white/80">{{ $product['network'] }}</p>
                                    @endif
                                </div>
                            </div>

                            <div class="p-8 lg:p-10">
                                <span class="eyebrow">{{ $product['eyebrow'] }}</span>
                                <h3 class="mt-3 text-2xl font-medium leading-snug tracking-[-0.01em] text-[#141413]">{{ $product['title'] }}</h3>
                                <p class="mt-4 text-base leading-relaxed text-[#141413]/60">{{ $product['desc'] }}</p>

                                <ul class="mt-6 space-y-3 text-sm text-[#141413]/75">
                                    @foreach ($product['points'] as $point)
                                        <li class="flex items-start gap-3">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-4 w-4 flex-shrink-0 text-[#cf4500]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 13l4 4L19 7"/></svg>
                                            {{ $point }}
                                        </li>
                                    @endforeach
                                </ul>

                                <div class="mt-8 flex flex-wrap items-center justify-between gap-4 border-t border-[#e8e5e1] pt-6">
                                    <span class="text-sm text-[#141413]/45">[X ₽] за выпуск · 0 ₽ в месяц</span>
                                    <a href="#" class="btn btn-primary text-sm">{{ $product['cta'] }}</a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ================= КАК ЭТО РАБОТАЕТ ================= --}}
        <section id="how" class="px-6 py-28 lg:px-10 lg:py-36">
            <div class="mx-auto max-w-[1320px]">
                <div data-reveal class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <span class="eyebrow">Как это устроено</span>
                        <h2 class="mt-4 text-[clamp(2.25rem,4vw,3.5rem)] font-medium leading-[1.05] tracking-[-0.02em] text-[#141413]">Четыре шага до первой оплаты</h2>
                    </div>
                    <p class="max-w-xs text-base leading-relaxed text-[#141413]/55 lg:pb-2">От регистрации до оплаты — без визита в офис.</p>
                </div>

                <div class="relative mt-20 grid gap-12 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="pointer-events-none absolute left-0 right-0 top-7 hidden h-px bg-[#e8e5e1] lg:block"></div>
                    @php
                        $steps = [
                            ['n' => '01', 't' => 'Регистрация и проверка личности', 'd' => 'Указываете данные и проходите проверку — как при оформлении любой банковской карты.'],
                            ['n' => '02', 't' => 'Выбор карты', 'd' => 'Card Black для оплаты в интернете или Card Orange с Apple Pay и Google Pay для поездок и оплаты на кассе.'],
                            ['n' => '03', 't' => 'Пополнение через СБП', 'd' => 'Курс и итоговая сумма видны до подтверждения перевода и не меняются после оплаты.'],
                            ['n' => '04', 't' => 'Оплата за границей', 'd' => 'Реквизиты — в приложении, карта — в Apple Pay и Google Pay. Платите на сайте или прикладываете телефон к терминалу.'],
                        ];
                    @endphp
                    @foreach ($steps as $i => $step)
                        <div data-reveal style="--reveal-delay: {{ $i * 100 }}ms" class="relative">
                            <span class="relative z-10 flex h-14 w-14 items-center justify-center rounded-full border border-[#e8e5e1] bg-[#f3f0ee] text-base font-bold text-[#cf4500]">{{ $step['n'] }}</span>
                            <h3 class="mt-6 text-lg font-semibold text-[#141413]">{{ $step['t'] }}</h3>
                            <p class="mt-3 text-base leading-relaxed text-[#141413]/60">{{ $step['d'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ================= ИНФРАСТРУКТУРА ================= --}}
        <section id="features" class="px-6 py-28 lg:px-10 lg:py-36">
            <div class="mx-auto max-w-[1320px]">
                <div data-reveal class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <span class="eyebrow">Надёжность</span>
                        <h2 class="mt-4 max-w-2xl text-[clamp(2.25rem,4vw,3.5rem)] font-medium leading-[1.05] tracking-[-0.02em] text-[#141413]">Инфраструктура, а не один поставщик</h2>
                    </div>
                    <p class="max-w-xs text-base leading-relaxed text-[#141413]/55 lg:pb-2">Несколько партнёров и контроль каждой операции — без единой точки отказа.</p>
                </div>

                <div class="mt-16 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    @php
                        $infra = [
                            ['icon' => 'nodes', 't' => 'Резервная эмиссия', 'd' => 'Если один из партнёров-эмитентов временно недоступен, система переключает выпуск и обслуживание карт на резервного — незаметно для держателя карты.'],
                            ['icon' => 'shield', 't' => 'Контроль каждой операции', 'd' => 'Каждый платёж проходит через внутреннюю систему риск-правил — аномалии видны раньше, чем по ним успевает среагировать платёжная система.'],
                            ['icon' => 'lock', 't' => 'Реквизиты не хранятся', 'd' => 'Номер карты и CVV не лежат в нашей базе — они запрашиваются у эмитента в момент, когда держатель открывает их в приложении.'],
                            ['icon' => 'pulse', 't' => 'Баланс без задержек', 'd' => 'Баланс в приложении сверяется с провайдером в реальном времени — расхождений между тем, что потрачено, и тем, что показано, не бывает.'],
                        ];
                    @endphp
                    @foreach ($infra as $i => $f)
                        <div data-reveal style="--reveal-delay: {{ $i * 90 }}ms" class="rounded-3xl border border-[#e8e5e1] bg-white p-7">
                            <span class="flex h-12 w-12 items-center justify-center rounded-full bg-[#f37338]/10 text-[#cf4500]">
                                @switch($f['icon'])
                                    @case('nodes')
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"><circle cx="6" cy="6" r="2"/><circle cx="18" cy="6" r="2"/><circle cx="12" cy="18" r="2"/><path d="M7.6 7.6l3.2 8.8M16.4 7.6l-3.2 8.8"/></svg>
                                        @break
                                    @case('shield')
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"><path d="M12 3l7 3v6c0 4.5-3 7.5-7 9-4-1.5-7-4.5-7-9V6l7-3z"/></svg>
                                        @break
                                    @case('lock')
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/></svg>
                                        @break
                                    @case('pulse')
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12h4l2-6 4 12 2-6h6"/></svg>
                                        @break
                                @endswitch
                            </span>
                            <h3 class="mt-6 text-lg font-semibold text-[#141413]">{{ $f['t'] }}</h3>
                            <p class="mt-3 text-base leading-relaxed text-[#141413]/60">{{ $f['d'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ================= ЛИЧНЫЙ КАБИНЕТ ================= --}}
        <section class="px-6 py-28 lg:px-10 lg:py-36">
            <div class="mx-auto grid max-w-[1320px] items-center gap-16 rounded-[32px] bg-white p-10 lg:grid-cols-2 lg:p-16">
                <div data-reveal class="order-2 lg:order-1">
                    <span class="eyebrow">Личный кабинет</span>
                    <h2 class="mt-4 text-[clamp(2.25rem,4vw,3.5rem)] font-medium leading-[1.05] tracking-[-0.02em] text-[#141413]">Управление картой — полностью в приложении</h2>
                    <dl class="mt-9 space-y-7">
                        @foreach ([
                            ['t' => 'Несколько карт под разные задачи', 'd' => 'Отдельная карта на подписки, отдельная — на рекламу или поездку: расходы не смешиваются.'],
                            ['t' => 'История операций', 'd' => 'Каждая операция — с суммой в валюте карты и в рублях, статусом и временем.'],
                            ['t' => 'Заморозка и закрытие в одно касание', 'd' => 'Подозрительная активность или карта больше не нужна — статус меняется мгновенно, без обращения в поддержку.'],
                            ['t' => 'Выписка по запросу', 'd' => 'Формируется за один тап и подходит для личной отчётности.'],
                            ['t' => 'Push-уведомления', 'd' => 'О каждом списании и пополнении — в момент операции.'],
                        ] as $item)
                            <div class="flex gap-4">
                                <span class="mt-2 h-2 w-2 flex-shrink-0 rounded-full bg-[#f37338]"></span>
                                <div>
                                    <dt class="text-lg font-semibold text-[#141413]">{{ $item['t'] }}</dt>
                                    <dd class="mt-1.5 text-base leading-relaxed text-[#141413]/60">{{ $item['d'] }}</dd>
                                </div>
                            </div>
                        @endforeach
                    </dl>
                </div>

                <div data-reveal style="--reveal-delay:150ms" class="order-1 mx-auto w-full max-w-[320px] lg:order-2">
                    <div class="rounded-[36px] border border-[#141413]/10 bg-[#141413] p-3 shadow-2xl">
                        <div class="rounded-[26px] bg-[#0e0e0d] px-5 pb-6 pt-8">
                            <p class="text-xs text-white/40">Баланс</p>
                            <p class="mt-1 text-2xl font-bold text-white">$1,000.00</p>

                            <div class="relative mt-6 h-[110px]">
                                <div class="absolute left-6 top-3 w-[190px] rotate-3 rounded-2xl bg-gradient-to-br from-[#f37338] to-[#9a3a0a] p-4 text-white">
                                    <p class="font-mono text-xs tracking-widest">•••• 5678</p>
                                    <p class="mt-4 text-[10px] font-bold italic">VISA</p>
                                </div>
                                <div class="absolute left-0 top-0 w-[190px] -rotate-3 rounded-2xl bg-gradient-to-br from-[#2b2a28] to-[#141413] p-4 text-white shadow-lg">
                                    <p class="font-mono text-xs tracking-widest">•••• 4821</p>
                                    <p class="mt-4 text-[10px] font-bold">Mastercard</p>
                                </div>
                            </div>

                            <p class="mt-6 text-[11px] font-semibold uppercase tracking-wider text-white/40">Операции</p>
                            <div class="mt-3 space-y-3">
                                @foreach ([
                                    ['n' => 'Пополнение · СБП', 'a' => '+$44.48', 'c' => 'text-[#f5a877]'],
                                    ['n' => 'Подписка · сервис', 'a' => '−$20.00', 'c' => 'text-white/70'],
                                    ['n' => 'Реклама · инструмент', 'a' => '−$11.99', 'c' => 'text-white/70'],
                                ] as $tx)
                                    <div class="flex items-center justify-between border-b border-white/5 pb-3 text-sm">
                                        <span class="text-white/70">{{ $tx['n'] }}</span>
                                        <span class="font-medium {{ $tx['c'] }}">{{ $tx['a'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ================= ПОПОЛНЕНИЕ ================= --}}
        <section class="px-6 py-28 lg:px-10 lg:py-36">
            <div class="mx-auto grid max-w-[1320px] items-center gap-16 rounded-[32px] bg-white p-10 lg:grid-cols-2 lg:p-16">
                <div data-reveal>
                    <span class="eyebrow">Пополнение</span>
                    <h2 class="mt-4 text-[clamp(2.25rem,4vw,3.5rem)] font-medium leading-[1.05] tracking-[-0.02em] text-[#141413]">Курс и сумма — известны заранее</h2>
                    <p class="mt-5 max-w-md text-lg leading-relaxed text-[#141413]/60">
                        Пополняете через СБП или картой российского банка. Курс конвертации и
                        комиссия показываются до подтверждения и фиксируются в момент оплаты —
                        сумма после этого не меняется.
                    </p>
                </div>

                <div data-reveal style="--reveal-delay:150ms" class="mx-auto w-full max-w-sm rounded-3xl border border-[#e8e5e1] bg-[#f3f0ee] p-8">
                    <p class="text-xs font-semibold uppercase tracking-wider text-[#141413]/40">Пополнить баланс</p>

                    <div class="mt-5 flex items-center justify-between rounded-xl border border-[#f37338]/30 bg-[#f37338]/10 px-4 py-3">
                        <span class="text-sm font-medium text-[#141413]">Карта •• 5678</span>
                        <span class="text-sm text-[#141413]/50">$100,00</span>
                    </div>

                    <div class="mt-4 flex items-baseline justify-between">
                        <span class="text-4xl font-bold text-[#141413]">100</span>
                        <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-[#141413]/60">$ / ₽</span>
                    </div>
                    <p class="mt-1 text-xs text-[#141413]/40">Курс: 1 $ ≈ 100,53 ₽</p>

                    <div class="mt-6 space-y-2 border-t border-[#e8e5e1] pt-5 text-sm">
                        <div class="flex justify-between text-[#141413]/60">
                            <span>Зачислим на карту •• 5678</span>
                            <span>$100,00</span>
                        </div>
                        <div class="flex justify-between text-base font-bold text-[#141413]">
                            <span>Итого к оплате</span>
                            <span>10 053 ₽</span>
                        </div>
                    </div>

                    <button type="button" class="btn btn-primary mt-7 w-full">Перейти к оплате</button>
                </div>
            </div>
        </section>

        {{-- ================= ПАРТНЁРСКАЯ ПРОГРАММА ================= --}}
        <section id="partners" class="px-6 py-28 lg:px-10 lg:py-36">
            <div class="mx-auto max-w-[1320px]">
                <div data-reveal class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <span class="eyebrow">Партнёрам</span>
                        <h2 class="mt-4 max-w-2xl text-[clamp(2.25rem,4vw,3.5rem)] font-medium leading-[1.05] tracking-[-0.02em] text-[#141413]">Партнёрская программа без ручных сверок</h2>
                    </div>
                    <p class="max-w-xs text-base leading-relaxed text-[#141413]/55 lg:pb-2">Приглашённый пользователь закрепляется навсегда, начисления видны сразу.</p>
                </div>

                <div class="mt-14 grid gap-6 sm:grid-cols-3">
                    @foreach ([
                        ['v' => '[X]%', 'l' => 'с каждого пополнения приглашённого пользователя'],
                        ['v' => '[X]%', 'l' => 'с выпуска карты приглашённым — с первой и со всех следующих'],
                        ['v' => '24/7', 'l' => 'начисления считаются автоматически и видны сразу'],
                    ] as $i => $stat)
                        <div data-reveal style="--reveal-delay: {{ $i * 90 }}ms" class="rounded-3xl border border-[#e8e5e1] bg-white p-9">
                            <p class="text-4xl font-bold text-[#cf4500]">{{ $stat['v'] }}</p>
                            <p class="mt-3 text-base leading-relaxed text-[#141413]/60">{{ $stat['l'] }}</p>
                        </div>
                    @endforeach
                </div>

                <a href="#" data-reveal class="btn btn-primary mt-12">Получить партнёрскую ссылку</a>
            </div>
        </section>

        {{-- ================= БЕЗОПАСНОСТЬ ================= --}}
        <section id="security" class="mx-4 rounded-[32px] bg-[#141413] px-6 py-28 text-white sm:mx-8 lg:px-10 lg:py-36">
            <div class="mx-auto max-w-4xl text-center">
                <span class="eyebrow justify-center !text-[#f37338]">Безопасность</span>
                <h2 class="mt-4 text-[clamp(2.25rem,4vw,3.5rem)] font-medium leading-[1.05] tracking-[-0.02em]">Стандарты, которые мы не готовы снижать</h2>

                <div class="mt-16 grid gap-9 text-left sm:grid-cols-2">
                    @foreach ([
                        'Карты выпускает партнёр-эмитент по лицензии платёжной системы',
                        'Проверка личности при выпуске карты',
                        'Мониторинг операций по внутренним риск-правилам в реальном времени',
                        'Реквизиты карты доступны только держателю и не хранятся в открытом виде',
                    ] as $i => $point)
                        <div data-reveal style="--reveal-delay: {{ $i * 90 }}ms" class="flex items-start gap-4 border-t border-white/10 pt-7">
                            <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-5 w-5 flex-shrink-0 text-[#f37338]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M5 13l4 4L19 7"/></svg>
                            <p class="text-base leading-relaxed text-white/75">{{ $point }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ================= FAQ ================= --}}
        <section id="faq" class="px-6 py-28 lg:px-10 lg:py-36">
            <div class="mx-auto max-w-3xl">
                <div data-reveal class="text-center">
                    <span class="eyebrow justify-center">Вопросы</span>
                    <h2 class="mt-4 text-[clamp(2.25rem,4vw,3.5rem)] font-medium leading-[1.05] tracking-[-0.02em] text-[#141413]">Вопросы, которые обычно задают перед выпуском карты</h2>
                </div>

                <div class="mt-14 divide-y divide-[#e8e5e1] border-y border-[#e8e5e1]">
                    @php
                        $faqs = [
                            ['q' => 'Это законно?', 'a' => 'Карту выпускает партнёр-эмитент по лицензии платёжной системы, вы становитесь её держателем — как при оформлении любой банковской карты.'],
                            ['q' => 'Что если платёж не проходит?', 'a' => 'В приложении видна причина отказа — чаще всего это ограничение по региону сервиса или недостаточный баланс на карте. Поддержка разбирает конкретный случай.'],
                            ['q' => 'Где карта не сработает?', 'a' => 'Это виртуальная карта, наличные с неё снять нельзя. Внутри России для повседневных платежей она не нужна.'],
                            ['q' => 'Какие данные нужны для выпуска?', 'a' => 'Только то, что требует партнёр-эмитент: ФИО, дата рождения и контакты. Номер карты и CVV мы не храним.'],
                            ['q' => 'Как быстро приходят реквизиты?', 'a' => 'После подтверждения выпуска — в течение нескольких минут, прямо в приложении.'],
                            ['q' => 'Что будет с деньгами при закрытии карты?', 'a' => 'Остаток можно перевести на другую карту в приложении — деньги никуда не исчезают.'],
                            ['q' => 'Сколько карт можно держать одновременно?', 'a' => 'Ограничения нет — заводите отдельную карту под каждую задачу.'],
                            ['q' => 'Как пополнить карту?', 'a' => 'Через СБП или картой российского банка прямо в приложении, без переходов на сторонние сайты.'],
                        ];
                    @endphp
                    @foreach ($faqs as $index => $faq)
                        <div data-faq-item data-open="false" class="faq-item py-6">
                            <button type="button" data-faq-button aria-expanded="false" class="flex w-full items-center justify-between gap-4 text-left">
                                <span class="text-lg font-semibold text-[#141413]">{{ $faq['q'] }}</span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="faq-chevron h-5 w-5 flex-shrink-0 text-[#141413]/40 transition-transform duration-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
                            </button>
                            <div class="faq-answer">
                                <p class="pr-8 pt-3 text-base leading-relaxed text-[#141413]/60">{{ $faq['a'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ================= ПОДДЕРЖКА ================= --}}
        <section id="support" class="px-6 pb-32 lg:px-10">
            <div data-reveal class="mx-auto max-w-4xl rounded-[32px] bg-white px-8 py-20 text-center">
                <span class="eyebrow justify-center">Поддержка</span>
                <h2 class="mt-4 text-[clamp(2.25rem,4vw,3.5rem)] font-medium leading-[1.05] tracking-[-0.02em] text-[#141413]">Остался вопрос?</h2>
                <p class="mx-auto mt-4 max-w-md text-lg text-[#141413]/60">Напишите в поддержку — отвечаем в приложении и на почте.</p>
                <a href="mailto:support@example.com" class="btn btn-primary mt-9">Написать в поддержку</a>
            </div>
        </section>
    </main>

    {{-- ================= ПОДВАЛ ================= --}}
    <footer class="border-t border-[#e8e5e1] bg-[#f3f0ee] px-6 py-16 lg:px-10">
        <div class="mx-auto max-w-[1320px]">
            <div class="grid gap-12 lg:grid-cols-[1.4fr_1fr_1fr_1fr]">
                <div>
                    <p class="text-lg font-extrabold tracking-tight text-[#141413]">[Бренд]</p>
                    <p class="mt-3 max-w-xs text-sm leading-relaxed text-[#141413]/55">[Бренд] — виртуальные карты Mastercard и Visa для платежей за рубежом.</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-[#141413]/40">Продукт</p>
                    <ul class="mt-4 space-y-2.5 text-sm text-[#141413]/65">
                        <li><a href="#lifestyle" class="hover:text-[#141413]">Возможности</a></li>
                        <li><a href="#products" class="hover:text-[#141413]">Карты</a></li>
                        <li><a href="#partners" class="hover:text-[#141413]">Партнёрам</a></li>
                        <li><a href="#faq" class="hover:text-[#141413]">Вопросы</a></li>
                    </ul>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-[#141413]/40">Аккаунт</p>
                    <ul class="mt-4 space-y-2.5 text-sm text-[#141413]/65">
                        <li><a href="#" class="hover:text-[#141413]">Регистрация</a></li>
                        <li><a href="#" class="hover:text-[#141413]">Вход</a></li>
                        <li><a href="#support" class="hover:text-[#141413]">Поддержка</a></li>
                    </ul>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-[#141413]/40">Документы</p>
                    <ul class="mt-4 space-y-2.5 text-sm text-[#141413]/65">
                        <li><a href="#" class="hover:text-[#141413]">Пользовательское соглашение</a></li>
                        <li><a href="#" class="hover:text-[#141413]">Политика конфиденциальности</a></li>
                        <li><a href="#" class="hover:text-[#141413]">Политика AML/KYC</a></li>
                        <li><a href="#" class="hover:text-[#141413]">Тарифы и комиссии</a></li>
                        <li><a href="#" class="hover:text-[#141413]">Политика возврата</a></li>
                        <li><a href="#" class="hover:text-[#141413]">Эмитент карт</a></li>
                    </ul>
                </div>
            </div>

            <p class="mt-14 max-w-3xl text-xs leading-relaxed text-[#141413]/40">
                [Бренд] не является банком и не выпускает карты самостоятельно. Карты эмитирует
                лицензированный партнёр-эмитент; [Бренд] предоставляет интерфейс для выпуска карт,
                управления ими и обработку платежей за услугу.
            </p>
            <p class="mt-6 text-xs text-[#141413]/35">© {{ date('Y') }} [Бренд]. Все права защищены.</p>
        </div>
    </footer>
</body>
</html>
