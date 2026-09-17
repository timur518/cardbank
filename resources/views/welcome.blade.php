<!DOCTYPE html>
<html lang="ru" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>[Бренд] — виртуальные карты для платежей по всему миру</title>
    <meta name="description" content="Виртуальные карты для оплаты сервисов, подписок и покупок по всему миру. Управление картами полностью онлайн.">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=sofia-sans:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">

<header data-site-header class="site-header sticky top-[40px] z-50 mt-[40px] px-4 lg:px-8">
    <div class="nav-pill mx-auto flex max-w-[1350px] items-center justify-between rounded-full px-5 py-3 sm:px-7">
        <a href="#top" class="brand-mark">[Бренд]</a>
        <nav class="hidden items-center gap-8 text-[16px] text-[#141413] lg:flex">
            <a href="#lifestyle" class="nav-link">Возможности</a>
            <a href="#products" class="nav-link">Карты</a>
            <a href="#how" class="nav-link">Как это работает</a>
            <a href="#partners" class="nav-link">Партнёрам</a>
            <a href="#faq" class="nav-link">Вопросы</a>
        </nav>
        <div class="hidden lg:flex"><a href="#" class="btn btn-primary btn-small">Личный кабинет</a></div>
        <button data-menu-toggle type="button" aria-expanded="false" aria-controls="mobile-menu" class="flex h-10 w-10 items-center justify-center rounded-full bg-black/5 lg:hidden">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h16" /></svg>
        </button>
    </div>
    <div data-mobile-menu id="mobile-menu" class="nav-pill mx-auto mt-2 hidden max-w-[1400px] flex-col gap-1 rounded-3xl px-5 py-4 lg:hidden">
        <a href="#lifestyle" class="rounded-xl px-3 py-2.5 text-[16px] text-[#141413]">Возможности</a>
        <a href="#products" class="rounded-xl px-3 py-2.5 text-[16px] text-[#141413]">Карты</a>
        <a href="#how" class="rounded-xl px-3 py-2.5 text-[16px] text-[#141413]">Как это работает</a>
        <a href="#partners" class="rounded-xl px-3 py-2.5 text-[16px] text-[#141413]">Партнёрам</a>
        <a href="#faq" class="rounded-xl px-3 py-2.5 text-[16px] text-[#141413]">Вопросы</a>
        <a href="#" class="btn btn-primary mt-2 justify-center text-sm">Личный кабинет</a>
    </div>
</header>

<main id="top" class="bg-[#f3f0ee]">
    {{-- ================= HERO ================= --}}
    <section class="px-6 pt-[20px] lg:px-10 lg:-mt-[115px]">
        <div class="mx-auto max-w-[1400px]">
            <div class="relative isolate flex h-[460px] items-end overflow-hidden rounded-[32px] sm:h-[560px] lg:h-[750px] lg:rounded-[40px]">
                <img
                    src="assets/images/herobg.png"
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
                            <a href="#products" class="btn btn-hero-orange">Оформить карту онлайн</a>
                            <a href="#" class="btn btn-hero-black">Войти в аккаунт</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ================= LIFESTYLE / OVAL CAROUSEL ================= --}}
    <section id="lifestyle" class="overflow-hidden py-28 lg:py-40" data-carousel>
        <div class="mx-auto max-w-[1400px] px-6 lg:px-10">
            <div class="grid gap-8 lg:grid-cols-[1.15fr_.85fr] lg:items-start">
                <div data-reveal>
                    <h2 class="max-w-4xl text-[clamp(2.8rem,5vw,5.4rem)] font-semibold leading-[.94] tracking-[-.045em]">Виртуальные карты для платежей и покупок</h2>
                </div>
                <div data-reveal>
                    <p class="mt-4 max-w-lg text-[20px] font-normal leading-relaxed text-[#141414]">Удобный способ совершать покупки из России и СНГ</p>
                    <p class="max-w-lg text-base leading-relaxed text-[#141413]/45">От подписки на ИИ-сервис до отеля в отпуске — просто</p>
                </div>
            </div>
        </div>

        @php
            $scenes = [
                ['tag'=>'ИИ-сервисы', 'title'=>'Оплата ИИ-сервисов и подписок', 'img'=>'https://web.archive.org/web/20260913045546im_/https://www.mastercard.com/adobe/dynamicmedia/deliver/dm-aid--e6176780-42de-4e72-b550-906d08a82da0/agentic-commerce-standards-hero.jpg?quality=84&preferwebp=true'],
                ['tag'=>'Онлайн-шопинг', 'title'=>'Покупки в зарубежных онлайн-магазинах', 'img'=>'https://web.archive.org/web/20260913045549im_/https://www.mastercard.com/adobe/dynamicmedia/deliver/dm-aid--efc24a51-bf03-4d56-a493-964b269c6aeb/br45724-mastercard-websiteimageryrefresh-solutions-3-9x16.jpg?quality=84&preferwebp=true'],
                ['tag'=>'Путешествия', 'title'=>'Отели, билеты и поездки за границей', 'img'=>'https://images.unsplash.com/photo-1773393776477-61773dfc8a09?q=84&w=1600&auto=format&fit=crop'],
                ['tag'=>'На кассе', 'title'=>'Оплата телефоном в кафе и ресторанах', 'img'=>'https://web.archive.org/web/20260913045546im_/https://www.mastercard.com/adobe/dynamicmedia/deliver/dm-aid--31deb226-0992-429e-8fae-9bccb16fc665/br45724-mastercard-websiteimageryrefresh-solutions-4-1x1.jpg?quality=84&preferwebp=true'],
                ['tag'=>'Покупки', 'title'=>'Одна карта — множество сценариев', 'img'=>'https://images.unsplash.com/photo-1758519289594-8e0444825b04?q=84&w=1400&auto=format&fit=crop'],
            ];
        @endphp
        <div class="oval-carousel mt-20" data-carousel-track tabindex="0" aria-label="Сценарии использования">
            @foreach ($scenes as $i => $scene)
                <article data-carousel-item data-reveal style="--reveal-delay: {{ $i * 80 }}ms" class="oval-slide group">
                    <img src="{{ $scene['img'] }}" alt="{{ $scene['title'] }}" loading="lazy" class="absolute inset-0 h-full w-full object-cover transition duration-1000 ease-out group-hover:scale-105">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/10 to-black/5"></div>
                    <div class="oval-slide-badges">
                        <span class="rounded-full bg-white/90 px-4 py-2 text-xs font-bold uppercase tracking-[.08em] text-[#141413]">{{ $scene['tag'] }}</span>
                        <span class="rounded-full bg-white px-6 py-3 text-center text-lg font-semibold leading-snug tracking-[-.01em] text-[#141413] sm:text-xl">{{ $scene['title'] }}</span>
                    </div>
                </article>
            @endforeach
        </div>
        <div class="mx-auto mt-8 flex max-w-[1400px] items-center justify-between px-6 lg:px-10">
            <div class="carousel-dots" data-carousel-dots></div>
            <div class="flex gap-2">
                <button type="button" data-carousel-prev aria-label="Предыдущий слайд" class="carousel-arrow">←</button>
                <button type="button" data-carousel-next aria-label="Следующий слайд" class="carousel-arrow">→</button>
            </div>
        </div>
    </section>

    {{-- ================= PRODUCTS ================= --}}
    <section id="products" class="px-6 py-28 lg:px-10 lg:py-40">
        <div class="mx-auto max-w-[1400px]">
            <div data-reveal class="max-w-4xl">
                <span class="eyebrow">Только три карты</span>
                <h2 class="mt-5 text-[clamp(2.8rem,5vw,5.4rem)] font-medium leading-[.94] tracking-[-.045em]">Карты, созданные <br> под основные задачи</h2>
            </div>

            @php
                $products = [
                    [
                        'class'=>'product-black', 'eyebrow'=>'CARD BLACK', 'title'=>'Для интернета. Подписок. Сервисов.',
                        'desc'=>'Главная карта для онлайн платежей. Оплата ИИ-сервисов, облачных платформ, рекламы, подписок и зарубежных интернет-магазинов — без физического пластика.',
                        'points'=>['Оформление за 3 минуты','Пополнение Российской картой или по СБП','Обслуживание - бесплатно'], 'bgImage'=>'blackcardbg.png', 'cta'=>'Оформить карту Black'
                    ],
                    [
                        'class'=>'product-orange', 'eyebrow'=>'CARD ORANGE', 'title'=>'Для путешествий. Телефона. Покупок.',
                        'desc'=>'Карта для жизни вне экрана. Добавляйте в Apple Pay и Google Pay, оплачивайте покупки телефоном или часами в кафе, ресторанах, отелях и магазинах.',
                        'points'=>['Apple Pay и Google Pay','Бесконтактная оплата по NFC','Работа с Apple Watch и Wear OS'], 'bgImage'=>'orangecardbg.png', 'cta'=>'Оформить Card Orange'
                    ],
                ];
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
                            <ul class="mt-8 grid gap-4 sm:grid-cols-3">
                                @foreach ($product['points'] as $point)
                                    <li class="flex gap-3 text-sm font-semibold leading-relaxed"><span class="mt-2 h-2 w-2 flex-shrink-0 rounded-full bg-current"></span>{{ $point }}</li>
                                @endforeach
                            </ul>
                            <div class="mt-10 flex flex-wrap items-center gap-5 border-t border-current/15 pt-7">
                                <a href="#" class="btn {{ $i === 0 ? 'btn-hero-orange' : 'btn-dark-on-orange' }}">{{ $product['cta'] }}</a>
                                <span class="text-sm opacity-50">[X ₽] за выпуск · 0 ₽ в месяц</span>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ================= HOW ================= --}}
    <section id="how" class="editorial-dark px-6 py-28 lg:px-10 lg:py-40">
        <div class="mx-auto max-w-[1400px]">
            <div class="grid gap-10 lg:grid-cols-[1fr_.65fr] lg:items-end">
                <div data-reveal><span class="eyebrow !text-[#f37338]">Как это работает</span><h2 class="mt-5 text-[clamp(2.8rem,5vw,5.4rem)] font-medium leading-[.94] tracking-[-.045em] text-white">От регистрации до первой оплаты — четыре шага</h2></div>
                <p data-reveal class="text-lg leading-relaxed text-white/55">Никаких офисов и пластика. Всё необходимое — в одном цифровом интерфейсе.</p>
            </div>
            <div class="mt-20 grid gap-px overflow-hidden rounded-[32px] bg-white/10 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ([['01','Регистрация','Укажите данные и пройдите проверку личности.'],['02','Выбор карты','Выберите карту под свой сценарий использования.'],['03','Пополнение','Пополните баланс через СБП или картой российского банка.'],['04','Оплата','Получите реквизиты или добавьте карту в кошелёк.']] as $i => $step)
                    <div data-reveal style="--reveal-delay: {{ $i * 90 }}ms" class="how-card"><span>{{ $step[0] }}</span><h3>{{ $step[1] }}</h3><p>{{ $step[2] }}</p></div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ================= FEATURES ================= --}}
    <section id="features" class="px-6 py-28 lg:px-10 lg:py-40">
        <div class="mx-auto max-w-[1400px]">
            <div data-reveal class="max-w-4xl"><span class="eyebrow">Контроль</span><h2 class="mt-5 text-[clamp(2.8rem,5vw,5.4rem)] font-medium leading-[.94] tracking-[-.045em]">Всё важное — под рукой</h2></div>
            <div class="feature-grid mt-16 lg:mt-24">
                @foreach ([['shield','Безопасность','Мониторинг операций и контроль каждой транзакции в реальном времени.'],['lock','Реквизиты','Данные карты доступны только держателю через защищённый интерфейс.'],['pulse','Баланс','Актуальное состояние счёта и операции видны без задержек.'],['nodes','Гибкость','Несколько карт под разные сервисы, подписки и поездки.']] as $i => $f)
                    <div data-reveal style="--reveal-delay: {{ $i * 80 }}ms" class="feature-card"><div class="feature-icon">@if($f[0]==='shield')🛡️@elseif($f[0]==='lock')🔒@elseif($f[0]==='pulse')↗@else◎@endif</div><h3>{{ $f[1] }}</h3><p>{{ $f[2] }}</p><span class="feature-number">0{{ $i + 1 }}</span></div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ================= APP ================= --}}
    <section class="px-6 pb-28 lg:px-10 lg:pb-40">
        <div class="app-panel mx-auto max-w-[1400px] overflow-hidden rounded-[40px] bg-white">
            <div class="grid lg:grid-cols-[1fr_.75fr]">
                <div data-reveal class="p-10 sm:p-14 lg:p-20"><span class="eyebrow">Личный кабинет</span><h2 class="mt-5 max-w-2xl text-[clamp(2.8rem,5vw,5rem)] font-medium leading-[.94] tracking-[-.045em]">Управляйте картами в одном месте</h2><p class="mt-7 max-w-xl text-lg leading-relaxed text-[#141413]/60">История операций, баланс, заморозка, реквизиты и уведомления — без лишних экранов и звонков.</p><div class="mt-9 grid gap-4 sm:grid-cols-2">@foreach(['Несколько карт','История операций','Заморозка в одно касание','Push-уведомления'] as $item)<div class="rounded-2xl bg-[#f3f0ee] p-5 text-sm font-semibold">{{ $item }}</div>@endforeach</div></div>
                <div data-reveal class="app-visual flex items-center justify-center p-10 lg:p-16"><div class="phone-shell"><div class="phone-screen"><div class="flex items-center justify-between text-xs text-white/40"><span>Сегодня</span><span>•••</span></div><p class="mt-5 text-xs text-white/40">Баланс</p><p class="text-3xl font-bold text-white">$1,000.00</p><div class="mini-card mini-black"><span>•••• 4821</span><b>MC</b></div><div class="mini-card mini-orange"><span>•••• 5678</span><b>VISA</b></div><p class="mt-10 text-[10px] font-bold uppercase tracking-[.18em] text-white/35">Операции</p>@foreach(['Пополнение · СБП','Подписка · сервис','Отель · путешествие'] as $tx)<div class="mt-4 flex justify-between border-b border-white/10 pb-3 text-xs"><span class="text-white/60">{{ $tx }}</span><span class="text-white">−$20.00</span></div>@endforeach</div></div></div>
            </div>
        </div>
    </section>

    {{-- ================= TOP-UP ================= --}}
    <section class="px-6 py-28 lg:px-10 lg:py-40">
        <div class="mx-auto grid max-w-[1400px] gap-16 lg:grid-cols-[.9fr_1.1fr] lg:items-center">
            <div data-reveal><span class="eyebrow">Пополнение</span><h2 class="mt-5 text-[clamp(2.8rem,5vw,5rem)] font-medium leading-[.94] tracking-[-.045em]">Курс и сумма известны заранее</h2><p class="mt-7 max-w-xl text-lg leading-relaxed text-[#141413]/60">Курс конвертации и комиссия показываются до подтверждения. После оплаты сумма не меняется.</p></div>
            <div data-reveal class="topup-card"><div class="flex justify-between text-sm font-semibold"><span>Пополнить баланс</span><span class="opacity-40">Card •• 5678</span></div><div class="mt-12 flex items-end justify-between"><span class="text-7xl font-semibold tracking-[-.06em]">100</span><span class="mb-3 rounded-full bg-[#f3f0ee] px-4 py-2 text-sm font-bold">USD</span></div><p class="mt-3 text-sm opacity-40">Курс: 1 $ ≈ 100,53 ₽</p><div class="mt-8 space-y-4 border-t border-[#141413]/10 pt-6 text-sm"><div class="flex justify-between opacity-55"><span>Зачислим на карту</span><span>$100,00</span></div><div class="flex justify-between text-lg font-bold"><span>Итого к оплате</span><span>10 053 ₽</span></div></div><button type="button" class="btn btn-primary mt-8 w-full">Перейти к оплате</button></div>
        </div>
    </section>

    {{-- ================= PARTNERS ================= --}}
    <section id="partners" class="px-6 py-28 lg:px-10 lg:py-40">
        <div class="mx-auto max-w-[1400px] rounded-[40px] bg-[#f37338] px-8 py-16 sm:px-12 lg:px-20 lg:py-24">
            <div class="grid gap-12 lg:grid-cols-[1fr_.8fr] lg:items-end"><div data-reveal><span class="eyebrow !text-[#141413]">Партнёрам</span><h2 class="mt-5 max-w-4xl text-[clamp(2.8rem,5vw,5.4rem)] font-medium leading-[.94] tracking-[-.045em]">Рекомендуйте сервис и получайте вознаграждение</h2></div><div data-reveal><p class="text-lg leading-relaxed text-[#141413]/65">Приглашённый пользователь закрепляется навсегда. Начисления считаются автоматически.</p><a href="#" class="btn btn-primary mt-8">Получить партнёрскую ссылку</a></div></div>
            <div class="mt-16 grid gap-px overflow-hidden rounded-3xl bg-black/10 sm:grid-cols-3">@foreach ([['[X]%','с каждого пополнения'],['[X]%','с выпуска карты'],['24/7','автоматические начисления']] as $stat)<div class="bg-white/15 p-8"><p class="text-5xl font-semibold tracking-[-.04em]">{{ $stat[0] }}</p><p class="mt-3 text-sm font-semibold opacity-60">{{ $stat[1] }}</p></div>@endforeach</div>
        </div>
    </section>

    {{-- ================= SECURITY ================= --}}
    <section id="security" class="editorial-dark mx-4 rounded-[40px] px-6 py-28 text-white sm:mx-8 lg:px-10 lg:py-40">
        <div class="mx-auto max-w-[1200px]"><div data-reveal class="max-w-4xl"><span class="eyebrow !text-[#f37338]">Безопасность</span><h2 class="mt-5 text-[clamp(2.8rem,5vw,5.4rem)] font-medium leading-[.94] tracking-[-.045em]">Цифровой продукт, в котором важен контроль</h2></div><div class="mt-16 grid gap-px overflow-hidden rounded-3xl bg-white/10 sm:grid-cols-2">@foreach(['Карты выпускает лицензированный партнёр-эмитент','Проверка личности при выпуске','Мониторинг операций в реальном времени','Реквизиты доступны только держателю'] as $i => $point)<div data-reveal style="--reveal-delay: {{ $i * 80 }}ms" class="bg-white/[.035] p-8"><span class="text-sm font-bold text-[#f37338]">0{{ $i + 1 }}</span><p class="mt-8 max-w-md text-lg leading-relaxed text-white/75">{{ $point }}</p></div>@endforeach</div></div>
    </section>

    {{-- ================= FAQ ================= --}}
    <section id="faq" class="px-6 py-28 lg:px-10 lg:py-40"><div class="mx-auto max-w-[1100px]"><div data-reveal class="max-w-4xl"><span class="eyebrow">Вопросы</span><h2 class="mt-5 text-[clamp(2.8rem,5vw,5.2rem)] font-medium leading-[.94] tracking-[-.045em]">Перед выпуском карты</h2></div>@php $faqs=[['Это законно?','Карту выпускает партнёр-эмитент по лицензии платёжной системы.'],['Что если платёж не проходит?','В приложении видна причина отказа; поддержка помогает разобраться с конкретной операцией.'],['Где карта не сработает?','Карта предназначена для международных онлайн- и офлайн-платежей в пределах доступности конкретного сервиса или торговой точки.'],['Какие данные нужны?','Только данные, которые требуются партнёру-эмитенту для выпуска карты.'],['Как быстро приходят реквизиты?','После подтверждения выпуска реквизиты появляются в приложении.'],['Сколько карт можно держать?','Можно выпускать отдельные карты под разные задачи в рамках доступных лимитов.']]; @endphp<div class="mt-14 divide-y divide-[#141413]/10 border-y border-[#141413]/10">@foreach($faqs as $faq)<div data-faq-item data-open="false" class="faq-item"><button type="button" data-faq-button aria-expanded="false" class="flex w-full items-center justify-between gap-6 py-7 text-left"><span class="text-xl font-semibold tracking-[-.02em]">{{ $faq[0] }}</span><span class="faq-plus">+</span></button><div class="faq-answer"><p class="max-w-3xl pb-7 pr-12 text-base leading-relaxed text-[#141413]/55">{{ $faq[1] }}</p></div></div>@endforeach</div></div></section>

    <section id="support" class="px-6 pb-32 lg:px-10"><div data-reveal class="mx-auto max-w-[1000px] rounded-[40px] bg-[#141413] px-8 py-20 text-center text-white sm:px-12"><span class="eyebrow justify-center !text-[#f37338]">Поддержка</span><h2 class="mt-5 text-[clamp(2.8rem,5vw,5rem)] font-medium leading-[.94] tracking-[-.045em]">Остался вопрос?</h2><p class="mx-auto mt-6 max-w-md text-lg text-white/55">Напишите в поддержку — поможем разобраться с выпуском и использованием карты.</p><a href="mailto:support@example.com" class="btn btn-ondark mt-9">Написать в поддержку</a></div></section>
</main>

<footer class="border-t border-[#141413]/10 bg-[#f3f0ee] px-6 py-16 lg:px-10"><div class="mx-auto max-w-[1400px]"><div class="grid gap-12 lg:grid-cols-[1.5fr_1fr_1fr_1fr]"><div><p class="brand-mark">[Бренд]</p><p class="mt-4 max-w-sm text-sm leading-relaxed text-[#141413]/50">Виртуальные карты для платежей, подписок и покупок по всему миру.</p></div><div><p class="footer-title">Продукт</p><ul class="footer-links"><li><a href="#lifestyle">Возможности</a></li><li><a href="#products">Карты</a></li><li><a href="#how">Как это работает</a></li></ul></div><div><p class="footer-title">Помощь</p><ul class="footer-links"><li><a href="#faq">Вопросы</a></li><li><a href="#support">Поддержка</a></li><li><a href="#">Вход</a></li></ul></div><div><p class="footer-title">Документы</p><ul class="footer-links"><li><a href="#">Пользовательское соглашение</a></li><li><a href="#">Политика конфиденциальности</a></li><li><a href="#">AML / KYC</a></li><li><a href="#">Тарифы</a></li></ul></div></div><p class="mt-16 max-w-4xl text-xs leading-relaxed text-[#141413]/35">[Бренд] не является банком и не выпускает карты самостоятельно. Карты эмитирует лицензированный партнёр-эмитент.</p><p class="mt-5 text-xs text-[#141413]/30">© {{ date('Y') }} [Бренд]. Все права защищены.</p></div></footer>
</body>
</html>
