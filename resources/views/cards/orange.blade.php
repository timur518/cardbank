@php
    $available = $product && ! $product->coming_soon;
    $ctaUrl = $available ? 'https://mne.mojno.cc/register' : 'https://t.me/mojno_support';
    $ctaLabel = $available ? 'Оформить Orange' : 'Уточнить доступность';
    $paymentNames = array_values(array_filter([
        $product?->apple_pay_enabled ? 'Apple Pay' : null,
        $product?->google_pay_enabled ? 'Google Pay' : null,
        $product?->samsung_pay_enabled ? 'Samsung Pay' : null,
    ]));
    $phonePayments = count($paymentNames) > 1
        ? implode(', ', array_slice($paymentNames, 0, -1)).' и '.end($paymentNames)
        : implode('', $paymentNames);
    $phonePaymentInstructions = $phonePayments
        ? 'Добавьте вашу карту в '.$phonePayments.' по инструкции в личном кабинете. Для оплаты в магазине нужны телефон или часы с бесконтактной оплатой и подходящий терминал.'
        : 'Оплата через Apple Pay, Google Pay и Samsung Pay для этой карты сейчас недоступна. Для покупок онлайн используйте реквизиты из личного кабинета.';
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
    <meta name="description" content="Виртуальная карта Orange для оплаты билетов, отелей и покупок в путешествиях. Оформление онлайн, пополнение с карт российских банков и условия карты на сайте.">
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
            <p>Оплачивайте билеты и отели онлайн, кафе и покупки — в поездке. Оформите виртуальную карту Orange онлайн и пополняйте её с любой карты российского банка</p>
            <div class="orange-actions"><a class="orange-button" href="{{ $ctaUrl }}">{{ $ctaLabel }} <span aria-hidden="true">↗︎</span></a><a class="orange-text-link" href="#terms">Посмотреть условия <span aria-hidden="true">↓</span></a></div>
            <div class="orange-hero-facts"><div><strong>{{ $rub($product?->price_rub) }}</strong><span>за выпуск карты</span></div><div><strong>0 ₽</strong><span>обслуживание в месяц</span></div><div><strong class="orange-sbp"><img src="{{ asset('assets/images/orange/payments/sbp.svg') }}" width="30" height="30" alt=""><span>СБП</span></strong><span>пополнение рублями</span></div></div>
        </div>
        <div class="orange-hero-visual">
            <img class="orange-hero-photo" src="{{ asset('assets/images/booking.png') }}" width="1491" height="1055" alt="Путешественники приезжают в отель" fetchpriority="high">
            <div class="orange-photo-caption">Ваша карта<br>для покупок за границей</div>
            <img class="orange-hero-card" src="{{ asset('assets/images/orangecard.png') }}" width="510" height="300" alt="Виртуальная карта Orange от Можно">
            <div class="orange-photo-stamp"><span aria-hidden="true">↗︎</span> Можно путешествовать</div>
        </div>
    </section>

    <section id="possibilities" class="orange-container orange-section">
        <div class="orange-section-heading"><h2>Оплачивайте то,<br>что нужно в поездке</h2><p>Проживание, питание и покупки — пользуйтесь картой Orange во время путешествия</p></div>
        <div class="orange-scenes">
            <article class="orange-scene orange-scene-large"><img src="{{ asset('assets/images/booking.png') }}" alt="Заселение в отель во время путешествия" loading="lazy" width="1491" height="1055"><div><h3>Отели<br>и апартаменты</h3><p>Бронируйте жильё онлайн и оплачивайте проживание картой Orange</p></div></article>
            <article class="orange-scene"><img src="{{ asset('assets/images/cafe.png') }}" alt="Отдых в кафе" loading="lazy"><div><h3>Кафе<br>и рестораны</h3><p>Платите за завтрак, кофе и ужин во время поездки</p></div></article>
            <article class="orange-scene"><img src="{{ asset('assets/images/ecomm.png') }}" alt="Покупки в зарубежных магазинах" loading="lazy"><div><h3>Магазины<br>и сувениры</h3><p>Покупайте подарки и вещи для себя в магазинах и аэропортах</p></div></article>
        </div>
    </section>

    <section class="orange-container orange-section orange-booking">
        <div class="orange-section-heading"><h2>Планируйте поездку<br>и платите онлайн</h2><p>Покупайте билеты, бронируйте жильё и планируйте отдых с картой Orange ещё до отъезда</p></div>
        <div class="orange-booking-grid orange-services-grid">
            @foreach ([
                ['Отели и апартаменты', 'Выбирайте жилье и оплачивайте перед поездкой прямо из России', [['booking.svg', 'Booking.com'], ['airbnb.svg', 'Airbnb'], ['agoda.png', 'Agoda']]],
                ['Авиа и ЖД билеты', 'Покупайте билеты на всех популярных сервисах заранее или прямо в поездке', [['emirates.png', 'Emirates'], ['turkish-airlines.png', 'Turkish Airlines'], ['trainline.png', 'Trainline']]],
                ['eSIM для путешествий', 'Покупайте eSIM для страны поездки и подключайте мобильный интернет сразу после прилёта', [['bnesim.png', 'BNESIM'], ['alosim.png', 'aloSIM'], ['esim-plus.png', 'eSIM Plus']]],
                ['Экскурсии и развлечения', 'Выберите экскурсии, музеи и достопримечательности. Оплатите билеты заранее и сохраните подтверждение', [['getyourguide.png', 'GetYourGuide'], ['klook.png', 'Klook'], ['trip-com.png', 'Trip.com']]],
            ] as [$title, $text, $services])
                <article>
                    <h3>{{ $title }}</h3><p>{{ $text }}</p>
                    <ul class="orange-service-logos">
                        @foreach ($services as [$logo, $service])
                            <li><img src="{{ asset('assets/images/orange/services/'.$logo) }}" alt="" aria-hidden="true" width="32" height="32" loading="lazy"><span>{{ $service }}</span></li>
                        @endforeach
                    </ul>
                </article>
            @endforeach
        </div>
        <details class="orange-online-guide">
            <summary>Как оплатить бронирование на сайте <span aria-hidden="true">+</span></summary>
            <ol>
                <li><strong>Выберите оплату картой</strong><p>Проверьте сумму и валюту покупки, правила отмены и дату списания.</p></li>
                <li><strong>Введите данные Orange</strong><p>Номер карты, срок действия и CVV доступны в личном кабинете. Если сайт просит платёжный адрес (billing address), скопируйте его из раздела «Платёжный адрес» вашей карты.</p></li>
                <li><strong>Подтвердите оплату</strong><p>@if ($product?->three_ds_supported) Если потребуется код 3DS, откройте раздел «3DS коды» на странице карты в личном кабинете.@else Следуйте указаниям сайта. Поддержку подтверждения 3DS можно проверить в условиях Orange.@endif Сохраните билет или подтверждение бронирования и проверьте операцию в истории карты.</p></li>
            </ol>
        </details>
        <div class="orange-booking-help"><p><strong>Хотите уточнить, подойдёт ли Orange для вашей покупки?</strong> Пришлите поддержке название сайта или отеля, страну и сумму покупки. Поможем проверить условия карты до оформления.</p><a class="orange-text-link" href="https://t.me/mojno_support">Уточнить в поддержке <span aria-hidden="true">↗︎</span></a></div>
    </section>

    <section class="orange-container orange-section">
        <div class="orange-wallet-panel">
            <div class="orange-wallet-art" aria-hidden="true"><div class="orange-phone"><div class="orange-phone-top"></div><span>{{ $phonePayments ? 'Оплата телефоном' : 'Orange' }}</span><img src="{{ asset('assets/images/orangecard.png') }}" width="510" height="300" alt=""><div class="orange-contactless">)))</div><p>{{ $phonePayments ? 'Поднесите к терминалу' : 'Ваша карта в личном кабинете' }}</p></div><div class="orange-wallet-circle"></div></div>
            <div class="orange-wallet-copy">
                @if ($phonePayments)
                    <h2>Платите телефоном<br>за границей</h2><p>Добавьте вашу карту в {{ $phonePayments }}. В магазинах, кафе и ресторанах поднесите телефон или часы к терминалу для оплаты</p>
                @else
                    <h2>Реквизиты карты<br>в личном кабинете</h2><p>Откройте карту в личном кабинете, чтобы посмотреть её номер, срок действия и данные для оплаты покупок онлайн</p>
                @endif
                <div class="orange-wallet-badges">
                    @if ($product?->apple_pay_enabled)<span><img src="{{ asset('assets/images/orange/payments/apple-pay.svg') }}" width="68" height="28" alt="Apple Pay"></span>@endif
                    @if ($product?->google_pay_enabled)<span><img src="{{ asset('assets/images/orange/payments/google-pay.svg') }}" width="68" height="28" alt="Google Pay"></span>@endif
                    @if ($product?->samsung_pay_enabled)<span><img src="{{ asset('assets/images/orange/payments/samsung-pay.svg') }}" width="28" height="28" alt=""><b>Samsung Pay</b></span>@endif
                    @if (! $product)<span>Возможности карты уточняются</span>@endif
                </div>
            </div>
        </div>
    </section>

    <section id="phone-setup" class="orange-container orange-section">
        <div class="orange-section-heading"><h2>Как добавить карту<br>в Apple Pay/Google Pay?</h2><p>После выпуска откройте реквизиты карты в личном кабинете. Выберите инструкцию для своего телефона</p></div>
        <div class="orange-phone-guides">
            <details class="orange-phone-guide" @if ($product?->apple_pay_enabled) open @endif>
                <summary><img src="{{ asset('assets/images/orange/payments/apple-pay.svg') }}" width="86" height="36" alt="Apple Pay"><span>На iPhone <b aria-hidden="true">+</b></span></summary>
                @if ($product?->apple_pay_enabled)
                    <ol>
                        <li><strong>Откройте приложение Wallet</strong><p>Нажмите «+», затем выберите «Дебетовая или кредитная карта».</p></li>
                        <li><strong>Введите реквизиты Orange</strong><p>Добавьте карту вручную: укажите имя владельца, номер, срок действия и CVV из личного кабинета.</p></li>
                        <li><strong>Пройдите подтверждение</strong><p>Следуйте указаниям Wallet и инструкции для Orange в личном кабинете. Если требуется дополнительная проверка, обратитесь в поддержку.</p></li>
                        <li><strong>Оплачивайте покупки</strong><p>На iPhone с Face ID дважды нажмите боковую кнопку, выберите Orange и подтвердите оплату. Поднесите верхнюю часть телефона к терминалу и дождитесь отметки об успешной оплате. На моделях с Touch ID используйте кнопку «Домой».</p></li>
                    </ol>
                    <p class="orange-guide-note">Apple Pay должен быть доступен на вашем устройстве и в его настройках региона. Условия подключения Orange смотрите в личном кабинете.</p>
                @else
                    <p class="orange-guide-note">Подключение Orange к Apple Pay сейчас не подтверждено в условиях карты. Уточните доступность у поддержки перед выпуском.</p>
                @endif
            </details>
            <details class="orange-phone-guide" @if ($product?->google_pay_enabled) open @endif>
                <summary><img src="{{ asset('assets/images/orange/payments/google-pay.svg') }}" width="86" height="36" alt="Google Pay"><span>На Android <b aria-hidden="true">+</b></span></summary>
                @if ($product?->google_pay_enabled)
                    <ol>
                        <li><strong>Откройте Google Wallet</strong><p>Нажмите «Добавить в Кошелёк», затем «Платёжная карта» и «Новая кредитная или дебетовая карта».</p></li>
                        <li><strong>Добавьте Orange</strong><p>Введите номер карты, срок действия и CVV. Если потребуется платёжный адрес, возьмите его из личного кабинета.</p></li>
                        <li><strong>Подтвердите карту и настройте NFC</strong><p>Следуйте указаниям приложения. Включите NFC и блокировку экрана. Выберите Google Wallet приложением для бесконтактной оплаты по умолчанию.</p></li>
                        <li><strong>Платите телефоном</strong><p>Разблокируйте телефон и поднесите его к терминалу. Если добавлено несколько карт, перед оплатой выберите Orange.</p></li>
                    </ol>
                    <p class="orange-guide-note">Для оплаты нужен совместимый Android-смартфон с NFC. Доступность Google Pay зависит от устройства и региона. Названия пунктов меню могут отличаться.</p>
                @else
                    <p class="orange-guide-note">Подключение Orange к Google Pay сейчас не подтверждено в условиях карты. Уточните доступность у поддержки перед выпуском.</p>
                @endif
            </details>
            <details class="orange-phone-guide orange-samsung-guide" @if ($product?->samsung_pay_enabled) open @endif>
                <summary><span class="orange-samsung-brand"><img src="{{ asset('assets/images/orange/payments/samsung-pay.svg') }}" width="38" height="38" alt="">Samsung Pay</span><span><b aria-hidden="true">+</b></span></summary>
                @if ($product?->samsung_pay_enabled)
                    <ol>
                        <li><strong>Откройте Samsung Wallet</strong><p>На некоторых устройствах приложение называется Samsung Pay. Выберите добавление платёжной карты.</p></li>
                        <li><strong>Введите данные Orange</strong><p>Укажите номер карты, срок действия и CVV из личного кабинета. Примите условия и пройдите проверку по указаниям приложения.</p></li>
                        <li><strong>Подготовьте оплату</strong><p>Включите NFC. Откройте Samsung Wallet, выберите Orange и подтвердите оплату отпечатком пальца или PIN-кодом. Поднесите телефон к терминалу.</p></li>
                    </ol>
                    <p class="orange-guide-note">Samsung Pay должен быть доступен для модели телефона и региона устройства. Если карта не добавляется, обратитесь в поддержку с текстом ошибки.</p>
                @else
                    <p class="orange-guide-note">Подключение Orange к Samsung Pay сейчас не подтверждено в условиях карты. Уточните доступность у поддержки перед выпуском.</p>
                @endif
            </details>
        </div>
    </section>

    <section id="how" class="orange-container orange-section">
        <div class="orange-section-heading"><h2>Оформите карту<br>до поездки</h2><p>Зарегистрируйтесь, выпустите Orange и пополните баланс. Все действия доступны в личном кабинете</p></div>
        <ol class="orange-steps">
            <li><span>01</span><h3>Создайте кабинет</h3><p>Зарегистрируйтесь в Можно и заполните данные владельца карты.@if ($product?->provider_kyc_required) Для выпуска потребуется проверка личности.@endif</p></li>
            <li><span>02</span><h3>Оплатите выпуск</h3><p>Выберите Orange, сумму первого пополнения и способ оплаты. Стоимость выпуска и сумма зачисления показаны отдельно до оплаты.</p></li>
            <li><span>03</span><h3>Дождитесь карты</h3><p>После подтверждения оплаты начнётся выпуск. Следите за статусом в личном кабинете. Готовая карта и её реквизиты появятся там же.</p></li>
            <li><span>04</span><h3>Начните платить</h3><p>Используйте реквизиты для покупок онлайн.@if ($phonePayments) Добавьте вашу карту в {{ $phonePayments }} для оплаты телефоном.@endif</p></li>
        </ol>
    </section>

    <section class="orange-container orange-section">
        <div class="orange-control-panel"><div><h2>Пополняйте с карт<br>российских банков</h2><p>Пополняйте виртуальную карту с любой карты российского банка через СБП. Управляйте картой в личном кабинете: проверяйте баланс, смотрите историю покупок и пополняйте счёт</p><a class="orange-button orange-button-light" href="{{ $ctaUrl }}">{{ $ctaLabel }} <span aria-hidden="true">↗︎</span></a></div><div class="orange-control-preview"><div class="orange-preview-header"><img src="{{ asset('assets/images/logo_min.svg') }}" width="32" height="32" alt=""><span>Всё в одном кабинете</span><span aria-hidden="true">↗︎</span></div><div class="orange-preview-card"><img src="{{ asset('assets/images/orangecard.png') }}" width="510" height="300" alt="Карта Orange в личном кабинете" loading="lazy"></div><ul><li><span>Баланс карты</span><span aria-hidden="true">✓</span></li><li><span>История операций</span><span aria-hidden="true">✓</span></li><li><span>Реквизиты и управление</span><span aria-hidden="true">✓</span></li></ul><p>Пример отображения карты в личном кабинете</p></div></div>
    </section>

    <section id="terms" class="orange-container orange-section">
        <div class="orange-section-heading"><h2>Стоимость и условия<br>карты Orange</h2><p>Посмотрите стоимость выпуска, комиссии и лимиты перед оформлением</p></div>
        <div class="orange-terms-layout"><div class="orange-price-card"><img src="{{ asset('assets/images/orangecard.png') }}" width="510" height="300" alt="Orange" loading="lazy"><h3>Orange</h3><p>Для путешествий и покупок за границей</p><strong>{{ $rub($product?->price_rub) }}</strong><span>Бесплатное обслуживание</span><a class="orange-button" href="{{ $ctaUrl }}">{{ $ctaLabel }} <span aria-hidden="true">↗︎</span></a></div>
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
                    <div><dt>Первое пополнение: минимум / максимум</dt><dd>{{ $usd($product->issue_min_amount) }} / {{ $usd($product->issue_max_amount) }}</dd></div>
                    <div><dt>Apple Pay</dt><dd>{{ $product->apple_pay_enabled ? 'Доступен' : 'Недоступен' }}</dd></div>
                    <div><dt>Google Pay</dt><dd>{{ $product->google_pay_enabled ? 'Доступен' : 'Недоступен' }}</dd></div>
                    <div><dt>Samsung Pay</dt><dd>{{ $product->samsung_pay_enabled ? 'Доступен' : 'Недоступен' }}</dd></div>
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
        <div><h2>Проверьте карту<br>перед поездкой</h2><p>Проверьте баланс, способ оплаты и условия бронирований заранее</p><a class="orange-text-link" href="https://t.me/mojno_support">Задать вопрос поддержке <span aria-hidden="true">↗︎</span></a></div>
        <ol>
            <li><strong>Проверьте способ оплаты</strong><p>Для оплаты онлайн подготовьте реквизиты карты.@if ($phonePayments) Для покупок в магазинах подключите Orange к {{ $phonePayments }} и проверьте настройку оплаты на телефоне.@else Оплата через Apple Pay, Google Pay и Samsung Pay для этой карты сейчас недоступна.@endif</p></li>
            <li><strong>Проверьте баланс и комиссии</strong><p>Пополните карту на нужную сумму. Если валюта покупки отличается от валюты карты, проверьте комиссию за такую оплату в тарифах.</p></li>
            <li><strong>Уточните правила отеля</strong><p>Уточните, нужен ли залог или физическая карта при заселении. Условия оплаты на сайте и в самом отеле могут отличаться.</p></li>
            <li><strong>Сохраните контакт поддержки</strong><p>Сохраните @mojno_support в Telegram. По вопросам оплаты напишите поддержке и укажите дату и сумму операции из личного кабинета.</p></li>
        </ol>
    </section>

    <section id="questions" class="orange-container orange-section orange-faq">
        <div><h2>Ответы на вопросы<br>о карте Orange</h2><p>Поддержка ответит на вопросы об оформлении, пополнении и оплате картой</p><a class="orange-text-link" href="https://t.me/mojno_support">Написать в Telegram <span aria-hidden="true">↗︎</span></a></div>
        <div>
            @foreach ([
                ['Это физическая или виртуальная карта?', 'Orange — виртуальная карта. Её номер, срок действия и данные для оплаты доступны в личном кабинете. '.$phonePaymentInstructions],
                ['Как пополнить виртуальную карту?', 'Откройте карту в личном кабинете и выберите пополнение через СБП. Оплатите с любой карты российского банка. Деньги поступят на баланс Orange после обработки платежа.'],
                ['Подойдёт ли карта для билетов, отелей и eSIM?', 'Orange можно использовать для таких покупок на сайтах, которые принимают эту карту. Перед оплатой проверьте ограничения в условиях. Если нужна помощь, пришлите поддержке название сайта, страну и сумму покупки.'],
                ['Можно ли платить телефоном уже за границей?', $phonePayments ? 'Да, после подключения Orange к '.$phonePayments.' можно платить у терминалов с бесконтактной оплатой, которые принимают карту. Перед поездкой добавьте карту в телефон и проверьте баланс.' : 'Перед выпуском уточните возможность оплаты телефоном у поддержки. Текущие условия Apple Pay, Google Pay и Samsung Pay указаны в тарифах Orange.'],
                ['Нужен ли интернет для оплаты телефоном?', 'Для добавления карты и проверки баланса нужен интернет. Для оплаты уже добавленной картой подключение обычно не требуется, но телефон или приложение могут запросить его для проверки. Подготовьте доступ к интернету на время поездки.'],
                ['Когда деньги поступят после пополнения?', 'После подтверждения платежа и зачисления на карту баланс обновится в личном кабинете. Срок зависит от способа оплаты и обработки операции. Планируйте пополнение до вылета и важных покупок. Если баланс не обновился, напишите поддержке данные платежа.'],
                ['Можно ли оплатить дорогой отель или несколько билетов?', 'Проверьте ограничения карты, сумму покупки и баланс с учётом комиссий. Если сумма крупная или оплата срочная, уточните условия у поддержки перед пополнением. Лимиты пополнения показаны в тарифах Orange.'],
                ['Можно ли оплатить поездку из России?', 'Используйте реквизиты Orange для покупки билетов и бронирований на зарубежных сайтах, которые принимают эту карту. Покупки в российских магазинах и на российских сайтах недоступны. Доступ к самому сервису зависит от его правил и региона аккаунта.'],
                ['Когда можно начать пользоваться?', 'После выпуска карты и зачисления денег на баланс можно оплачивать покупки онлайн. '.$phonePaymentInstructions],
            ] as [$question, $answer])
                <details><summary>{{ $question }}<span aria-hidden="true">+</span></summary><p>{{ $answer }}</p></details>
            @endforeach
        </div>
    </section>

    <section class="orange-container orange-section orange-final"><div><h2>Оформите Orange<br>для следующей <em>поездки</em></h2><p>Выпустите карту онлайн, пополните баланс и начните с билетов и бронирования отеля</p><a class="orange-button" href="{{ $ctaUrl }}">{{ $ctaLabel }} <span aria-hidden="true">↗︎</span></a></div><img src="{{ asset('assets/images/orangecard.png') }}" width="510" height="300" alt="Orange — карта для путешествий" loading="lazy"></section>
</main>
@include('partials.site-footer')
<div class="orange-mobile-cta"><div><strong>Orange</strong><span>{{ $rub($product?->price_rub) }} за выпуск</span></div><a class="orange-button" href="{{ $ctaUrl }}">{{ $ctaLabel }} ↗︎</a></div>
</body>
</html>
