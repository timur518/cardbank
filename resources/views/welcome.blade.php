<!DOCTYPE html>
<html lang="ru" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>[Бренд] — карта для платежей за пределами России</title>
    <meta name="description" content="Виртуальная карта Mastercard и Visa для оплаты сервисов, подписок и покупок за рубежом. Пополнение из России через СБП, полный контроль — в приложении.">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>body { font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; }</style>
</head>
<body class="antialiased">

    {{-- ================= ШАПКА ================= --}}
    <header data-site-header class="fixed inset-x-0 top-0 z-50">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-5 lg:px-8">
            <a href="#top" class="text-lg font-extrabold tracking-tight text-[#12131A]">[Бренд]</a>

            <nav class="hidden items-center gap-8 text-sm font-medium text-[#12131A]/70 lg:flex">
                <a href="#features" class="transition hover:text-[#12131A]">Возможности</a>
                <a href="#pricing" class="transition hover:text-[#12131A]">Тарифы</a>
                <a href="#partners" class="transition hover:text-[#12131A]">Партнёрам</a>
                <a href="#faq" class="transition hover:text-[#12131A]">Вопросы</a>
                <a href="#support" class="transition hover:text-[#12131A]">Поддержка</a>
            </nav>

            <div class="hidden items-center gap-3 lg:flex">
                <a href="#" class="rounded-lg px-4 py-2 text-sm font-medium text-[#12131A]/70 transition hover:text-[#12131A]">Войти</a>
                <a href="#pricing" class="rounded-lg bg-[#1A46FF] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#1636CC]">Выпустить карту</a>
            </div>

            <button data-menu-toggle type="button" aria-expanded="false" aria-controls="mobile-menu" class="flex h-10 w-10 items-center justify-center rounded-lg text-[#12131A] lg:hidden">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h16" /></svg>
            </button>
        </div>

        <div data-mobile-menu id="mobile-menu" class="hidden flex-col gap-1 border-t border-[#E6E5E1] bg-[#FAF9F7] px-6 py-4 lg:hidden">
            <a href="#features" class="rounded-lg px-3 py-2.5 text-sm font-medium text-[#12131A]">Возможности</a>
            <a href="#pricing" class="rounded-lg px-3 py-2.5 text-sm font-medium text-[#12131A]">Тарифы</a>
            <a href="#partners" class="rounded-lg px-3 py-2.5 text-sm font-medium text-[#12131A]">Партнёрам</a>
            <a href="#faq" class="rounded-lg px-3 py-2.5 text-sm font-medium text-[#12131A]">Вопросы</a>
            <a href="#support" class="rounded-lg px-3 py-2.5 text-sm font-medium text-[#12131A]">Поддержка</a>
            <a href="#pricing" class="mt-2 rounded-lg bg-[#1A46FF] px-3 py-2.5 text-center text-sm font-semibold text-white">Выпустить карту</a>
        </div>
    </header>

    <main id="top">
        {{-- ================= HERO ================= --}}
        <section class="relative overflow-hidden px-6 pb-20 pt-36 lg:px-8 lg:pt-44">
            <div class="mx-auto grid max-w-7xl items-center gap-16 lg:grid-cols-2">
                <div data-reveal>
                    <h1 class="text-4xl font-extrabold leading-[1.08] tracking-tight text-[#12131A] sm:text-5xl lg:text-[3.4rem]">
                        Карта для платежей за&nbsp;пределами России
                    </h1>
                    <p class="mt-6 max-w-lg text-lg leading-relaxed text-[#12131A]/65">
                        Mastercard и Visa для оплаты сервисов, подписок и покупок за рубежом.
                        Оформляется за минуты, пополняется через СБП, работает на любом сайте
                        и кассе, где принимают Mastercard или Visa.
                    </p>

                    <div class="mt-9 flex flex-wrap items-center gap-4">
                        <a href="#pricing" class="rounded-lg bg-[#1A46FF] px-6 py-3.5 text-sm font-semibold text-white shadow-[0_10px_30px_-10px_rgba(26,70,255,0.55)] transition hover:bg-[#1636CC]">
                            Выпустить карту
                        </a>
                        <a href="#how" class="rounded-lg border border-[#12131A]/15 px-6 py-3.5 text-sm font-semibold text-[#12131A] transition hover:border-[#12131A]/30">
                            Как это работает
                        </a>
                    </div>

                    <p class="mt-8 max-w-md border-t border-[#E6E5E1] pt-6 text-sm leading-relaxed text-[#12131A]/55">
                        Инфраструктура построена на нескольких независимых партнёрах-эмитентах —
                        карта продолжает работать, даже если один из них временно недоступен.
                    </p>
                </div>

                {{-- Мокап двух карт --}}
                <div data-reveal style="--reveal-delay:150ms" class="relative mx-auto h-[420px] w-full max-w-md sm:h-[460px]">
                    <div class="float-card absolute right-0 top-10 w-[270px] rotate-[8deg] rounded-[22px] bg-gradient-to-br from-[#0F3D3A] to-[#1E6F63] p-6 text-white shadow-2xl sm:w-[300px]" style="--tilt:8deg; animation-delay:0.6s;">
                        <div class="flex items-start justify-between">
                            <div class="h-8 w-10 rounded-md bg-gradient-to-br from-amber-200 to-amber-400"></div>
                            <div class="flex gap-1.5">
                                <span class="rounded-full bg-white/15 px-2 py-1 text-[10px] font-semibold backdrop-blur"> Pay</span>
                                <span class="rounded-full bg-white/15 px-2 py-1 text-[10px] font-semibold backdrop-blur">G Pay</span>
                            </div>
                        </div>
                        <p class="mt-8 font-mono text-lg tracking-widest">•••• •••• •••• 5678</p>
                        <div class="mt-6 flex items-end justify-between">
                            <div>
                                <p class="text-[10px] uppercase tracking-wider text-white/50">Баланс</p>
                                <p class="text-base font-semibold">$100.00</p>
                            </div>
                            <p class="text-sm font-bold italic tracking-wide">VISA</p>
                        </div>
                    </div>

                    <div class="float-card absolute left-0 top-0 z-10 w-[270px] -rotate-[7deg] rounded-[22px] bg-gradient-to-br from-[#1B2030] to-[#2E3550] p-6 text-white shadow-2xl sm:w-[300px]" style="--tilt:-7deg;">
                        <div class="flex items-start justify-between">
                            <div class="h-8 w-10 rounded-md bg-gradient-to-br from-amber-200 to-amber-400"></div>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" d="M8 9a5.5 5.5 0 0 1 8 0M5.5 6.2a9 9 0 0 1 13 0M11 12a2 2 0 0 1 2 0" /></svg>
                        </div>
                        <p class="mt-8 font-mono text-lg tracking-widest">•••• •••• •••• 4821</p>
                        <div class="mt-6 flex items-end justify-between">
                            <div>
                                <p class="text-[10px] uppercase tracking-wider text-white/50">Баланс</p>
                                <p class="text-base font-semibold">$1,000.00</p>
                            </div>
                            <p class="text-sm font-bold tracking-wide">Mastercard</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ================= ГДЕ ПРИНИМАЮТ ================= --}}
        <section class="border-y border-[#E6E5E1] bg-[#F1F1EE] px-6 py-20 lg:px-8">
            <div class="mx-auto max-w-7xl">
                <div data-reveal class="max-w-2xl">
                    <h2 class="text-2xl font-bold tracking-tight text-[#12131A] sm:text-3xl">Работает там же, где карты крупных банков</h2>
                    <p class="mt-3 text-base leading-relaxed text-[#12131A]/60">
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
                        <div data-reveal style="--reveal-delay: {{ $i * 70 }}ms" class="flex flex-col items-center gap-3 rounded-2xl border border-[#E6E5E1] bg-white px-4 py-6 text-center">
                            <span class="flex h-11 w-11 items-center justify-center rounded-full bg-[#F1F1EE] text-[#1A46FF]">
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
                            <span class="text-xs font-medium leading-snug text-[#12131A]/75">{{ $cat['label'] }}</span>
                        </div>
                    @endforeach
                </div>
                <p class="mt-6 text-center text-sm text-[#12131A]/45">и любой сайт или касса, где принимают Mastercard или Visa</p>
            </div>
        </section>

        {{-- ================= КАК ЭТО РАБОТАЕТ ================= --}}
        <section id="how" class="px-6 py-24 lg:px-8">
            <div class="mx-auto max-w-7xl">
                <h2 data-reveal class="text-center text-2xl font-bold tracking-tight text-[#12131A] sm:text-3xl">Четыре шага до первой оплаты</h2>

                <div class="relative mt-16 grid gap-10 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="pointer-events-none absolute left-0 right-0 top-6 hidden h-px bg-[#E6E5E1] lg:block"></div>
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
                            <span class="relative z-10 flex h-12 w-12 items-center justify-center rounded-full border border-[#E6E5E1] bg-[#FAF9F7] text-sm font-bold text-[#1A46FF]">{{ $step['n'] }}</span>
                            <h3 class="mt-5 text-base font-semibold text-[#12131A]">{{ $step['t'] }}</h3>
                            <p class="mt-2 text-sm leading-relaxed text-[#12131A]/60">{{ $step['d'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ================= ФОТО-БАННЕР ================= --}}
        <section data-reveal class="relative isolate flex h-[420px] items-center justify-center overflow-hidden px-6">
            <img
                src="https://images.unsplash.com/photo-1758908176211-5bd0932f956a?w=2400&q=80&auto=format&fit=crop"
                alt="Две карты на тёмной поверхности"
                class="absolute inset-0 h-full w-full object-cover"
                loading="lazy"
            >
            <div class="absolute inset-0 bg-[#12131A]/70"></div>
            <p class="relative max-w-2xl text-center text-2xl font-semibold leading-snug text-white sm:text-3xl">
                Одна инфраструктура — все возможности: интернет, поездки, Apple Pay и Google Pay.
            </p>
        </section>

        {{-- ================= ИНФРАСТРУКТУРА ================= --}}
        <section id="features" class="px-6 py-24 lg:px-8">
            <div class="mx-auto max-w-7xl">
                <div data-reveal class="max-w-2xl">
                    <h2 class="text-2xl font-bold tracking-tight text-[#12131A] sm:text-3xl">Карта работает благодаря инфраструктуре, а не одному поставщику</h2>
                    <p class="mt-3 text-base leading-relaxed text-[#12131A]/60">
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
                        <div data-reveal style="--reveal-delay: {{ $i * 90 }}ms" class="rounded-2xl border border-[#E6E5E1] p-6">
                            <span class="flex h-11 w-11 items-center justify-center rounded-full bg-[#1A46FF]/10 text-[#1A46FF]">
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
                            <h3 class="mt-5 text-base font-semibold text-[#12131A]">{{ $f['t'] }}</h3>
                            <p class="mt-2 text-sm leading-relaxed text-[#12131A]/60">{{ $f['d'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ================= ЛИЧНЫЙ КАБИНЕТ ================= --}}
        <section class="bg-[#F1F1EE] px-6 py-24 lg:px-8">
            <div class="mx-auto grid max-w-7xl items-center gap-16 lg:grid-cols-2">
                <div data-reveal class="order-2 lg:order-1">
                    <h2 class="text-2xl font-bold tracking-tight text-[#12131A] sm:text-3xl">Управление картой — полностью в приложении</h2>
                    <dl class="mt-8 space-y-6">
                        @foreach ([
                            ['t' => 'Несколько карт под разные задачи', 'd' => 'Отдельная карта на подписки, отдельная — на рекламу или поездку: расходы не смешиваются.'],
                            ['t' => 'История операций', 'd' => 'Каждая операция — с суммой в валюте карты и в рублях, статусом и временем.'],
                            ['t' => 'Заморозка и закрытие в одно касание', 'd' => 'Подозрительная активность или карта больше не нужна — статус меняется мгновенно, без обращения в поддержку.'],
                            ['t' => 'Выписка по запросу', 'd' => 'Формируется за один тап и подходит для личной отчётности.'],
                            ['t' => 'Push-уведомления', 'd' => 'О каждом списании и пополнении — в момент операции.'],
                        ] as $item)
                            <div class="flex gap-4">
                                <span class="mt-1.5 h-2 w-2 flex-shrink-0 rounded-full bg-[#1A46FF]"></span>
                                <div>
                                    <dt class="text-base font-semibold text-[#12131A]">{{ $item['t'] }}</dt>
                                    <dd class="mt-1 text-sm leading-relaxed text-[#12131A]/60">{{ $item['d'] }}</dd>
                                </div>
                            </div>
                        @endforeach
                    </dl>
                </div>

                {{-- Мокап приложения --}}
                <div data-reveal style="--reveal-delay:150ms" class="order-1 mx-auto w-full max-w-[300px] lg:order-2">
                    <div class="rounded-[36px] border border-[#12131A]/10 bg-[#12131A] p-3 shadow-2xl">
                        <div class="rounded-[26px] bg-[#0E0F14] px-5 pb-6 pt-8">
                            <p class="text-xs text-white/40">Баланс</p>
                            <p class="mt-1 text-2xl font-bold text-white">$1,000.00</p>

                            <div class="relative mt-6 h-[110px]">
                                <div class="absolute left-6 top-3 w-[190px] rotate-3 rounded-2xl bg-gradient-to-br from-[#0F3D3A] to-[#1E6F63] p-4 text-white">
                                    <p class="font-mono text-xs tracking-widest">•••• 5678</p>
                                    <p class="mt-4 text-[10px] font-bold italic">VISA</p>
                                </div>
                                <div class="absolute left-0 top-0 w-[190px] -rotate-3 rounded-2xl bg-gradient-to-br from-[#1B2030] to-[#2E3550] p-4 text-white shadow-lg">
                                    <p class="font-mono text-xs tracking-widest">•••• 4821</p>
                                    <p class="mt-4 text-[10px] font-bold">Mastercard</p>
                                </div>
                            </div>

                            <p class="mt-6 text-[11px] font-semibold uppercase tracking-wider text-white/40">Операции</p>
                            <div class="mt-3 space-y-3">
                                @foreach ([
                                    ['n' => 'Пополнение · СБП', 'a' => '+$44.48', 'c' => 'text-emerald-400'],
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

        {{-- ================= APPLE PAY / GOOGLE PAY ================= --}}
        <section class="px-6 py-24 lg:px-8">
            <div class="mx-auto grid max-w-7xl items-center gap-16 lg:grid-cols-2">
                <div data-reveal class="relative mx-auto flex h-[340px] w-full max-w-sm items-center justify-center">
                    <span class="nfc-wave absolute h-40 w-40 rounded-full border border-[#1A46FF]/40"></span>
                    <span class="nfc-wave absolute h-40 w-40 rounded-full border border-[#1A46FF]/40" style="animation-delay:0.7s"></span>
                    <span class="nfc-wave absolute h-40 w-40 rounded-full border border-[#1A46FF]/40" style="animation-delay:1.4s"></span>
                    <div class="relative z-10 w-[190px] rounded-[30px] border border-[#12131A]/10 bg-[#12131A] p-2.5 shadow-2xl">
                        <div class="flex h-[360px] flex-col items-center justify-center rounded-[22px] bg-[#0E0F14] p-5">
                            <div class="w-full rounded-2xl bg-gradient-to-br from-[#0F3D3A] to-[#1E6F63] p-4 text-white shadow-lg">
                                <div class="flex justify-between">
                                    <span class="rounded-full bg-white/15 px-2 py-0.5 text-[9px] font-semibold"> Pay</span>
                                    <span class="rounded-full bg-white/15 px-2 py-0.5 text-[9px] font-semibold">G Pay</span>
                                </div>
                                <p class="mt-6 font-mono text-xs tracking-widest">•••• 5678</p>
                                <p class="mt-5 text-[10px] font-bold italic">VISA</p>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" class="mt-8 h-8 w-8 text-white/50" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"><path d="M8 9a5.5 5.5 0 0 1 8 0M5.5 6.2a9 9 0 0 1 13 0M11 12a2 2 0 0 1 2 0"/></svg>
                        </div>
                    </div>
                </div>

                <div data-reveal style="--reveal-delay:150ms">
                    <h2 class="text-2xl font-bold tracking-tight text-[#12131A] sm:text-3xl">Карта в телефоне и на часах</h2>
                    <p class="mt-4 text-base leading-relaxed text-[#12131A]/60">
                        Карта с поддержкой токенизации добавляется в Apple Pay и Google Pay. Пластика
                        нет — оплата проходит по одноразовому токену, а не по номеру карты.
                    </p>
                    <ul class="mt-8 space-y-4 text-sm text-[#12131A]/75">
                        @foreach ([
                            'Бесконтактная оплата по NFC за границей',
                            'Интернет на кассе не нужен',
                            'Работает с Apple Watch и Wear OS',
                            'Продавец не получает номер карты — только токен',
                        ] as $li)
                            <li class="flex items-start gap-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-5 w-5 flex-shrink-0 text-[#1A46FF]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M5 13l4 4L19 7"/></svg>
                                {{ $li }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </section>

        {{-- ================= ПОПОЛНЕНИЕ ================= --}}
        <section class="bg-[#F1F1EE] px-6 py-24 lg:px-8">
            <div class="mx-auto grid max-w-7xl items-center gap-16 lg:grid-cols-2">
                <div data-reveal>
                    <h2 class="text-2xl font-bold tracking-tight text-[#12131A] sm:text-3xl">Курс и сумма — известны заранее</h2>
                    <p class="mt-4 max-w-md text-base leading-relaxed text-[#12131A]/60">
                        Пополняете через СБП или картой российского банка. Курс конвертации и
                        комиссия показываются до подтверждения и фиксируются в момент оплаты —
                        сумма после этого не меняется.
                    </p>
                </div>

                <div data-reveal style="--reveal-delay:150ms" class="mx-auto w-full max-w-sm rounded-3xl border border-[#E6E5E1] bg-white p-7 shadow-[0_20px_45px_-25px_rgba(18,19,26,0.35)]">
                    <p class="text-xs font-semibold uppercase tracking-wider text-[#12131A]/40">Пополнить баланс</p>

                    <div class="mt-5 flex items-center justify-between rounded-xl border border-[#1A46FF]/30 bg-[#1A46FF]/5 px-4 py-3">
                        <span class="text-sm font-medium text-[#12131A]">Карта •• 5678</span>
                        <span class="text-sm text-[#12131A]/50">$100,00</span>
                    </div>

                    <div class="mt-4 flex items-baseline justify-between">
                        <span class="text-4xl font-extrabold text-[#12131A]">100</span>
                        <span class="rounded-full bg-[#F1F1EE] px-3 py-1 text-xs font-semibold text-[#12131A]/60">$ / ₽</span>
                    </div>
                    <p class="mt-1 text-xs text-[#12131A]/40">Курс: 1 $ ≈ 100,53 ₽</p>

                    <div class="mt-6 space-y-2 border-t border-[#E6E5E1] pt-5 text-sm">
                        <div class="flex justify-between text-[#12131A]/60">
                            <span>Зачислим на карту •• 5678</span>
                            <span>$100,00</span>
                        </div>
                        <div class="flex justify-between text-base font-bold text-[#12131A]">
                            <span>Итого к оплате</span>
                            <span>10 053 ₽</span>
                        </div>
                    </div>

                    <button type="button" class="mt-6 w-full rounded-xl bg-[#1A46FF] py-3.5 text-sm font-semibold text-white">Перейти к оплате</button>
                </div>
            </div>
        </section>

        {{-- ================= ПАРТНЁРСКАЯ ПРОГРАММА ================= --}}
        <section id="partners" class="px-6 py-24 lg:px-8">
            <div class="mx-auto max-w-7xl">
                <div data-reveal class="max-w-2xl">
                    <h2 class="text-2xl font-bold tracking-tight text-[#12131A] sm:text-3xl">Партнёрская программа с прозрачной статистикой</h2>
                    <p class="mt-3 text-base leading-relaxed text-[#12131A]/60">
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
                        <div data-reveal style="--reveal-delay: {{ $i * 90 }}ms" class="rounded-2xl border border-[#E6E5E1] p-8">
                            <p class="text-3xl font-extrabold text-[#1A46FF]">{{ $stat['v'] }}</p>
                            <p class="mt-2 text-sm leading-relaxed text-[#12131A]/60">{{ $stat['l'] }}</p>
                        </div>
                    @endforeach
                </div>

                <a href="#" data-reveal class="mt-10 inline-flex rounded-lg bg-[#12131A] px-6 py-3.5 text-sm font-semibold text-white transition hover:bg-[#12131A]/85">
                    Получить партнёрскую ссылку
                </a>
            </div>
        </section>

        {{-- ================= ТАРИФЫ ================= --}}
        <section id="pricing" class="bg-[#F1F1EE] px-6 py-24 lg:px-8">
            <div class="mx-auto max-w-7xl">
                <h2 data-reveal class="text-center text-2xl font-bold tracking-tight text-[#12131A] sm:text-3xl">Понятная стоимость без ежемесячной платы за обслуживание</h2>

                <div class="mt-14 grid gap-6 lg:grid-cols-3">
                    <div data-reveal class="rounded-3xl border border-[#E6E5E1] bg-white p-8">
                        <p class="text-sm font-semibold text-[#1A46FF]">Mastercard</p>
                        <h3 class="mt-2 text-xl font-bold text-[#12131A]">Карта для оплаты в интернете</h3>
                        <p class="mt-6 text-3xl font-extrabold text-[#12131A]">[X ₽]</p>
                        <p class="text-sm text-[#12131A]/50">разово за выпуск · 0 ₽ в месяц</p>
                        <ul class="mt-6 space-y-3 text-sm text-[#12131A]/70">
                            <li>— Работает на любом сайте, принимающем Mastercard</li>
                            <li>— Курс и сумма пополнения видны до оплаты</li>
                            <li>— История и статус по каждой операции</li>
                        </ul>
                        <a href="#" class="mt-8 block rounded-lg border border-[#12131A]/15 py-3 text-center text-sm font-semibold text-[#12131A] transition hover:border-[#12131A]/30">Выпустить карту</a>
                    </div>

                    <div data-reveal style="--reveal-delay:100ms" class="relative rounded-3xl border-2 border-[#1A46FF] bg-white p-8">
                        <p class="text-sm font-semibold text-[#1A46FF]">Visa</p>
                        <h3 class="mt-2 text-xl font-bold text-[#12131A]">Карта с Apple Pay и Google Pay</h3>
                        <p class="mt-6 text-3xl font-extrabold text-[#12131A]">[X ₽]</p>
                        <p class="text-sm text-[#12131A]/50">разово за выпуск · 0 ₽ в месяц</p>
                        <ul class="mt-6 space-y-3 text-sm text-[#12131A]/70">
                            <li>— Бесконтактная оплата за границей</li>
                            <li>— Токенизация вместо передачи номера карты</li>
                            <li>— Работает с Apple Watch</li>
                        </ul>
                        <a href="#" class="mt-8 block rounded-lg bg-[#1A46FF] py-3 text-center text-sm font-semibold text-white transition hover:bg-[#1636CC]">Выпустить карту</a>
                    </div>

                    <div data-reveal style="--reveal-delay:200ms" class="rounded-3xl border border-[#E6E5E1] bg-white p-8">
                        <p class="text-sm font-semibold text-[#1A46FF]">Для бизнеса</p>
                        <h3 class="mt-2 text-xl font-bold text-[#12131A]">Для команд и бизнеса</h3>
                        <p class="mt-6 text-3xl font-extrabold text-[#12131A]">По запросу</p>
                        <p class="text-sm text-[#12131A]/50">оплата по счёту</p>
                        <ul class="mt-6 space-y-3 text-sm text-[#12131A]/70">
                            <li>— Несколько карт на одном балансе компании</li>
                            <li>— Пополнение и оплата по счёту</li>
                            <li>— Закрывающие документы для бухгалтерии</li>
                        </ul>
                        <a href="#support" class="mt-8 block rounded-lg border border-[#12131A]/15 py-3 text-center text-sm font-semibold text-[#12131A] transition hover:border-[#12131A]/30">Обсудить условия</a>
                    </div>
                </div>
                <p class="mt-8 text-center text-sm text-[#12131A]/45">Комиссия за пополнение показывается до перевода и не меняется после подтверждения.</p>
            </div>
        </section>

        {{-- ================= БЕЗОПАСНОСТЬ ================= --}}
        <section id="security" class="bg-[#12131A] px-6 py-24 text-white lg:px-8">
            <div class="mx-auto max-w-4xl text-center">
                <h2 data-reveal class="text-2xl font-bold tracking-tight sm:text-3xl">Стандарты, которые мы не готовы снижать</h2>

                <div class="mt-14 grid gap-8 text-left sm:grid-cols-2">
                    @foreach ([
                        'Карты выпускает партнёр-эмитент по лицензии платёжной системы',
                        'Проверка личности при выпуске карты',
                        'Мониторинг операций по внутренним риск-правилам в реальном времени',
                        'Реквизиты карты доступны только держателю и не хранятся в открытом виде',
                    ] as $i => $point)
                        <div data-reveal style="--reveal-delay: {{ $i * 90 }}ms" class="flex items-start gap-4 border-t border-white/10 pt-6">
                            <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-5 w-5 flex-shrink-0 text-[#5B84FF]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M5 13l4 4L19 7"/></svg>
                            <p class="text-sm leading-relaxed text-white/75">{{ $point }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ================= FAQ ================= --}}
        <section id="faq" class="px-6 py-24 lg:px-8">
            <div class="mx-auto max-w-3xl">
                <h2 data-reveal class="text-center text-2xl font-bold tracking-tight text-[#12131A] sm:text-3xl">Вопросы, которые обычно задают перед выпуском карты</h2>

                <div class="mt-12 divide-y divide-[#E6E5E1] border-y border-[#E6E5E1]">
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
                                <span class="text-base font-semibold text-[#12131A]">{{ $faq['q'] }}</span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="faq-chevron h-5 w-5 flex-shrink-0 text-[#12131A]/40 transition-transform duration-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
                            </button>
                            <div class="faq-answer">
                                <p class="pr-8 pt-3 text-sm leading-relaxed text-[#12131A]/60">{{ $faq['a'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ================= ПОДДЕРЖКА ================= --}}
        <section id="support" class="px-6 pb-28 lg:px-8">
            <div data-reveal class="mx-auto max-w-4xl rounded-3xl bg-[#F1F1EE] px-8 py-16 text-center">
                <h2 class="text-2xl font-bold tracking-tight text-[#12131A] sm:text-3xl">Остался вопрос?</h2>
                <p class="mx-auto mt-3 max-w-md text-base text-[#12131A]/60">Напишите в поддержку — отвечаем в приложении и на почте.</p>
                <a href="mailto:support@example.com" class="mt-8 inline-flex rounded-lg bg-[#1A46FF] px-6 py-3.5 text-sm font-semibold text-white transition hover:bg-[#1636CC]">Написать в поддержку</a>
            </div>
        </section>
    </main>

    {{-- ================= ПОДВАЛ ================= --}}
    <footer class="border-t border-[#E6E5E1] px-6 py-16 lg:px-8">
        <div class="mx-auto max-w-7xl">
            <div class="grid gap-12 lg:grid-cols-[1.4fr_1fr_1fr_1fr]">
                <div>
                    <p class="text-lg font-extrabold tracking-tight text-[#12131A]">[Бренд]</p>
                    <p class="mt-3 max-w-xs text-sm leading-relaxed text-[#12131A]/55">[Бренд] — виртуальные карты Mastercard и Visa для платежей за рубежом.</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-[#12131A]/40">Продукт</p>
                    <ul class="mt-4 space-y-2.5 text-sm text-[#12131A]/65">
                        <li><a href="#features" class="hover:text-[#12131A]">Возможности</a></li>
                        <li><a href="#pricing" class="hover:text-[#12131A]">Тарифы</a></li>
                        <li><a href="#partners" class="hover:text-[#12131A]">Партнёрам</a></li>
                        <li><a href="#faq" class="hover:text-[#12131A]">Вопросы</a></li>
                    </ul>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-[#12131A]/40">Аккаунт</p>
                    <ul class="mt-4 space-y-2.5 text-sm text-[#12131A]/65">
                        <li><a href="#" class="hover:text-[#12131A]">Регистрация</a></li>
                        <li><a href="#" class="hover:text-[#12131A]">Вход</a></li>
                        <li><a href="#support" class="hover:text-[#12131A]">Поддержка</a></li>
                    </ul>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-[#12131A]/40">Документы</p>
                    <ul class="mt-4 space-y-2.5 text-sm text-[#12131A]/65">
                        <li><a href="#" class="hover:text-[#12131A]">Пользовательское соглашение</a></li>
                        <li><a href="#" class="hover:text-[#12131A]">Политика конфиденциальности</a></li>
                        <li><a href="#" class="hover:text-[#12131A]">Политика AML/KYC</a></li>
                        <li><a href="#" class="hover:text-[#12131A]">Тарифы и комиссии</a></li>
                        <li><a href="#" class="hover:text-[#12131A]">Политика возврата</a></li>
                        <li><a href="#" class="hover:text-[#12131A]">Эмитент карт</a></li>
                    </ul>
                </div>
            </div>

            <p class="mt-14 max-w-3xl text-xs leading-relaxed text-[#12131A]/40">
                [Бренд] не является банком и не выпускает карты самостоятельно. Карты эмитирует
                лицензированный партнёр-эмитент; [Бренд] предоставляет интерфейс для выпуска карт,
                управления ими и обработку платежей за услугу.
            </p>
            <p class="mt-6 text-xs text-[#12131A]/35">© {{ date('Y') }} [Бренд]. Все права защищены.</p>
        </div>
    </footer>
</body>
</html>
