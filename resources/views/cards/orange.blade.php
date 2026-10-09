@php
    $available = $product && ! $product->coming_soon;
    $ctaUrl = $available ? 'https://mne.mojno.cc/register' : 'https://t.me/mojno_support';
    $ctaLabel = $available ? 'Оформить Orange' : 'Уточнить доступность';
    $phonePayments = implode(' и ', array_filter([
        $product?->apple_pay_enabled ? 'Apple Pay' : null,
        $product?->google_pay_enabled ? 'Google Pay' : null,
    ]));
    $phonePaymentInstructions = $phonePayments
        ? 'Добавьте Orange в '.$phonePayments.' по инструкции в личном кабинете. Для оплаты в магазине нужны телефон или часы с бесконтактной оплатой и подходящий терминал.'
        : 'Оплата через Apple Pay и Google Pay для этой карты сейчас недоступна. Для покупок онлайн используйте реквизиты из личного кабинета.';
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
    <meta name="description" content="Виртуальная карта Orange для оплаты билетов, отелей и покупок в путешествиях. Оформление онлайн, пополнение из России и условия карты на сайте.">
    <link rel="canonical" href="https://mojno.cc/cards/orange">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="ru_RU">
    <meta property="og:title" content="Orange — карта для ваших путешествий">
    <meta property="og:description" content="Карта для онлайн-бронирований и покупок в путешествиях от Можно.">
    <meta property="og:url" content="https://mojno.cc/cards/orange">
    <meta property="og:image" content="{{ asset('assets/images/orangecardbg.png') }}">
    <link rel="icon" href="{{ asset('assets/images/favicon.svg') }}" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=sofia-sans:400,500,600,700,800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('assets/css/orange.css') }}?v={{ filemtime(public_path('assets/css/orange.css')) }}">
    {!! $analyticsCodes['analytics_counter_id'] ?? '' !!}
    {!! $analyticsCodes['analytics_pixel_ids'] ?? '' !!}
</head>
<body class="orange-page font-sans antialiased">
<a class="orange-skip" href="#main">Перейти к содержимому</a>
@include('partials.site-header')
<main id="main">
    <section class="orange-hero orange-container">
        <div class="orange-hero-copy">
            <div class="orange-product-name">Orange <span>Карта для путешествий</span></div>
            <h1>Карта для ваших<br><em>путешествий</em></h1>
            <p>Оплачивайте билеты и отели онлайн, кафе и покупки — в поездке. Оформите виртуальную карту Orange онлайн и пополняйте её из России.</p>
            <div class="orange-actions"><a class="orange-button" href="{{ $ctaUrl }}">{{ $ctaLabel }} <span aria-hidden="true">↗︎</span></a><a class="orange-text-link" href="#terms">Посмотреть условия <span aria-hidden="true">↓</span></a></div>
            <div class="orange-hero-facts"><div><strong>{{ $rub($product?->price_rub) }}</strong><span>за выпуск карты</span></div><div><strong>{{ $percent($product?->provider_topup_fee_percent) }}</strong><span>комиссия пополнения</span></div><div><strong>Онлайн</strong><span>без визита в офис</span></div></div>
        </div>
        <div class="orange-hero-visual">
            <img class="orange-hero-photo" src="{{ asset('assets/images/booking.png') }}" width="1491" height="1055" alt="Путешественники приезжают в отель" fetchpriority="high">
            <div class="orange-photo-caption">Ваша карта<br>для покупок за границей</div>
            <img class="orange-hero-card" src="{{ asset('assets/images/orangecard.png') }}" width="510" height="300" alt="Виртуальная карта Orange от Можно">
            <div class="orange-photo-stamp"><span aria-hidden="true">↗︎</span> Можно путешествовать</div>
        </div>
    </section>
    <div class="orange-container"><div class="orange-trust-line"><span>Оформление на ваше имя</span><span>Пополнение из России</span><span>Управление в личном кабинете</span><a href="https://t.me/mojno_support">Поддержка в Telegram ↗︎</a></div></div>

    <section id="possibilities" class="orange-container orange-section">
        <div class="orange-section-heading"><h2>Оплачивайте то,<br>что нужно в поездке</h2><p>Проживание, обеды и покупки — пользуйтесь Orange во время путешествия.</p></div>
        <div class="orange-scenes">
            <article class="orange-scene orange-scene-large"><img src="{{ asset('assets/images/booking.png') }}" alt="Заселение в отель во время путешествия" loading="lazy" width="1491" height="1055"><div><h3>Отели<br>и апартаменты</h3><p>Бронируйте жильё онлайн и оплачивайте проживание картой Orange.</p></div></article>
            <article class="orange-scene"><img src="{{ asset('assets/images/cafe.png') }}" alt="Отдых в кафе" loading="lazy"><div><h3>Кафе<br>и рестораны</h3><p>Платите за завтрак, кофе и ужин во время поездки.</p></div></article>
            <article class="orange-scene"><img src="{{ asset('assets/images/ecomm.png') }}" alt="Покупки в зарубежных магазинах" loading="lazy"><div><h3>Магазины<br>и сувениры</h3><p>Покупайте подарки и вещи для себя в магазинах и аэропортах.</p></div></article>
        </div>
        <p class="orange-disclaimer">Приём карты зависит от страны, магазина и условий Orange. Для оплаты в магазинах нужен Apple Pay или Google Pay, подключённый к карте. Перед бронированием уточните у отеля, нужна ли физическая карта или залог при заселении.</p>
    </section>

    <section class="orange-container orange-section orange-booking">
        <div class="orange-section-heading"><h2>Планируйте поездку<br>и платите онлайн</h2><p>Покупайте билеты, бронируйте жильё и планируйте отдых с картой Orange ещё до отъезда.</p></div>
        <div class="orange-booking-grid">
            @foreach ([
                ['air', 'Билеты туда и обратно', 'Покупайте билеты на самолёт и поезд на сайтах перевозчиков и в сервисах бронирования.'],
                ['hotel', 'Отели и апартаменты', 'Оплачивайте жильё до поездки. Заранее уточните у отеля условия заселения и размер залога.'],
                ['route', 'Транспорт, экскурсии и связь', 'Закажите трансфер, купите билеты в музеи и подключите мобильный интернет за границей с eSIM.'],
            ] as [$icon, $title, $text])
                <article>
                    <div class="orange-booking-icon" aria-hidden="true">
                        <svg viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            @if ($icon === 'air')<path d="m27 5-8 11 7 7-3 2-9-5-6 6-3-1 5-9-5-9 2-3 7 7 11-8Z"/>
                            @elseif ($icon === 'hotel')<path d="M5 28V8h22v20M11 8V4h10v4M12 28v-7h8v7M9 13h3m8 0h3M9 17h3m8 0h3M3 28h26"/>
                            @else<path d="M8 25c0-6 16-2 16-9 0-4-8-3-8-6M12 9a4 4 0 1 1 8 0c0 3-4 6-4 6s-4-3-4-6Z"/><circle cx="8" cy="26" r="2"/>
                            @endif
                        </svg>
                    </div>
                    <h3>{{ $title }}</h3><p>{{ $text }}</p>
                </article>
            @endforeach
        </div>
        <div class="orange-booking-help"><p><strong>Хотите уточнить, подойдёт ли Orange для вашей покупки?</strong> Пришлите поддержке название сайта или отеля, страну и сумму покупки. Поможем проверить условия карты до оформления.</p><a class="orange-text-link" href="https://t.me/mojno_support">Уточнить в поддержке <span aria-hidden="true">↗︎</span></a></div>
        <p class="orange-disclaimer">Приём карты зависит от сайта, страны и ограничений Orange. Проверьте условия перед бронированием.</p>
    </section>

    <section class="orange-container orange-section">
        <div class="orange-wallet-panel">
            <div class="orange-wallet-art" aria-hidden="true"><div class="orange-phone"><div class="orange-phone-top"></div><span>{{ ($product?->apple_pay_enabled || $product?->google_pay_enabled) ? 'Оплата телефоном' : 'Orange' }}</span><img src="{{ asset('assets/images/orangecard.png') }}" width="510" height="300" alt=""><div class="orange-contactless">)))</div><p>{{ ($product?->apple_pay_enabled || $product?->google_pay_enabled) ? 'Поднесите к терминалу' : 'Ваша карта в личном кабинете' }}</p></div><div class="orange-wallet-circle"></div></div>
            <div class="orange-wallet-copy">
                @if ($product?->apple_pay_enabled || $product?->google_pay_enabled)
                    <h2>Платите телефоном<br>за границей</h2><p>Добавьте Orange в {{ $phonePayments }}. В магазинах, кафе и ресторанах поднесите телефон или часы к терминалу для оплаты.</p>
                @else
                    <h2>Реквизиты карты<br>в личном кабинете</h2><p>Откройте карту в личном кабинете, чтобы посмотреть её номер, срок действия и данные для оплаты покупок онлайн.</p>
                @endif
                <div class="orange-wallet-badges">
                    @if ($product?->apple_pay_enabled)<span>Apple Pay</span>@endif
                    @if ($product?->google_pay_enabled)<span>Google Pay</span>@endif
                    @if (! $product)<span>Возможности карты уточняются</span>@endif
                </div>
                @if ($phonePayments)
                    <p class="orange-small">{{ $product?->wallet_activation ?: 'Инструкция по подключению '.$phonePayments.' есть в личном кабинете. Для оплаты нужны совместимые телефон или часы и терминал с бесконтактной оплатой.' }}</p>
                @endif
                <a class="orange-text-link" href="#terms">Посмотреть условия карты <span aria-hidden="true">↗︎</span></a>
            </div>
        </div>
    </section>

    <section id="how" class="orange-container orange-section">
        <div class="orange-section-heading"><h2>Оформите карту<br>до поездки</h2><p>Зарегистрируйтесь, выпустите Orange и пополните баланс. Все действия доступны в личном кабинете.</p></div>
        <ol class="orange-steps"><li><span>01</span><h3>Создайте кабинет</h3><p>Создайте личный кабинет в Можно и укажите данные владельца карты.</p></li><li><span>02</span><h3>Выберите Orange</h3><p>Проверьте стоимость и условия, затем оформите карту.@if ($product?->provider_kyc_required) Для выпуска требуется проверка личности.@endif</p></li><li><span>03</span><h3>Пополните карту</h3><p>Выберите способ пополнения в личном кабинете. Проверьте курс, комиссию и сумму, которая поступит на карту.</p></li><li><span>04</span><h3>Оплачивайте покупки</h3><p>Вводите реквизиты карты при оплате онлайн.@if ($phonePayments) Для оплаты телефоном добавьте Orange в {{ $phonePayments }}.@endif</p></li></ol>
    </section>

    <section class="orange-container orange-section">
        <div class="orange-control-panel"><div><h2>Пополняйте из России<br>и следите за расходами</h2><p>Пополняйте Orange в личном кабинете. При пополнении рубли переводятся в валюту карты. Курс, комиссия и сумма зачисления видны перед оплатой. Здесь же можно проверить баланс и историю покупок.</p><a class="orange-button orange-button-light" href="{{ $ctaUrl }}">{{ $ctaLabel }} <span aria-hidden="true">↗︎</span></a></div><div class="orange-control-preview"><div class="orange-preview-header"><img src="{{ asset('assets/images/logo_min.svg') }}" width="32" height="32" alt=""><span>Всё в одном кабинете</span><span aria-hidden="true">↗︎</span></div><div class="orange-preview-card"><img src="{{ asset('assets/images/orangecard.png') }}" width="510" height="300" alt="Карта Orange в личном кабинете" loading="lazy"></div><ul><li><span>Баланс карты</span><span aria-hidden="true">✓</span></li><li><span>История операций</span><span aria-hidden="true">✓</span></li><li><span>Реквизиты и управление</span><span aria-hidden="true">✓</span></li></ul><p>Пример отображения карты в личном кабинете</p></div></div>
    </section>

    <section id="terms" class="orange-container orange-section">
        <div class="orange-section-heading"><h2>Стоимость и условия<br>карты Orange</h2><p>Посмотрите стоимость выпуска, комиссии и лимиты перед оформлением.</p></div>
        <div class="orange-terms-layout"><div class="orange-price-card"><img src="{{ asset('assets/images/orangecard.png') }}" width="510" height="300" alt="Orange" loading="lazy"><h3>Orange</h3><p>Для путешествий и покупок за границей</p><strong>{{ $rub($product?->price_rub) }}</strong><span>выпуск карты@if ($product) · {{ $product->currency }}@endif</span><a class="orange-button" href="{{ $ctaUrl }}">{{ $ctaLabel }} <span aria-hidden="true">↗︎</span></a></div>
            <div class="orange-terms">
            @if ($product)
                <dl>
                    <div><dt>Валюта карты</dt><dd>{{ $product->currency }}</dd></div>
                    <div><dt>Платёжная система</dt><dd>{{ $product->network?->getLabel() ?? 'Уточняется' }}</dd></div>
                    <div><dt>Страна выпуска</dt><dd>{{ $product->card_country?->getLabel() ?? 'Уточняется' }}</dd></div>
                    <div><dt>Пополнение карты</dt><dd>{{ $percent($product->provider_topup_fee_percent) }}</dd></div>
                    <div><dt>Комиссия за покупку</dt><dd>{{ $usd($product->successful_payment_fee_usd) }}</dd></div>
                    <div><dt>Комиссия при отказе в оплате</dt><dd>{{ $usd($product->decline_fee_usd) }}</dd></div>
                    <div><dt>Оплата в другой валюте</dt><dd>{{ $product->non_usd_payment_fee ?: 'Уточняется' }}</dd></div>
                    <div><dt>Рисковая операция</dt><dd>{{ $usd($product->risk_operation_fee_usd) }}</dd></div>
                    <div><dt>Пополнение: минимум / максимум</dt><dd>{{ $usd($product->topup_min_amount) }} / {{ $usd($product->topup_max_amount) }}</dd></div>
                    <div><dt>Выпуск: минимум / максимум</dt><dd>{{ $usd($product->issue_min_amount) }} / {{ $usd($product->issue_max_amount) }}</dd></div>
                    <div><dt>Apple Pay / Google Pay</dt><dd>{{ $product->apple_pay_enabled ? 'Да' : 'Нет' }} / {{ $product->google_pay_enabled ? 'Да' : 'Нет' }}</dd></div>
                    <div><dt>Подтверждение оплаты 3DS</dt><dd>{{ $product->three_ds_supported ? 'Поддерживается' : 'Не поддерживается' }}</dd></div>
                </dl>
            @else
                <p>Условия Orange сейчас уточняются. Напишите в поддержку, чтобы узнать о выпуске карты.</p>
            @endif
                <a class="orange-text-link" href="{{ route('tariffs') }}">Полные тарифы и условия <span aria-hidden="true">↗︎</span></a>
            </div>
        </div>
        @if ($product?->restricted_merchants)<details class="orange-terms-details"><summary>Ограничения по магазинам и сервисам</summary><div class="legal-doc-content">{!! $product->restricted_merchants !!}</div></details>@endif
        @if ($product?->full_terms)<details class="orange-terms-details"><summary>Подробные условия использования Orange</summary><div class="legal-doc-content">{!! $product->full_terms !!}</div></details>@endif
    </section>

    <section class="orange-container orange-section orange-ready">
        <div><h2>Проверьте карту<br>перед поездкой</h2><p>Проверьте баланс, способ оплаты и условия бронирований заранее.</p><a class="orange-text-link" href="https://t.me/mojno_support">Задать вопрос поддержке <span aria-hidden="true">↗︎</span></a></div>
        <ol>
            <li><strong>Проверьте способ оплаты</strong><p>Для оплаты онлайн подготовьте реквизиты карты.@if ($phonePayments) Для покупок в магазинах подключите Orange к {{ $phonePayments }} и проверьте настройку оплаты на телефоне.@else Оплата через Apple Pay и Google Pay для этой карты сейчас недоступна.@endif</p></li>
            <li><strong>Проверьте баланс и комиссии</strong><p>Пополните карту на нужную сумму. Если валюта покупки отличается от валюты карты, проверьте комиссию за такую оплату в тарифах.</p></li>
            <li><strong>Уточните правила отеля</strong><p>Уточните, нужен ли залог или физическая карта при заселении. Условия оплаты на сайте и в самом отеле могут отличаться.</p></li>
            <li><strong>Сохраните контакт поддержки</strong><p>Сохраните @mojno_support в Telegram. По вопросам оплаты напишите поддержке и укажите дату и сумму операции из личного кабинета.</p></li>
        </ol>
    </section>

    <section id="questions" class="orange-container orange-section orange-faq">
        <div><h2>Ответы на вопросы<br>о карте Orange</h2><p>Поддержка ответит на вопросы об оформлении, пополнении и оплате картой.</p><a class="orange-text-link" href="https://t.me/mojno_support">Написать в Telegram <span aria-hidden="true">↗︎</span></a></div>
        <div>
            @foreach ([
                ['Это физическая или виртуальная карта?', 'Orange — виртуальная карта. Её номер, срок действия и данные для оплаты доступны в личном кабинете. '.$phonePaymentInstructions],
                ['На чьё имя выпускается карта?', 'На имя владельца, указанное при регистрации. Проверьте правильность данных перед оформлением карты.'],
                ['Как пополнить карту из России?', 'Откройте карту в личном кабинете и выберите способ пополнения. Перед оплатой вы увидите курс, комиссию и сумму зачисления.'],
                ['Подойдёт ли карта для билетов, отелей и eSIM?', 'Orange можно использовать для таких покупок на сайтах, которые принимают эту карту. Перед оплатой проверьте ограничения в условиях. Если нужна помощь, пришлите поддержке название сайта, страну и сумму покупки.'],
                ['Можно ли оплачивать отели?', 'Оплачивайте бронирование картой на сайтах, которые принимают Orange. Перед покупкой уточните у отеля, нужен ли залог или физическая карта при заселении. Orange выпускается в виртуальном формате.'],
                ['Что делать, если оплата не прошла?', 'Проверьте баланс и статус операции в личном кабинете. За отказ в оплате может взиматься комиссия по тарифу карты. Перед повторной попыткой напишите поддержке название магазина или сайта, дату и сумму покупки. Полный номер карты, защитный код CVV и коды подтверждения сохраняйте в тайне.'],
                ['Будет ли карта работать в России?', 'Orange предназначена для покупок за границей и не работает в России. Перед поездкой проверьте ограничения по странам в условиях карты.'],
                ['Когда можно начать пользоваться?', 'После выпуска карты и зачисления денег на баланс можно оплачивать покупки онлайн. '.$phonePaymentInstructions],
            ] as [$question, $answer])
                <details><summary>{{ $question }}<span aria-hidden="true">+</span></summary><p>{{ $answer }}</p></details>
            @endforeach
        </div>
    </section>

    <section class="orange-container orange-section orange-final"><div><h2>Оформите Orange<br>для следующей <em>поездки</em></h2><p>Выпустите карту онлайн, пополните баланс и начните с билетов и бронирования отеля.</p><a class="orange-button" href="{{ $ctaUrl }}">{{ $ctaLabel }} <span aria-hidden="true">↗︎</span></a></div><img src="{{ asset('assets/images/orangecard.png') }}" width="510" height="300" alt="Orange — карта для путешествий" loading="lazy"></section>
</main>
@include('partials.site-footer')
<div class="orange-mobile-cta"><div><strong>Orange</strong><span>{{ $rub($product?->price_rub) }} за выпуск</span></div><a class="orange-button" href="{{ $ctaUrl }}">{{ $ctaLabel }} ↗︎</a></div>
</body>
</html>
