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

    {{-- ================= ШАПКА (плавающая пилюля, как на mastercard.com) ================= --}}
    <header data-site-header class="fixed inset-x-0 top-4 z-50 px-4">
        <div class="nav-pill mx-auto flex max-w-[1180px] items-center justify-between rounded-full px-5 py-3 sm:px-7">
            <a href="#top" class="text-lg font-extrabold tracking-tight text-[#141413]">[Бренд]</a>

            <nav class="hidden items-center gap-7 text-[15px] font-medium text-[#141413]/70 lg:flex">
                <a href="#features" class="transition hover:text-[#141413]">Возможности</a>
                <a href="#pricing" class="transition hover:text-[#141413]">Тарифы</a>
                <a href="#partners" class="transition hover:text-[#141413]">Партнёрам</a>
                <a href="#faq" class="transition hover:text-[#141413]">Вопросы</a>
                <a href="#support" class="transition hover:text-[#141413]">Поддержка</a>
            </nav>

            <div class="hidden items-center gap-2 lg:flex">
                <a href="#" class="btn btn-tertiary px-3 py-2 text-sm">Войти</a>
                <a href="#pricing" class="btn btn-primary text-sm">Выпустить карту</a>
            </div>

            <button data-menu-toggle type="button" aria-expanded="false" aria-controls="mobile-menu" class="flex h-9 w-9 items-center justify-center rounded-full text-[#141413] lg:hidden">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h16" /></svg>
            </button>
        </div>

        <div data-mobile-menu id="mobile-menu" class="nav-pill mx-auto mt-2 hidden max-w-[1180px] flex-col gap-1 rounded-3xl px-5 py-4 lg:hidden">
            <a href="#features" class="rounded-xl px-3 py-2.5 text-sm font-medium text-[#141413]">Возможности</a>
            <a href="#pricing" class="rounded-xl px-3 py-2.5 text-sm font-medium text-[#141413]">Тарифы</a>
            <a href="#partners" class="rounded-xl px-3 py-2.5 text-sm font-medium text-[#141413]">Партнёрам</a>
            <a href="#faq" class="rounded-xl px-3 py-2.5 text-sm font-medium text-[#141413]">Вопросы</a>
            <a href="#support" class="rounded-xl px-3 py-2.5 text-sm font-medium text-[#141413]">Поддержка</a>
            <a href="#pricing" class="btn btn-primary mt-2 justify-center text-sm">Выпустить карту</a>
        </div>
    </header>

    <main id="top" class="bg-[#f3f0ee]">
        {{-- ================= HERO ================= --}}
        <section class="relative overflow-hidden px-6 pb-20 pt-40 lg:px-8 lg:pt-48">
            <div class="mx-auto grid max-w-[1240px] items-center gap-16 lg:grid-cols-2">
                <div data-reveal>
                    <span class="eyebrow">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor"><circle cx="9" cy="12" r="7" fill-opacity=".55"/><circle cx="15" cy="12" r="7" fill-opacity=".85"/></svg>
                        [Бренд] · платежи без границ
                    </span>
                    <h1 class="mt-5 text-[clamp(2.6rem,5.2vw,4.75rem)] font-medium leading-[1.02] tracking-[-0.03em] text-[#141413]">
                        Карта для платежей за пределами России
                    </h1>
                    <p class="mt-6 max-w-lg text-lg leading-relaxed text-[#141413]/65">
                        Mastercard и Visa для оплаты сервисов, подписок и покупок за рубежом.
                        Оформляется за минуты, пополняется через СБП, работает на любом сайте
                        и кассе, где принимают Mastercard или Visa.
                    </p>

                    <div class="mt-9 flex flex-wrap items-center gap-3">
                        <a href="#pricing" class="btn btn-primary">Выпустить карту</a>
                        <a href="#how" class="btn btn-secondary">Как это работает</a>
                    </div>

                    <p class="mt-10 max-w-md border-t border-[#e8e5e1] pt-6 text-sm leading-relaxed text-[#141413]/55">
                        Инфраструктура построена на нескольких независимых партнёрах-эмитентах —
                        карта продолжает работать, даже если один из них временно недоступен.
                    </p>
                </div>

                {{-- Мокап двух карт --}}
                <div data-reveal style="--reveal-delay:150ms" class="relative mx-auto h-[420px] w-full max-w-md sm:h-[460px]">
                    <div class="float-card absolute right-0 top-10 w-[270px] rotate-[8deg] rounded-[28px] bg-gradient-to-br from-[#f37338] to-[#9a3a0a] p-6 text-white shadow-2xl sm:w-[300px]" style="--tilt:8deg; animation-delay:0.6s;">
                        <div class="flex items-start justify-between">
                            <div class="h-8 w-10 rounded-md bg-gradient-to-br from-amber-100 to-amber-300"></div>
                            <div class="flex gap-1.5">
                                <span class="rounded-full bg-white/20 px-2 py-1 text-[10px] font-semibold backdrop-blur"> Pay</span>
                                <span class="rounded-full bg-white/20 px-2 py-1 text-[10px] font-semibold backdrop-blur">G Pay</span>
                            </div>
                        </div>
                        <p class="mt-8 font-mono text-lg tracking-widest">•••• •••• •••• 5678</p>
                        <div class="mt-6 flex items-end justify-between">
                            <div>
                                <p class="text-[10px] uppercase tracking-wider text-white/60">Баланс</p>
                                <p class="text-base font-semibold">$100.00</p>
                            </div>
                            <p class="text-sm font-bold italic tracking-wide">VISA</p>
                        </div>
                    </div>

                    <div class="float-card absolute left-0 top-0 z-10 w-[270px] -rotate-[7deg] rounded-[28px] bg-gradient-to-br from-[#232220] to-[#141413] p-6 text-white shadow-2xl sm:w-[300px]" style="--tilt:-7deg;">
                        <div class="flex items-start justify-between">
                            <div class="h-8 w-10 rounded-md bg-gradient-to-br from-amber-100 to-amber-300"></div>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" d="M8 9a5.5 5.5 0 0 1 8 0M5.5 6.2a9 9 0 0 1 13 0M11 12a2 2 0 0 1 2 0" /></svg>
                        </div>
                        <p class="mt-8 font-mono text-lg tracking-widest">•••• •••• •••• 4821</p>
                        <div class="mt-6 flex items-end justify-between">
                            <div>
                                <p class="text-[10px] uppercase tracking-wider text-white/60">Баланс</p>
                                <p class="text-base font-semibold">$1,000.00</p>
                            </div>
                            <p class="text-sm font-bold tracking-wide">Mastercard</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ================= ГДЕ ПРИНИМАЮТ ================= --}}
        <section class="px-6 py-24 lg:px-8">
            <div class="mx-auto max-w-[1240px]">
                <div data-reveal class="max-w-2xl">
                    <span class="eyebrow">Возможности</span>
                    <h2 class="mt-3 text-[clamp(1.9rem,3.2vw,2.75rem)] font-medium leading-[1.08] tracking-[-0.02em] text-[#141413]">Работает там же, где карты крупных банков</h2>
                    <p class="mt-3 text-base leading-relaxed text-[#141413]/60">
                        Подписки, облачные сервисы, реклама, бронирования, маркетплейсы — и любой
                        другой сайт или касса, принимающие Mastercard и Visa.
                    </p>
                </div>

                <div class="mt-12 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
                    @php
                        $categories = [
                            ['label' => 'Подписки и стриминг', 'icon' => 'play'],
                            ['label' => 'Облачные сервисы и ИИ', 'icon' => 'spark'],
                            ['label' => 'Путешествия и бронирования', 'icon' => 'plane'],
                            ['label' => 'Маркетплейсы и покупки', 'icon' => 'bag'],
                            ['label' => 'Реклама и инструменты бизнеса', 'icon' => 'bars'],
                            ['label' => 'Игры и цифровые платформы', 'icon' => 'pad'],
                        ];
                    @endphp
                    @foreach ($categories as $i => $cat)
                        <div data-reveal style="--reveal-delay: {{ $i * 70 }}ms" class="flex flex-col items-center gap-3 rounded-3xl border border-[#e8e5e1] bg-white px-4 py-7 text-center">
                            <span class="flex h-11 w-11 items-center justify-center rounded-full bg-[#f37338]/10 text-[#cf4500]">
                                @switch($cat['icon'])
                                    @case('play')
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="12" r="9"/><path d="M10 8.5l6 3.5-6 3.5v-7z"/></svg>
                                        @break
                                    @case('spark')
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"><path d="M12 3l2 6.5L20.5 12 14 14.5 12 21l-2-6.5L3.5 12 10 9.5 12 3z"/></svg>
                                        @break
                                    @case('plane')
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round" stroke-linecap="round"><path d="M3 12l18-7-7 18-2-8-8-3z"/></svg>
                                        @break
                                    @case('bag')
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8h12l-1 12H7L6 8z"/><path d="M9 8V6a3 3 0 0 1 6 0v2"/></svg>
                                        @break
                                    @case('bars')
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"><path d="M5 20V11M12 20V4M19 20v-7"/></svg>
                                        @break
                                    @case('pad')
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="8" width="18" height="9" rx="4"/><path d="M7.5 10.5v4M5.5 12.5h4"/><circle cx="16" cy="12" r="1"/><circle cx="18" cy="14" r="1"/></svg>
                                        @break
                                @endswitch
                            </span>
                            <span class="text-xs font-medium leading-snug text-[#141413]/75">{{ $cat['label'] }}</span>
                        </div>
                    @endforeach
                </div>
                <p class="mt-6 text-center text-sm text-[#141413]/45">и любой сайт или касса, где принимают Mastercard или Visa</p>
            </div>
        </section>

        {{-- ================= КАК ЭТО РАБОТАЕТ ================= --}}
        <section id="how" class="px-6 py-24 lg:px-8">
            <div class="mx-auto max-w-[1240px]">
                <div data-reveal class="mx-auto max-w-2xl text-center">
                    <span class="eyebrow justify-center">Как это устроено</span>
                    <h2 class="mt-3 text-[clamp(1.9rem,3.2vw,2.75rem)] font-medium leading-[1.08] tracking-[-0.02em] text-[#141413]">Четыре шага до первой оплаты</h2>
                </div>

                <div class="relative mt-16 grid gap-10 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="pointer-events-none absolute left-0 right-0 top-6 hidden h-px bg-[#e8e5e1] lg:block"></div>
                    @php
                        $steps = [
                            ['n' => '01', 't' => 'Регистрация и проверка личности', 'd' => 'Указываете данные и проходите проверку — как при оформлении любой банковской карты.'],
                            ['n' => '02', 't' => 'Выбор карты', 'd' => 'Карта для оплаты в интернете или карта с Apple Pay и Google Pay для поездок и оплаты на кассе.'],
                            ['n' => '03', 't' => 'Пополнение через СБП', 'd' => 'Курс и итоговая сумма видны до подтверждения перевода и не меняются после оплаты.'],
                            ['n' => '04', 't' => 'Оплата за границей', 'd' => 'Реквизиты — в приложении, карта — в Apple Pay и Google Pay. Платите на сайте или прикладываете телефон к терминалу.'],
                        ];
                    @endphp
                    @foreach ($steps as $i => $step)
                        <div data-reveal style="--reveal-delay: {{ $i * 100 }}ms" class="relative">
                            <span class="relative z-10 flex h-12 w-12 items-center justify-center rounded-full border border-[#e8e5e1] bg-[#f3f0ee] text-sm font-bold text-[#cf4500]">{{ $step['n'] }}</span>
                            <h3 class="mt-5 text-base font-semibold text-[#141413]">{{ $step['t'] }}</h3>
                            <p class="mt-2 text-sm leading-relaxed text-[#141413]/60">{{ $step['d'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ================= ФОТО-БАННЕР (изображение с mastercard.com) ================= --}}
        <section data-reveal class="relative isolate mx-4 flex h-[440px] items-center justify-center overflow-hidden rounded-[32px] sm:mx-8">
            <img
                src="https://web.archive.org/web/20260913045546im_/https://www.mastercard.com/adobe/dynamicmedia/deliver/dm-aid--e6176780-42de-4e72-b550-906d08a82da0/agentic-commerce-standards-hero.jpg?quality=82&preferwebp=true"
                alt="Оплата телефоном"
                class="absolute inset-0 h-full w-full object-cover"
                loading="lazy"
            >
            <div class="absolute inset-0 bg-gradient-to-t from-[#141413]/80 via-[#141413]/35 to-[#141413]/10"></div>
            <div class="relative max-w-2xl px-6 text-center">
                <span class="eyebrow justify-center !text-white/70">Одна карта — весь мир</span>
                <p class="mt-4 text-[clamp(1.6rem,3vw,2.5rem)] font-medium leading-[1.15] tracking-[-0.02em] text-white">
                    Одна инфраструктура — все возможности: интернет, поездки, Apple Pay и Google Pay.
                </p>
            </div>
        </section>

        {{-- ================= ИНФРАСТРУКТУРА ================= --}}
        <section id="features" class="px-6 py-24 lg:px-8">
            <div class="mx-auto max-w-[1240px]">
                <div data-reveal class="max-w-2xl">
                    <span class="eyebrow">Надёжность</span>
                    <h2 class="mt-3 text-[clamp(1.9rem,3.2vw,2.75rem)] font-medium leading-[1.08] tracking-[-0.02em] text-[#141413]">Карта работает благодаря инфраструктуре, а не одному поставщику</h2>
                    <p class="mt-3 text-base leading-relaxed text-[#141413]/60">
                        Сервис не привязан к одному банку-эмитенту. Несколько независимых партнёров
                        и постоянный мониторинг каждой операции — так карта остаётся рабочей даже
                        при сбоях на стороне отдельного поставщика.
                    </p>
                </div>

                <div class="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    @php
                        $infra = [
                            ['icon' => 'nodes', 't' => 'Резервная эмиссия', 'd' => 'Если один из партнёров-эмитентов временно недоступен, система переключает выпуск и обслуживание карт на резервного — незаметно для держателя карты.'],
                            ['icon' => 'shield', 't' => 'Контроль каждой операции', 'd' => 'Каждый платёж проходит через внутреннюю систему риск-правил — аномалии видны раньше, чем по ним успевает среагировать платёжная система.'],
                            ['icon' => 'lock', 't' => 'Реквизиты не хранятся', 'd' => 'Номер карты и CVV не лежат в нашей базе — они запрашиваются у эмитента в момент, когда держатель открывает их в приложении.'],
                            ['icon' => 'pulse', 't' => 'Баланс без задержек', 'd' => 'Баланс в приложении сверяется с провайдером в реальном времени — расхождений между тем, что потрачено, и тем, что показано, не бывает.'],
                        ];
                    @endphp
                    @foreach ($infra as $i => $f)
                        <div data-reveal style="--reveal-delay: {{ $i * 90 }}ms" class="rounded-3xl border border-[#e8e5e1] bg-white p-6">
                            <span class="flex h-11 w-11 items-center justify-center rounded-full bg-[#f37338]/10 text-[#cf4500]">
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
                            <h3 class="mt-5 text-base font-semibold text-[#141413]">{{ $f['t'] }}</h3>
                            <p class="mt-2 text-sm leading-relaxed text-[#141413]/60">{{ $f['d'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ================= ЛИЧНЫЙ КАБИНЕТ ================= --}}
        <section class="px-6 py-24 lg:px-8">
            <div class="mx-auto grid max-w-[1240px] items-center gap-16 rounded-[32px] bg-white p-8 lg:grid-cols-2 lg:p-14">
                <div data-reveal class="order-2 lg:order-1">
                    <span class="eyebrow">Личный кабинет</span>
                    <h2 class="mt-3 text-[clamp(1.9rem,3.2vw,2.75rem)] font-medium leading-[1.08] tracking-[-0.02em] text-[#141413]">Управление картой — полностью в приложении</h2>
                    <dl class="mt-8 space-y-6">
                        @foreach ([
                            ['t' => 'Несколько карт под разные задачи', 'd' => 'Отдельная карта на подписки, отдельная — на рекламу или поездку: расходы не смешиваются.'],
                            ['t' => 'История операций', 'd' => 'Каждая операция — с суммой в валюте карты и в рублях, статусом и временем.'],
                            ['t' => 'Заморозка и закрытие в одно касание', 'd' => 'Подозрительная активность или карта больше не нужна — статус меняется мгновенно, без обращения в поддержку.'],
                            ['t' => 'Выписка по запросу', 'd' => 'Формируется за один тап и подходит для личной отчётности.'],
                            ['t' => 'Push-уведомления', 'd' => 'О каждом списании и пополнении — в момент операции.'],
                        ] as $item)
                            <div class="flex gap-4">
                                <span class="mt-1.5 h-2 w-2 flex-shrink-0 rounded-full bg-[#f37338]"></span>
                                <div>
                                    <dt class="text-base font-semibold text-[#141413]">{{ $item['t'] }}</dt>
                                    <dd class="mt-1 text-sm leading-relaxed text-[#141413]/60">{{ $item['d'] }}</dd>
                                </div>
                            </div>
                        @endforeach
                    </dl>
                </div>

                {{-- Мокап приложения --}}
                <div data-reveal style="--reveal-delay:150ms" class="order-1 mx-auto w-full max-w-[300px] lg:order-2">
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

        {{-- ================= APPLE PAY / GOOGLE PAY (фото с mastercard.com) ================= --}}
        <section class="px-6 py-24 lg:px-8">
            <div class="mx-auto grid max-w-[1240px] items-center gap-16 lg:grid-cols-2">
                <div data-reveal class="overflow-hidden rounded-[32px]">
                    <img
                        src="https://web.archive.org/web/20260913045546im_/https://www.mastercard.com/adobe/dynamicmedia/deliver/dm-aid--31deb226-0992-429e-8fae-9bccb16fc665/br45724-mastercard-websiteimageryrefresh-solutions-4-1x1.jpg?quality=82&preferwebp=true"
                        alt="Оплата часами Apple Watch"
                        class="h-[380px] w-full object-cover sm:h-[440px]"
                        loading="lazy"
                    >
                </div>

                <div data-reveal style="--reveal-delay:150ms">
                    <span class="eyebrow">Apple Pay и Google Pay</span>
                    <h2 class="mt-3 text-[clamp(1.9rem,3.2vw,2.75rem)] font-medium leading-[1.08] tracking-[-0.02em] text-[#141413]">Карта в телефоне и на часах</h2>
                    <p class="mt-4 text-base leading-relaxed text-[#141413]/60">
                        Карта с поддержкой токенизации добавляется в Apple Pay и Google Pay. Пластика
                        нет — оплата проходит по одноразовому токену, а не по номеру карты.
                    </p>
                    <ul class="mt-8 space-y-4 text-sm text-[#141413]/75">
                        @foreach ([
                            'Бесконтактная оплата по NFC за границей',
                            'Интернет на кассе не нужен',
                            'Работает с Apple Watch и Wear OS',
                            'Продавец не получает номер карты — только токен',
                        ] as $li)
                            <li class="flex items-start gap-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-5 w-5 flex-shrink-0 text-[#cf4500]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M5 13l4 4L19 7"/></svg>
                                {{ $li }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </section>

        {{-- ================= ПОПОЛНЕНИЕ ================= --}}
        <section class="px-6 py-24 lg:px-8">
            <div class="mx-auto grid max-w-[1240px] items-center gap-16 rounded-[32px] bg-white p-8 lg:grid-cols-2 lg:p-14">
                <div data-reveal>
                    <span class="eyebrow">Пополнение</span>
                    <h2 class="mt-3 text-[clamp(1.9rem,3.2vw,2.75rem)] font-medium leading-[1.08] tracking-[-0.02em] text-[#141413]">Курс и сумма — известны заранее</h2>
                    <p class="mt-4 max-w-md text-base leading-relaxed text-[#141413]/60">
                        Пополняете через СБП или картой российского банка. Курс конвертации и
                        комиссия показываются до подтверждения и фиксируются в момент оплаты —
                        сумма после этого не меняется.
                    </p>
                </div>

                <div data-reveal style="--reveal-delay:150ms" class="mx-auto w-full max-w-sm rounded-3xl border border-[#e8e5e1] bg-[#f3f0ee] p-7">
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

                    <button type="button" class="btn btn-primary mt-6 w-full">Перейти к оплате</button>
                </div>
            </div>
        </section>

        {{-- ================= ПАРТНЁРСКАЯ ПРОГРАММА ================= --}}
        <section id="partners" class="px-6 py-24 lg:px-8">
            <div class="mx-auto max-w-[1240px]">
                <div data-reveal class="max-w-2xl">
                    <span class="eyebrow">Партнёрам</span>
                    <h2 class="mt-3 text-[clamp(1.9rem,3.2vw,2.75rem)] font-medium leading-[1.08] tracking-[-0.02em] text-[#141413]">Партнёрская программа с прозрачной статистикой</h2>
                    <p class="mt-3 text-base leading-relaxed text-[#141413]/60">
                        Приглашённый пользователь закрепляется за вами навсегда. Начисления
                        считаются автоматически и видны в личном кабинете в реальном времени —
                        без ручных сверок и ожидания отчёта.
                    </p>
                </div>

                <div class="mt-12 grid gap-6 sm:grid-cols-3">
                    @foreach ([
                        ['v' => '[X]%', 'l' => 'с каждого пополнения приглашённого пользователя'],
                        ['v' => '[X]%', 'l' => 'с выпуска карты приглашённым — с первой и со всех следующих'],
                        ['v' => '24/7', 'l' => 'начисления считаются автоматически и видны сразу'],
                    ] as $i => $stat)
                        <div data-reveal style="--reveal-delay: {{ $i * 90 }}ms" class="rounded-3xl border border-[#e8e5e1] bg-white p-8">
                            <p class="text-3xl font-bold text-[#cf4500]">{{ $stat['v'] }}</p>
                            <p class="mt-2 text-sm leading-relaxed text-[#141413]/60">{{ $stat['l'] }}</p>
                        </div>
                    @endforeach
                </div>

                <a href="#" data-reveal class="btn btn-primary mt-10">Получить партнёрскую ссылку</a>
            </div>
        </section>

        {{-- ================= ТАРИФЫ ================= --}}
        <section id="pricing" class="px-6 py-24 lg:px-8">
            <div class="mx-auto max-w-[1240px]">
                <div data-reveal class="mx-auto max-w-2xl text-center">
                    <span class="eyebrow justify-center">Тарифы</span>
                    <h2 class="mt-3 text-[clamp(1.9rem,3.2vw,2.75rem)] font-medium leading-[1.08] tracking-[-0.02em] text-[#141413]">Понятная стоимость без ежемесячной платы за обслуживание</h2>
                </div>

                <div class="mt-14 grid gap-6 lg:grid-cols-3">
                    <div data-reveal class="rounded-[28px] border border-[#e8e5e1] bg-white p-8">
                        <p class="text-sm font-semibold text-[#cf4500]">Mastercard</p>
                        <h3 class="mt-2 text-xl font-semibold text-[#141413]">Карта для оплаты в интернете</h3>
                        <p class="mt-6 text-3xl font-bold text-[#141413]">[X ₽]</p>
                        <p class="text-sm text-[#141413]/50">разово за выпуск · 0 ₽ в месяц</p>
                        <ul class="mt-6 space-y-3 text-sm text-[#141413]/70">
                            <li>— Работает на любом сайте, принимающем Mastercard</li>
                            <li>— Курс и сумма пополнения видны до оплаты</li>
                            <li>— История и статус по каждой операции</li>
                        </ul>
                        <a href="#" class="btn btn-secondary mt-8 w-full">Выпустить карту</a>
                    </div>

                    <div data-reveal style="--reveal-delay:100ms" class="relative rounded-[28px] bg-[#141413] p-8 text-white">
                        <p class="text-sm font-semibold text-[#f37338]">Visa</p>
                        <h3 class="mt-2 text-xl font-semibold">Карта с Apple Pay и Google Pay</h3>
                        <p class="mt-6 text-3xl font-bold">[X ₽]</p>
                        <p class="text-sm text-white/50">разово за выпуск · 0 ₽ в месяц</p>
                        <ul class="mt-6 space-y-3 text-sm text-white/70">
                            <li>— Бесконтактная оплата за границей</li>
                            <li>— Токенизация вместо передачи номера карты</li>
                            <li>— Работает с Apple Watch</li>
                        </ul>
                        <a href="#" class="btn btn-ondark mt-8 w-full">Выпустить карту</a>
                    </div>

                    <div data-reveal style="--reveal-delay:200ms" class="rounded-[28px] border border-[#e8e5e1] bg-white p-8">
                        <p class="text-sm font-semibold text-[#cf4500]">Для бизнеса</p>
                        <h3 class="mt-2 text-xl font-semibold text-[#141413]">Для команд и бизнеса</h3>
                        <p class="mt-6 text-3xl font-bold text-[#141413]">По запросу</p>
                        <p class="text-sm text-[#141413]/50">оплата по счёту</p>
                        <ul class="mt-6 space-y-3 text-sm text-[#141413]/70">
                            <li>— Несколько карт на одном балансе компании</li>
                            <li>— Пополнение и оплата по счёту</li>
                            <li>— Закрывающие документы для бухгалтерии</li>
                        </ul>
                        <a href="#support" class="btn btn-secondary mt-8 w-full">Обсудить условия</a>
                    </div>
                </div>
                <p class="mt-8 text-center text-sm text-[#141413]/45">Комиссия за пополнение показывается до перевода и не меняется после подтверждения.</p>
            </div>
        </section>

        {{-- ================= БЕЗОПАСНОСТЬ ================= --}}
        <section id="security" class="mx-4 rounded-[32px] bg-[#141413] px-6 py-24 text-white sm:mx-8 lg:px-8">
            <div class="mx-auto max-w-4xl text-center">
                <span class="eyebrow justify-center !text-[#f37338]">Безопасность</span>
                <h2 class="mt-3 text-[clamp(1.9rem,3.2vw,2.75rem)] font-medium leading-[1.08] tracking-[-0.02em]">Стандарты, которые мы не готовы снижать</h2>

                <div class="mt-14 grid gap-8 text-left sm:grid-cols-2">
                    @foreach ([
                        'Карты выпускает партнёр-эмитент по лицензии платёжной системы',
                        'Проверка личности при выпуске карты',
                        'Мониторинг операций по внутренним риск-правилам в реальном времени',
                        'Реквизиты карты доступны только держателю и не хранятся в открытом виде',
                    ] as $i => $point)
                        <div data-reveal style="--reveal-delay: {{ $i * 90 }}ms" class="flex items-start gap-4 border-t border-white/10 pt-6">
                            <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-5 w-5 flex-shrink-0 text-[#f37338]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M5 13l4 4L19 7"/></svg>
                            <p class="text-sm leading-relaxed text-white/75">{{ $point }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ================= FAQ ================= --}}
        <section id="faq" class="px-6 py-24 lg:px-8">
            <div class="mx-auto max-w-3xl">
                <div data-reveal class="text-center">
                    <span class="eyebrow justify-center">Вопросы</span>
                    <h2 class="mt-3 text-[clamp(1.9rem,3.2vw,2.75rem)] font-medium leading-[1.08] tracking-[-0.02em] text-[#141413]">Вопросы, которые обычно задают перед выпуском карты</h2>
                </div>

                <div class="mt-12 divide-y divide-[#e8e5e1] border-y border-[#e8e5e1]">
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
                        <div data-faq-item data-open="false" class="faq-item py-5">
                            <button type="button" data-faq-button aria-expanded="false" class="flex w-full items-center justify-between gap-4 text-left">
                                <span class="text-base font-semibold text-[#141413]">{{ $faq['q'] }}</span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="faq-chevron h-5 w-5 flex-shrink-0 text-[#141413]/40 transition-transform duration-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
                            </button>
                            <div class="faq-answer">
                                <p class="pr-8 pt-3 text-sm leading-relaxed text-[#141413]/60">{{ $faq['a'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ================= ПОДДЕРЖКА ================= --}}
        <section id="support" class="px-6 pb-28 lg:px-8">
            <div data-reveal class="mx-auto max-w-4xl rounded-[32px] bg-white px-8 py-16 text-center">
                <span class="eyebrow justify-center">Поддержка</span>
                <h2 class="mt-3 text-[clamp(1.9rem,3.2vw,2.75rem)] font-medium leading-[1.08] tracking-[-0.02em] text-[#141413]">Остался вопрос?</h2>
                <p class="mx-auto mt-3 max-w-md text-base text-[#141413]/60">Напишите в поддержку — отвечаем в приложении и на почте.</p>
                <a href="mailto:support@example.com" class="btn btn-primary mt-8">Написать в поддержку</a>
            </div>
        </section>
    </main>

    {{-- ================= ПОДВАЛ ================= --}}
    <footer class="border-t border-[#e8e5e1] bg-[#f3f0ee] px-6 py-16 lg:px-8">
        <div class="mx-auto max-w-[1240px]">
            <div class="grid gap-12 lg:grid-cols-[1.4fr_1fr_1fr_1fr]">
                <div>
                    <p class="text-lg font-extrabold tracking-tight text-[#141413]">[Бренд]</p>
                    <p class="mt-3 max-w-xs text-sm leading-relaxed text-[#141413]/55">[Бренд] — виртуальные карты Mastercard и Visa для платежей за рубежом.</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-[#141413]/40">Продукт</p>
                    <ul class="mt-4 space-y-2.5 text-sm text-[#141413]/65">
                        <li><a href="#features" class="hover:text-[#141413]">Возможности</a></li>
                        <li><a href="#pricing" class="hover:text-[#141413]">Тарифы</a></li>
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
