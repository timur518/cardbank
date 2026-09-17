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
        <a href="#top" class="brand-mark"><img src="assets/images/logo.png" width="55px"></a>
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
                        'class'=>'product-black', 'eyebrow'=>'Карта BLACK', 'title'=>'Для интернета. Подписок. Сервисов.',
                        'desc'=>'Главная карта для онлайн платежей. Оплата ИИ-сервисов, облачных платформ, рекламы, подписок и зарубежных интернет-магазинов — без физического пластика.',
                        'points'=>['Оформление за 3 минуты','Пополнение Российской картой или по СБП','Обслуживание - бесплатно'], 'bgImage'=>'blackcardbg.png', 'cta'=>'Оформить карту Black',
                        'price' => '990',
                    ],
                    [
                        'class'=>'product-orange', 'eyebrow'=>'Карта ORANGE', 'title'=>'Для путешествий. Телефона. Покупок.',
                        'desc'=>'Карта для жизни вне экрана. Добавляйте в Apple Pay и Google Pay, оплачивайте покупки телефоном или часами в кафе, ресторанах, отелях и магазинах.',
                        'points'=>['Поддерживает привязку к Apple Pay и Google Pay','Можно платить в магазинах и кафе','Работает с Apple Watch и Wear OS'], 'bgImage'=>'orangecardbg.png', 'cta'=>'Оформить карту Orange',
                        'price' => '3 490',
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
                                <span class="text-sm opacity-50">{{ $product['price'] }} ₽ за выпуск · 0 ₽ в месяц</span>
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
                <div data-reveal><span class="eyebrow !text-[#f37338]">Онлайн за 3 минуты</span><h2 class="mt-5 text-[clamp(2.8rem,5vw,5.4rem)] font-medium leading-[.94] tracking-[-.045em] text-white">Простое и быстрое оформление карт</h2></div>
                <div data-reveal>
                    <p class="text-lg leading-relaxed text-white/55">Никаких офисов и пластика. Всё необходимое — здесь.</p>
                    <a href="#" class="btn btn-hero-orange mt-8">Зарегистрироваться</a>
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
    <section id="features" class="px-6 py-28 lg:px-10 lg:py-40">
        <div class="mx-auto max-w-[1400px]">
            <div data-reveal class="max-w-4xl"><span class="eyebrow">Для любых ситуаций</span><h2 class="mt-5 text-[clamp(2.8rem,5vw,5.4rem)] font-medium leading-[.94] tracking-[-.045em]">Что оплатить?</h2></div>
            @php
                $services = [
                    ['#10A37F','ChatGPT','<path d="M22.282 9.821a5.985 5.985 0 0 0-.516-4.91 6.046 6.046 0 0 0-6.51-2.9A6.065 6.065 0 0 0 4.981 4.18a5.985 5.985 0 0 0-3.998 2.9 6.046 6.046 0 0 0 .743 7.097 5.98 5.98 0 0 0 .51 4.911 6.051 6.051 0 0 0 6.515 2.9A5.985 5.985 0 0 0 13.26 24a6.056 6.056 0 0 0 5.772-4.206 5.99 5.99 0 0 0 3.997-2.9 6.056 6.056 0 0 0-.747-7.073zM13.26 22.43a4.476 4.476 0 0 1-2.876-1.04l.141-.081 4.779-2.758a.795.795 0 0 0 .392-.681v-6.737l2.02 1.168a.071.071 0 0 1 .038.052v5.583a4.504 4.504 0 0 1-4.494 4.494zM3.6 18.304a4.47 4.47 0 0 1-.535-3.014l.142.085 4.783 2.758a.771.771 0 0 0 .78 0l5.843-3.369v2.332a.08.08 0 0 1-.033.062L9.74 19.95a4.5 4.5 0 0 1-6.14-1.646zM2.34 7.896a4.485 4.485 0 0 1 2.366-1.973V11.6a.766.766 0 0 0 .388.677l5.815 3.354-2.02 1.168a.076.076 0 0 1-.071 0l-4.83-2.786A4.504 4.504 0 0 1 2.34 7.872zm16.597 3.855l-5.833-3.387L15.119 7.2a.076.076 0 0 1 .071 0l4.83 2.791a4.494 4.494 0 0 1-.676 8.105v-5.678a.79.79 0 0 0-.407-.667zm2.01-3.023l-.141-.085-4.774-2.782a.776.776 0 0 0-.785 0L9.409 9.23V6.897a.066.066 0 0 1 .028-.061l4.83-2.787a4.5 4.5 0 0 1 6.68 4.66zm-12.64 4.135l-2.02-1.164a.08.08 0 0 1-.038-.057V6.075a4.5 4.5 0 0 1 7.375-3.453l-.142.08-4.778 2.758a.795.795 0 0 0-.393.681zm1.097-2.365l2.602-1.5 2.607 1.5v3l-2.597 1.5-2.607-1.5z"/>'],
                    ['#C15F3C','Claude','<path d="M17.3041 3.541h-3.6718l6.696 16.918H24Zm-10.6082 0L0 20.459h3.7442l1.3693-3.5527h7.0052l1.3693 3.5528h3.7442L10.5363 3.5409Zm-.3712 10.2232 2.2914-5.9456 2.2914 5.9456Z"/>'],
                    ['#C7C7C7','Cursor','<path d="M11.503.131 1.891 5.678a.84.84 0 0 0-.42.726v11.188c0 .3.162.575.42.724l9.609 5.55a1 1 0 0 0 .998 0l9.61-5.55a.84.84 0 0 0 .42-.724V6.404a.84.84 0 0 0-.42-.726L12.497.131a1.01 1.01 0 0 0-.996 0M2.657 6.338h18.55c.263 0 .43.287.297.515L12.23 22.918c-.062.107-.229.064-.229-.06V12.335a.59.59 0 0 0-.295-.51l-9.11-5.257c-.109-.063-.064-.23.061-.23"/>'],
                    ['#C9C9C9','Midjourney','<path d="M21.6 15.7c-1.2 1-2.7 1.8-4.4 2.4-2.6.9-5.3 1.2-7.6 1-2.6-.2-4.6-1-5.7-2.3-.2-.2-.5-.2-.7 0-.2.2-.2.5 0 .7 1.3 1.5 3.6 2.4 6.3 2.6 2.5.2 5.3-.1 8-1.1 1.9-.7 3.6-1.6 4.9-2.7.2-.2.2-.5 0-.7-.2-.2-.5-.2-.8.1zM3.6 13.9c1.5.9 3.6 1.3 5.9 1.1 2.4-.2 4.7-1 6.6-2.2.2-.1.3-.4.2-.6l-.1-.1c-1.9-2.3-3.7-4-5.5-5.2C8.9 5.7 7.2 5.1 5.7 5.2c-1.4.1-2.5.7-3.2 1.8-.7 1.1-.9 2.5-.6 4 .3 1.2.9 2.2 1.7 2.9zm14.1-3.3c.7.5 1.4 1.1 2.1 1.8.2.2.5.2.7 0 .2-.2.2-.5 0-.7-1.8-1.9-3.6-3.2-5.4-4-1.9-.8-3.7-1-5.2-.6-.3.1-.4.4-.3.6.1.3.4.4.6.3 1.3-.3 2.8-.1 4.5.6 1 .4 2 1 3 1.7z"/>'],
                    ['#20B8CD','Perplexity','<path d="M22.3977 7.0896h-2.3106V.0676l-7.5094 6.3542V.1577h-1.1554v6.1966L4.4904 0v7.0896H1.6023v10.3976h2.8882V24l6.932-6.3591v6.2005h1.1554v-6.0469l6.9318 6.1807v-6.4879h2.8882V7.0896zm-3.4657-4.531v4.531h-5.355l5.355-4.531zm-13.2862.0676 4.8691 4.4634H5.6458V2.6262zM2.7576 16.332V8.245h7.8476l-6.1149 6.1147v1.9723H2.7576zm2.8882 5.0404v-3.8852h.0001v-2.6488l5.7763-5.7764v7.0111l-5.7764 5.2993zm12.7086.0248-5.7766-5.1509V9.0618l5.7766 5.7766v6.5588zm2.8882-5.0652h-1.733v-1.9723L13.3948 8.245h7.8478v8.087z"/>'],
                    ['#8E7CF8','Google Gemini','<path d="M11.04 19.32Q12 21.51 12 24q0-2.49.93-4.68.96-2.19 2.58-3.81t3.81-2.55Q21.51 12 24 12q-2.49 0-4.68-.93a12.3 12.3 0 0 1-3.81-2.58 12.3 12.3 0 0 1-2.58-3.81Q12 2.49 12 0q0 2.49-.96 4.68-.93 2.19-2.55 3.81a12.3 12.3 0 0 1-3.81 2.58Q2.49 12 0 12q2.49 0 4.68.96 2.19.93 3.81 2.55t2.55 3.81"/>'],
                    ['#E50914','Netflix','<path d="m5.398 0 8.348 23.602c2.346.059 4.856.398 4.856.398L10.113 0H5.398zm8.489 0v9.172l4.715 13.33V0h-4.715zM5.398 1.5V24c1.873-.225 2.81-.312 4.715-.398V14.83L5.398 1.5z"/>'],
                    ['#1DB954','Spotify','<path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.78.241 1.2zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.559.3z"/>'],
                    ['#FF0000','YouTube Premium','<path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>'],
                    ['#00AFF0','OnlyFans','<path d="M24 4.003h-4.015c-3.45 0-5.3.197-6.748 1.957a7.996 7.996 0 1 0 2.103 9.211c3.182-.231 5.39-2.134 6.085-5.173 0 0-2.399.585-4.43 0 4.018-.777 6.333-3.037 7.005-5.995zM5.61 11.999A2.391 2.391 0 0 1 9.28 9.97a2.966 2.966 0 0 1 2.998-2.528h.008c-.92 1.778-1.407 3.352-1.998 5.263A2.392 2.392 0 0 1 5.61 12Zm2.386-7.996a7.996 7.996 0 1 0 7.996 7.996 7.996 7.996 0 0 0-7.996-7.996Zm0 10.394A2.399 2.399 0 1 1 10.395 12a2.396 2.396 0 0 1-2.399 2.398Z"/>'],
                    ['#66C0F4','Steam','<path d="M11.979 0C5.678 0 .511 4.86.022 11.037l6.432 2.658c.545-.371 1.203-.59 1.912-.59.063 0 .125.004.188.006l2.861-4.142V8.91c0-2.495 2.028-4.524 4.524-4.524 2.494 0 4.524 2.031 4.524 4.527s-2.03 4.525-4.524 4.525h-.105l-4.076 2.911c0 .052.004.105.004.159 0 1.875-1.515 3.396-3.39 3.396-1.635 0-3.016-1.173-3.331-2.727L.436 15.27C1.862 20.307 6.486 24 11.979 24c6.627 0 11.999-5.373 11.999-12S18.605 0 11.979 0zM7.54 18.21l-1.473-.61c.262.543.714.999 1.314 1.25 1.297.539 2.793-.076 3.332-1.375.263-.63.264-1.319.005-1.949s-.75-1.121-1.377-1.383c-.624-.26-1.29-.249-1.878-.03l1.523.63c.956.4 1.409 1.5 1.009 2.455-.397.957-1.497 1.41-2.454 1.012H7.54zm11.415-9.303c0-1.662-1.353-3.015-3.015-3.015-1.665 0-3.015 1.353-3.015 3.015 0 1.665 1.35 3.015 3.015 3.015 1.663 0 3.015-1.35 3.015-3.015zm-5.273-.005c0-1.252 1.013-2.266 2.265-2.266 1.249 0 2.266 1.014 2.266 2.266 0 1.251-1.017 2.265-2.266 2.265-1.253 0-2.265-1.014-2.265-2.265z"/>'],
                    ['#52B043','Xbox Game Pass','<path d="M4.102 21.033C6.211 22.881 8.977 24 12 24c3.026 0 5.789-1.119 7.902-2.967 1.875-1.748-4.062-8.018-7.902-11.27-3.838 3.252-9.775 9.522-7.898 11.27zm11.27-17.972c-1.578-.999-2.581-1.341-3.372-1.341s-1.794.342-3.372 1.341C6.13 4.563 4.102 7.041 4.102 7.041S7.014 4.6 12 9.181c4.986-4.581 7.898-2.14 7.898-2.14s-2.028-2.478-4.526-3.98zM3.001 4.957C1.124 7.318 0 10.327 0 13.6c0 1.624.276 3.183.785 4.634C2.49 14.196 5.65 9.706 7.6 7.6 5.953 5.953 4.102 4.802 3.001 4.957zm17.998 0c-1.101-.155-2.952.996-4.599 2.643 1.95 2.106 5.11 6.596 6.815 10.634A13.53 13.53 0 0024 13.6c0-3.273-1.124-6.282-3.001-8.643z"/>'],
                    ['#7EAEE4','PlayStation Plus','<path d="M8.984 2.596v17.547l3.915 1.261V6.688c0-.69.304-1.151.794-.991.636.18.76.814.76 1.505v5.875c2.441 1.193 4.362-.002 4.362-3.152 0-3.237-1.126-4.675-4.438-5.827-1.307-.448-3.728-1.186-5.39-1.502zm4.656 16.241l6.296-2.275c.715-.258.826-.625.246-.818-.586-.192-1.637-.139-2.357.123l-4.205 1.5V14.98l.24-.085s1.201-.42 2.913-.615c1.696-.18 3.785.03 5.437.661 1.848.601 2.04 1.472 1.576 2.072-.465.6-1.622 1.036-1.622 1.036l-8.544 3.107V18.86zM1.807 18.6c-1.9-.545-2.214-1.668-1.352-2.32.801-.586 2.16-1.052 2.16-1.052l5.615-2.013v2.313L4.205 17c-.705.271-.825.632-.239.826.586.195 1.637.15 2.343-.12L8.247 17v2.074c-.12.03-.256.044-.39.073-1.939.331-3.996.196-6.038-.479z"/>'],
                    ['#5865F2','Discord Nitro','<path d="M20.317 4.3698a19.7913 19.7913 0 00-4.8851-1.5152.0741.0741 0 00-.0785.0371c-.211.3753-.4447.8648-.6083 1.2495-1.8447-.2762-3.68-.2762-5.4868 0-.1636-.3933-.4058-.8742-.6177-1.2495a.077.077 0 00-.0785-.037 19.7363 19.7363 0 00-4.8852 1.515.0699.0699 0 00-.0321.0277C.5334 9.0458-.319 13.5799.0992 18.0578a.0824.0824 0 00.0312.0561c2.0528 1.5076 4.0413 2.4228 5.9929 3.0294a.0777.0777 0 00.0842-.0276c.4616-.6304.8731-1.2952 1.226-1.9942a.076.076 0 00-.0416-.1057c-.6528-.2476-1.2743-.5495-1.8722-.8923a.077.077 0 01-.0076-.1277c.1258-.0943.2517-.1923.3718-.2914a.0743.0743 0 01.0776-.0105c3.9278 1.7933 8.18 1.7933 12.0614 0a.0739.0739 0 01.0785.0095c.1202.099.246.1981.3728.2924a.077.077 0 01-.0066.1276 12.2986 12.2986 0 01-1.873.8914.0766.0766 0 00-.0407.1067c.3604.698.7719 1.3628 1.225 1.9932a.076.076 0 00.0842.0286c1.961-.6067 3.9495-1.5219 6.0023-3.0294a.077.077 0 00.0313-.0552c.5004-5.177-.8382-9.6739-3.5485-13.6604a.061.061 0 00-.0312-.0286zM8.02 15.3312c-1.1825 0-2.1569-1.0857-2.1569-2.419 0-1.3332.9555-2.4189 2.157-2.4189 1.2108 0 2.1757 1.0952 2.1568 2.419 0 1.3332-.9555 2.4189-2.1569 2.4189zm7.9748 0c-1.1825 0-2.1569-1.0857-2.1569-2.419 0-1.3332.9554-2.4189 2.1569-2.4189 1.2108 0 2.1757 1.0952 2.1568 2.419 0 1.3332-.946 2.4189-2.1568 2.4189Z"/>'],
                    ['#FF3B30','Adobe','<path d="M14.072.094H24V24Zm-4.144 0H0V24Zm2.072 8.84L18.34 24h-4.07l-1.886-4.766H7.74Z"/>'],
                    ['#F24E1E','Figma','<path d="M15.852 8.981h-4.588V0h4.588c2.476 0 4.49 2.014 4.49 4.49s-2.014 4.491-4.49 4.491zM12.735 7.51h3.117c1.665 0 3.019-1.355 3.019-3.019s-1.355-3.019-3.019-3.019h-3.117V7.51zm0 1.471H8.148c-2.476 0-4.49-2.014-4.49-4.49S5.672 0 8.148 0h4.588v8.981zm-4.587-7.51c-1.665 0-3.019 1.355-3.019 3.019s1.354 3.02 3.019 3.02h3.117V1.471H8.148zm4.587 15.019H8.148c-2.476 0-4.49-2.014-4.49-4.49s2.014-4.49 4.49-4.49h4.588v8.98zM8.148 8.981c-1.665 0-3.019 1.355-3.019 3.019s1.355 3.019 3.019 3.019h3.117V8.981H8.148zM8.172 24c-2.489 0-4.515-2.014-4.515-4.49s2.014-4.49 4.49-4.49h4.588v4.441c0 2.503-2.047 4.539-4.563 4.539zm-.024-7.51a3.023 3.023 0 0 0-3.019 3.019c0 1.665 1.365 3.019 3.044 3.019 1.705 0 3.093-1.376 3.093-3.068v-2.97H8.148zm7.704 0h-.098c-2.476 0-4.49-2.014-4.49-4.49s2.014-4.49 4.49-4.49h.098c2.476 0 4.49 2.014 4.49 4.49s-2.014 4.49-4.49 4.49zm-.097-7.509c-1.665 0-3.019 1.355-3.019 3.019s1.355 3.019 3.019 3.019h.098c1.665 0 3.019-1.355 3.019-3.019s-1.355-3.019-3.019-3.019h-.098z"/>'],
                    ['#2FA8E8','Telegram Premium','<path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>'],
                    ['#C6CDD0','Apple Services','<path d="M12.152 6.896c-.948 0-2.415-1.078-3.96-1.04-2.04.027-3.91 1.183-4.961 3.014-2.117 3.675-.546 9.103 1.519 12.09 1.013 1.454 2.208 3.09 3.792 3.039 1.52-.065 2.09-.987 3.935-.987 1.831 0 2.35.987 3.96.948 1.637-.026 2.676-1.48 3.676-2.948 1.156-1.688 1.636-3.325 1.662-3.415-.039-.013-3.182-1.221-3.22-4.857-.026-3.04 2.48-4.494 2.597-4.559-1.429-2.09-3.623-2.324-4.39-2.376-2-.156-3.675 1.09-4.61 1.09zM15.53 3.83c.843-1.012 1.4-2.427 1.245-3.83-1.207.052-2.662.805-3.532 1.818-.78.896-1.454 2.338-1.273 3.714 1.338.104 2.715-.688 3.559-1.701"/>'],
                    ['#3D8BFD','Booking.com','<path d="M24 0H0v24h24ZM8.575 6.563h2.658c2.108 0 3.473 1.15 3.473 2.898 0 1.15-.575 1.82-.91 2.108l-.287.263.335.192c.815.479 1.318 1.389 1.318 2.395 0 1.988-1.51 3.257-3.857 3.257H7.449V7.713c0-.623.503-1.126 1.126-1.15zm1.7 1.868c-.479.024-.694.264-.694.79v1.893h1.676c.958 0 1.294-.743 1.294-1.365 0-.815-.503-1.318-1.318-1.318zm-.096 4.36c-.407.071-.598.31-.598.79v2.251h1.868c.934 0 1.509-.55 1.509-1.533 0-.934-.599-1.509-1.51-1.509zm7.737 2.394c.743 0 1.341.599 1.341 1.342a1.34 1.34 0 0 1-1.341 1.341 1.355 1.355 0 0 1-1.341-1.341c0-.743.598-1.342 1.34-1.342z"/>'],
                    ['#FF5A5F','Airbnb','<path d="M12.001 18.275c-1.353-1.697-2.148-3.184-2.413-4.457-.263-1.027-.16-1.848.291-2.465.477-.71 1.188-1.056 2.121-1.056s1.643.345 2.12 1.063c.446.61.558 1.432.286 2.465-.291 1.298-1.085 2.785-2.412 4.458zm9.601 1.14c-.185 1.246-1.034 2.28-2.2 2.783-2.253.98-4.483-.583-6.392-2.704 3.157-3.951 3.74-7.028 2.385-9.018-.795-1.14-1.933-1.695-3.394-1.695-2.944 0-4.563 2.49-3.927 5.382.37 1.565 1.352 3.343 2.917 5.332-.98 1.085-1.91 1.856-2.732 2.333-.636.344-1.245.558-1.828.609-2.679.399-4.778-2.2-3.825-4.88.132-.345.395-.98.845-1.961l.025-.053c1.464-3.178 3.242-6.79 5.285-10.795l.053-.132.58-1.116c.45-.822.635-1.19 1.351-1.643.346-.21.77-.315 1.246-.315.954 0 1.698.558 2.016 1.007.158.239.345.557.582.953l.558 1.089.08.159c2.041 4.004 3.821 7.608 5.279 10.794l.026.025.533 1.22.318.764c.243.613.294 1.222.213 1.858zm1.22-2.39c-.186-.583-.505-1.271-.9-2.094v-.03c-1.889-4.006-3.642-7.608-5.307-10.844l-.111-.163C15.317 1.461 14.468 0 12.001 0c-2.44 0-3.476 1.695-4.535 3.898l-.081.16c-1.669 3.236-3.421 6.843-5.303 10.847v.053l-.559 1.22c-.21.504-.317.768-.345.847C-.172 20.74 2.611 24 5.98 24c.027 0 .132 0 .265-.027h.372c1.75-.213 3.554-1.325 5.384-3.317 1.829 1.989 3.635 3.104 5.382 3.317h.372c.133.027.239.027.265.027 3.37.003 6.152-3.261 4.802-6.975z"/>'],
                    ['#E62E04','AliExpress','<path d="M12 2.4 4.3 17.1h3.4l1.3-2.7h6l1.3 2.7h3.4L12 2.4Zm0 4.9 1.9 3.9h-3.8L12 7.3Z"/><path d="M3.6 19.2a1 1 0 0 0-.7 1.7c1.6 1.6 4.9 2.7 9.1 2.7s7.5-1.1 9.1-2.7a1 1 0 0 0-1.4-1.4c-1.1 1.1-3.9 2.1-7.7 2.1s-6.6-1-7.7-2.1a1 1 0 0 0-.7-.3Z"/>'],
                    ['#4285F4','Google Ads','<path d="M3.9998 22.9291C1.7908 22.9291 0 21.1383 0 18.9293s1.7908-3.9998 3.9998-3.9998 3.9998 1.7908 3.9998 3.9998-1.7908 3.9998-3.9998 3.9998zm19.4643-6.0004L15.4632 3.072C14.3586 1.1587 11.9121.5028 9.9988 1.6074S7.4295 5.1585 8.5341 7.0718l8.0009 13.8567c1.1046 1.9133 3.5511 2.5679 5.4644 1.4646 1.9134-1.1046 2.568-3.5511 1.4647-5.4644zM7.5137 4.8438L1.5645 15.1484A4.5 4.5 0 0 1 4 14.4297c2.5597-.0075 4.6248 2.1585 4.4941 4.7148l3.2168-5.5723-3.6094-6.25c-.4499-.7793-.6322-1.6394-.5878-2.4784z"/>'],
                ];
            @endphp

            <div data-reveal class="service-wall mt-8 lg:mt-12">
                <ul>
                    @foreach ($services as $service)
                        <li class="service-cell" style="--svc: {{ $service[0] }}">
                            <a>
                                <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">{!! $service[2] !!}</svg>
                                <span>{{ $service[1] }}</span>
                            </a>
                        </li>
                    @endforeach
                    <li class="service-cell service-cell-all">
                        <button type="button">
                            <span class="service-all-label">Все сервисы
                            </span>
                            <span class="service-all-sub">и любой сайт, где принимают MasterCard и Visa</span>
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </section>

    {{-- ================= APP ================= --}}
    <section class="px-6 pb-28 lg:px-10 lg:pb-40">
        <div class="app-panel mx-auto max-w-[1400px] overflow-hidden rounded-[40px] bg-white">
            <div class="grid lg:grid-cols-[1fr_.75fr]">
                <div data-reveal class="p-10 sm:p-14 lg:p-20"><span class="eyebrow">Личный кабинет</span><h2 class="mt-5 max-w-2xl text-[clamp(2.8rem,5vw,5rem)] font-medium leading-[.94] tracking-[-.045em]">Удобный и простой личный кабинет</h2><p class="mt-7 max-w-xl text-lg leading-relaxed text-[#141413]/60">Выпускайте карты в пару кликов, отслеживайте актуальный баланс и историю своих платежей из одного места.</p><div class="mt-9 grid gap-4 sm:grid-cols-2">@foreach(['Быстрый выпуск карт','История операций','Пополнение баланса из России','Push-уведомления'] as $item)<div class="rounded-2xl bg-[#f3f0ee] p-5 text-sm font-semibold">{{ $item }}</div>@endforeach</div><div class="mt-8 flex flex-wrap gap-4"><a href="#" class="btn btn-hero-orange">Зарегистрироваться</a><a href="#" class="btn btn-hero-black">Авторизоваться</a></div></div>
                <div data-reveal class="app-visual" style="background-image:url('{{ asset('assets/images/iphone.png') }}');background-position:center;background-repeat:no-repeat;background-size: cover;"></div>
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
