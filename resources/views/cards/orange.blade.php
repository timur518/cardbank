@php
    $available = $product && ! $product->coming_soon;
    $ctaUrl = $available ? 'https://mne.mojno.cc/register' : 'https://t.me/mojno_support';
    $ctaLabel = $available ? 'Оформить Orange' : 'Уточнить доступность';
    $rub = fn ($value) => $value !== null ? number_format((float) $value, 0, ',', ' ').' ₽' : 'Уточняется';
    $usd = fn ($value) => $value !== null ? '$'.number_format((float) $value, 2, ',', ' ') : 'Уточняется';
    $percent = fn ($value) => $value !== null ? number_format((float) $value, 2, ',', ' ').'%' : 'Уточняется';
@endphp
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Orange — карта для путешествий и оплаты за границей | Можно</title>
    <meta name="description" content="Orange от Можно — виртуальная карта для путешествий, отелей, кафе и покупок за границей. Оформление онлайн, пополнение из России и управление в личном кабинете.">
    <link rel="canonical" href="https://mojno.cc/cards/orange">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="ru_RU">
    <meta property="og:title" content="Orange. Весь мир — в ваших планах.">
    <meta property="og:description" content="Карта для путешествий, оплаты телефоном и покупок за границей от Можно.">
    <meta property="og:url" content="https://mojno.cc/cards/orange">
    <meta property="og:image" content="{{ asset('assets/images/orangecardbg.png') }}">
    <link rel="icon" href="{{ asset('assets/images/favicon.svg') }}" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=sofia-sans:400,500,600,700,800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('assets/css/orange.css') }}">
    {!! $analyticsCodes['analytics_counter_id'] ?? '' !!}
    {!! $analyticsCodes['analytics_pixel_ids'] ?? '' !!}
</head>
<body class="orange-page font-sans antialiased">
<a class="orange-skip" href="#main">Перейти к содержимому</a>
<header class="orange-nav">
    <a href="/" aria-label="Можно — главная"><img src="{{ asset('assets/images/logo.svg') }}" width="130" height="40" alt="Можно"></a>
    <nav aria-label="Навигация по странице"><a href="#possibilities">Возможности</a><a href="#how">Оформление</a><a href="#terms">Условия</a><a href="#questions">Вопросы</a></nav>
    <a href="https://mne.mojno.cc/" class="orange-login">Личный кабинет <span aria-hidden="true">↗︎</span></a>
</header>
<main id="main">
    <section class="orange-hero orange-container">
        <div class="orange-hero-copy">
            <div class="orange-product-name">Orange <span>Карта для путешествий</span></div>
            <h1>Весь мир —<br>в ваших <em>планах.</em></h1>
            <p>Ужин с видом на море. Отель в новом городе. Покупки по пути домой. Берите Orange с собой — и платите за границей привычным способом.</p>
            <div class="orange-actions"><a class="orange-button" href="{{ $ctaUrl }}">{{ $ctaLabel }} <span aria-hidden="true">↗︎</span></a><a class="orange-text-link" href="#terms">Посмотреть условия <span aria-hidden="true">↓</span></a></div>
            <div class="orange-hero-facts"><div><strong>{{ $rub($product?->price_rub) }}</strong><span>за выпуск карты</span></div><div><strong>0 ₽</strong><span>обслуживание в месяц</span></div><div><strong>Онлайн</strong><span>без визита в офис</span></div></div>
        </div>
        <div class="orange-hero-visual">
            <img class="orange-hero-photo" src="{{ asset('assets/images/booking.png') }}" width="1491" height="1055" alt="Путешественники приезжают в отель" fetchpriority="high">
            <div class="orange-photo-caption">Новые места.<br>Знакомый способ платить.</div>
            <img class="orange-hero-card" src="{{ asset('assets/images/orangecard.png') }}" width="510" height="300" alt="Виртуальная карта Orange от Можно">
            <div class="orange-photo-stamp"><span aria-hidden="true">↗︎</span> Можно путешествовать</div>
        </div>
    </section>
    <div class="orange-container"><div class="orange-trust-line"><span>Оформление на ваше имя</span><span>Пополнение из России</span><span>Управление в личном кабинете</span><a href="https://t.me/mojno_support">Поддержка в Telegram ↗︎</a></div></div>

    <section id="possibilities" class="orange-container orange-section">
        <div class="orange-section-heading"><h2>Меньше забот об оплате.<br>Больше впечатлений.</h2><p>Одна виртуальная карта для больших планов и маленьких радостей в поездке.</p></div>
        <div class="orange-scenes">
            <article class="orange-scene orange-scene-large"><img src="{{ asset('assets/images/booking.png') }}" alt="Заселение в отель во время путешествия" loading="lazy" width="1491" height="1055"><div><span class="orange-scene-number">01 / В ПУТЕШЕСТВИИ</span><h3>Отель выбран.<br>Остаётся собрать чемодан.</h3><p>Для оплаты проживания и бронирований там, где принимают вашу карту.</p></div></article>
            <article class="orange-scene"><img src="{{ asset('assets/images/cafe.png') }}" alt="Отдых в кафе" loading="lazy"><div><span class="orange-scene-number">02 / КАЖДЫЙ ДЕНЬ</span><h3>Кофе, ужин,<br>ещё один красивый день.</h3><p>Кафе, рестораны и повседневные покупки за границей.</p></div></article>
            <article class="orange-scene"><img src="{{ asset('assets/images/ecomm.png') }}" alt="Покупки в зарубежных магазинах" loading="lazy"><div><span class="orange-scene-number">03 / ДЛЯ СЕБЯ</span><h3>Покупки, которые<br>будут напоминать о поездке.</h3><p>Оплачивайте в магазинах и онлайн по реквизитам карты.</p></div></article>
        </div>
        <p class="orange-disclaimer">Доступность оплаты зависит от страны, магазина и условий карты. Для бронирований с депозитом или требованием физической карты заранее уточните правила у отеля.</p>
    </section>

    <section class="orange-container orange-section">
        <div class="orange-wallet-panel">
            <div class="orange-wallet-art" aria-hidden="true"><div class="orange-phone"><div class="orange-phone-top"></div><span>Кошелёк</span><img src="{{ asset('assets/images/orangecard.png') }}" width="510" height="300" alt=""><div class="orange-contactless">)))</div><p>Поднесите к терминалу</p></div><div class="orange-wallet-circle"></div></div>
            <div class="orange-wallet-copy"><h2>Телефон с собой.<br>Значит, карта тоже.</h2><p>Orange создана для оплаты телефоном или часами за границей. Добавьте карту в поддерживаемый кошелёк — и пользуйтесь ей у бесконтактного терминала.</p>
                <div class="orange-wallet-badges">
                    @if ($product?->apple_pay_enabled)<span>Apple Pay</span>@endif
                    @if ($product?->google_pay_enabled)<span>Google Pay</span>@endif
                    @if (! $product)<span>Условия подключения — в тарифах</span>@endif
                </div>
                <p class="orange-small">{{ $product?->wallet_activation ?: 'Инструкция по подключению доступна в личном кабинете. Возможность оплаты зависит от устройства, страны и терминала.' }}</p>
                <a class="orange-text-link" href="#terms">Все возможности Orange <span aria-hidden="true">↗︎</span></a>
            </div>
        </div>
    </section>

    <section id="how" class="orange-container orange-section">
        <div class="orange-section-heading"><h2>Подготовьте карту<br>до вылета.</h2><p>Всё можно сделать онлайн. Реквизиты и управление картой останутся под рукой в личном кабинете.</p></div>
        <ol class="orange-steps"><li><span>01</span><h3>Создайте аккаунт</h3><p>Зарегистрируйтесь в Можно и заполните данные владельца карты.</p></li><li><span>02</span><h3>Выберите Orange</h3><p>Ознакомьтесь с условиями и оформите карту в личном кабинете.@if ($product?->provider_kyc_required) Для выпуска требуется проверка личности.@endif</p></li><li><span>03</span><h3>Пополните баланс</h3><p>Выберите доступный способ оплаты из России. Курс и итоговая сумма видны перед оплатой.</p></li><li><span>04</span><h3>Можно в поездку</h3><p>Используйте реквизиты для онлайн-оплаты или подключите поддерживаемый кошелёк.</p></li></ol>
    </section>

    <section class="orange-container orange-section">
        <div class="orange-control-panel"><div><h2>Пополнить из России.<br>Потратить в путешествии.</h2><p>Не нужно искать обменник, чтобы пополнить карту. Выберите доступный способ в личном кабинете: СБП или карту российского банка. Рубли конвертируются в валюту карты по курсу, который вы увидите перед оплатой.</p><a class="orange-button orange-button-light" href="{{ $ctaUrl }}">{{ $ctaLabel }} <span aria-hidden="true">↗︎</span></a></div><div class="orange-control-preview"><div class="orange-preview-header"><img src="{{ asset('assets/images/logo_min.svg') }}" width="32" height="32" alt=""><span>Всё в одном кабинете</span><span aria-hidden="true">↗︎</span></div><div class="orange-preview-card"><img src="{{ asset('assets/images/orangecard.png') }}" width="510" height="300" alt="Карта Orange в личном кабинете" loading="lazy"></div><ul><li><span>Баланс карты</span><span aria-hidden="true">✓</span></li><li><span>История операций</span><span aria-hidden="true">✓</span></li><li><span>Реквизиты и управление</span><span aria-hidden="true">✓</span></li></ul><p>Иллюстрация возможностей личного кабинета</p></div></div>
    </section>

    <section id="terms" class="orange-container orange-section">
        <div class="orange-section-heading"><h2>Условия, которые<br>можно понять заранее.</h2><p>Стоимость выпуска, комиссии и лимиты — до оформления карты.</p></div>
        <div class="orange-terms-layout"><div class="orange-price-card"><img src="{{ asset('assets/images/orangecard.png') }}" width="510" height="300" alt="Orange" loading="lazy"><h3>Orange</h3><p>Для путешествий и покупок за границей</p><strong>{{ $rub($product?->price_rub) }}</strong><span>выпуск карты · обслуживание 0 ₽ в месяц</span><a class="orange-button" href="{{ $ctaUrl }}">{{ $ctaLabel }} <span aria-hidden="true">↗︎</span></a></div>
            <div class="orange-terms">
            @if ($product)
                <dl>
                    <div><dt>Валюта карты</dt><dd>{{ $product->currency }}</dd></div>
                    <div><dt>Платёжная система</dt><dd>{{ $product->network?->getLabel() ?? 'Уточняется' }}</dd></div>
                    <div><dt>Страна выпуска</dt><dd>{{ $product->card_country?->getLabel() ?? 'Уточняется' }}</dd></div>
                    <div><dt>Пополнение карты</dt><dd>{{ $percent($product->provider_topup_fee_percent) }}</dd></div>
                    <div><dt>Успешная оплата</dt><dd>{{ $usd($product->successful_payment_fee_usd) }}</dd></div>
                    <div><dt>Отказ в оплате</dt><dd>{{ $usd($product->decline_fee_usd) }}</dd></div>
                    <div><dt>Оплата в другой валюте</dt><dd>{{ $product->non_usd_payment_fee ?: 'Уточняется' }}</dd></div>
                    <div><dt>Рисковая операция</dt><dd>{{ $usd($product->risk_operation_fee_usd) }}</dd></div>
                    <div><dt>Пополнение: минимум / максимум</dt><dd>{{ $usd($product->topup_min_amount) }} / {{ $usd($product->topup_max_amount) }}</dd></div>
                    <div><dt>Выпуск: минимум / максимум</dt><dd>{{ $usd($product->issue_min_amount) }} / {{ $usd($product->issue_max_amount) }}</dd></div>
                    <div><dt>Apple Pay / Google Pay</dt><dd>{{ $product->apple_pay_enabled ? 'Да' : 'Нет' }} / {{ $product->google_pay_enabled ? 'Да' : 'Нет' }}</dd></div>
                    <div><dt>Подтверждение оплаты 3DS</dt><dd>{{ $product->three_ds_supported ? 'Поддерживается' : 'Не поддерживается' }}</dd></div>
                </dl>
            @else
                <p>Актуальные условия Orange сейчас уточняются. Напишите в поддержку — поможем разобраться с доступностью карты и оформлением.</p>
            @endif
                <a class="orange-text-link" href="{{ route('tariffs') }}">Полные тарифы и условия <span aria-hidden="true">↗︎</span></a>
            </div>
        </div>
        @if ($product?->restricted_merchants)<details class="orange-terms-details"><summary>Ограничения по магазинам и сервисам</summary><div class="legal-doc-content">{!! $product->restricted_merchants !!}</div></details>@endif
        @if ($product?->full_terms)<details class="orange-terms-details"><summary>Подробные условия использования Orange</summary><div class="legal-doc-content">{!! $product->full_terms !!}</div></details>@endif
    </section>

    <section id="questions" class="orange-container orange-section orange-faq">
        <div><h2>Хорошие вопросы<br>перед поездкой.</h2><p>Не нашли ответ? Поддержка поможет разобраться с условиями Orange.</p><a class="orange-text-link" href="https://t.me/mojno_support">Написать в Telegram <span aria-hidden="true">↗︎</span></a></div>
        <div>
            @foreach ([
                ['Это физическая или виртуальная карта?', 'Orange — виртуальная карта. Её реквизиты доступны в личном кабинете. Для оплаты в магазине используйте поддерживаемый мобильный кошелёк и бесконтактный терминал.'],
                ['На чьё имя выпускается карта?', 'На ваше имя, указанное в анкете при регистрации. Заполняйте данные владельца корректно.'],
                ['Как пополнить карту из России?', 'Доступные способы пополнения показаны в личном кабинете. Можно использовать СБП или карту российского банка, если этот способ доступен вашему аккаунту. Курс, комиссии и итоговая сумма показываются перед оплатой.'],
                ['Можно ли оплачивать отели?', 'Карта подходит для оплаты там, где принимаются её платёжная система и формат. Отель может требовать физическую карту или депозит при заселении — заранее уточните эти условия напрямую у отеля.'],
                ['Будет ли карта работать в России?', 'Нет. Используйте Orange для оплаты за границей. Доступность в конкретной стране и ограничения проверяйте в условиях карты.'],
                ['Когда можно начать пользоваться?', 'После успешного выпуска и пополнения баланса. Для бесконтактной оплаты предварительно подключите карту к поддерживаемому кошельку по инструкции в личном кабинете.'],
            ] as [$question, $answer])
                <details><summary>{{ $question }}<span aria-hidden="true">+</span></summary><p>{{ $answer }}</p></details>
            @endforeach
        </div>
    </section>

    <section class="orange-container orange-section orange-final"><div><h2>Выберите, куда ехать.<br>С оплатой — <em>Можно.</em></h2><p>Оформите Orange онлайн и подготовьте карту к следующему путешествию.</p><a class="orange-button" href="{{ $ctaUrl }}">{{ $ctaLabel }} <span aria-hidden="true">↗︎</span></a></div><img src="{{ asset('assets/images/orangecard.png') }}" width="510" height="300" alt="Orange — карта для путешествий" loading="lazy"></section>
</main>
@include('partials.site-footer')
<div class="orange-mobile-cta"><div><strong>Orange</strong><span>{{ $rub($product?->price_rub) }} за выпуск</span></div><a class="orange-button" href="{{ $ctaUrl }}">{{ $ctaLabel }} ↗︎</a></div>
</body>
</html>
