@php
    $available = $product && ! $product->coming_soon;
    $ctaUrl = $available ? 'https://mne.mojno.cc/register' : 'https://t.me/mojno_support';
    $ctaLabel = $available ? 'Оформить карту' : 'Уточнить доступность';
    $ctaSubtitle = 'для покупок онлайн';
    $svgArrow = true;
    $rub = fn ($value) => $value !== null ? number_format((float) $value, 0, ',', ' ').' ₽' : 'Уточняется';
    $usd = fn ($value) => $value !== null ? '$'.number_format((float) $value, 2, ',', ' ') : 'Уточняется';
    $percent = fn ($value) => $value !== null ? number_format((float) $value, 2, ',', ' ').'%' : 'Уточняется';
@endphp
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Black — карта для онлайн-покупок и зарубежных подписок | Можно</title>
    <meta name="description" content="Виртуальная карта Black для онлайн-покупок, нейросетей и зарубежных подписок. Оплата реквизитами на сайтах и в приложениях, пополнение рублями через СБП.">
    <link rel="canonical" href="https://mojno.cc/cards/black">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="ru_RU">
    <meta property="og:title" content="Black — карта для ваших сервисов и подписок">
    <meta property="og:description" content="Оплачивайте онлайн-покупки картой иностранного банка. Выпуск онлайн и пополнение с карт российских банков.">
    <meta property="og:url" content="https://mojno.cc/cards/black">
    <meta property="og:image" content="{{ asset('assets/images/blackcardbg.png') }}">
    <link rel="icon" href="{{ asset('assets/images/favicon.svg') }}" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=sofia-sans:400,500,600,700,800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- Общий стиль карточных лендингов; Black добавляет только свои блоки. --}}
    <link rel="stylesheet" href="{{ asset('assets/css/orange.css') }}?v={{ filemtime(public_path('assets/css/orange.css')) }}">
    <link rel="stylesheet" href="{{ asset('assets/css/black.css') }}?v={{ filemtime(public_path('assets/css/black.css')) }}">
    {!! $analyticsCodes['analytics_counter_id'] ?? '' !!}
    {!! $analyticsCodes['analytics_pixel_ids'] ?? '' !!}
</head>
<body class="orange-page black-page font-sans antialiased">
<a class="orange-skip" href="#main">Перейти к содержимому</a>
@include('partials.site-header')
<main id="main">
    <section class="orange-container orange-hero black-hero">
        <div class="orange-hero-copy">
            <div class="orange-product-name">Black <span>Карта иностранного банка<br>для онлайн-покупок</span></div>
            <h1>Карта для<br>онлайн-покупок<br><em>и подписок</em></h1>
            <p>Нейросети, рабочие инструменты, музыка и покупки на зарубежных сайтах. Платите картой Black в своих аккаунтах и пополняйте баланс с любой карты российского банка</p>
            <div class="orange-actions">
                <a class="orange-button" href="{{ $ctaUrl }}">@include('cards.partials.apply-button-label') @include('cards.partials.arrow-icon')</a>
                <a class="orange-text-link" href="#terms">Стоимость и условия <span aria-hidden="true">↓</span></a>
            </div>
            <div class="orange-hero-facts">
                <div><strong>{{ $rub($product?->price_rub) }}</strong><span>за выпуск карты</span></div>
                <div><strong>Онлайн</strong><span>на сайтах и в приложениях</span></div>
                <div><strong class="orange-sbp"><img src="{{ asset('assets/images/orange/payments/sbp.svg') }}" width="30" height="30" alt=""><span>СБП</span></strong><span>пополнение рублями</span></div>
            </div>
        </div>
        <div class="black-hero-visual">
            <div class="black-visual-title">Ваши планы<br>на месяц вперёд</div>
            <div class="black-floating-logos" aria-hidden="true">
                <img src="{{ asset('assets/images/black/services/chatgpt.svg') }}" width="36" height="36" alt="">
                <img src="{{ asset('assets/images/black/services/figma.svg') }}" width="36" height="36" alt="">
                <img src="{{ asset('assets/images/black/services/spotify.png') }}" width="36" height="36" alt="">
            </div>
            <img class="black-hero-card" src="{{ asset('assets/images/blackcard.png') }}" width="510" height="300" alt="Виртуальная карта Black от Можно" fetchpriority="high">
            <div class="black-payment-preview" aria-hidden="true">
                <div class="black-preview-heading"><span>Оплата картой</span>@include('cards.partials.arrow-icon')</div>
                <div class="black-preview-field"><span>Номер карты</span><strong>•••• &nbsp; •••• &nbsp; •••• &nbsp; ••••</strong></div>
                <div class="black-preview-fields"><div><span>Срок действия</span><strong>ММ / ГГ</strong></div><div><span>CVV</span><strong>•••</strong></div></div>
                <div class="black-preview-caption">Реквизиты — в личном кабинете</div>
            </div>
        </div>
    </section>

    <section id="services" class="orange-container orange-section">
        <div class="orange-section-heading">
            <h2>Для работы, увлечений<br>и повседневных покупок</h2>
            <p>Выберите нужный сервис и используйте Black при оплате банковской картой</p>
        </div>
        <div class="orange-booking-grid orange-services-grid black-services">
            @foreach ([
                ['Нейросети', 'Создавайте тексты, изображения и код. Подключайте платные возможности инструментов, с которыми работаете каждый день', [['chatgpt.svg', 'ChatGPT'], ['claude.svg', 'Claude'], ['midjourney.svg', 'Midjourney'], ['cursor.svg', 'Cursor']]],
                ['Работа и творчество', 'Оплачивайте программы для дизайна, совместной работы и личных проектов в своём аккаунте', [['figma.svg', 'Figma'], ['adobe.svg', 'Adobe'], ['notion.svg', 'Notion'], ['canva.svg', 'Canva']]],
                ['Музыка и кино', 'Выбирайте подписки для отдыха: любимые исполнители, новые фильмы и видео без рекламы', [['spotify.png', 'Spotify'], ['netflix.svg', 'Netflix'], ['youtube-premium.svg', 'YouTube Premium']]],
                ['Игры и онлайн-покупки', 'Покупайте игры, цифровые товары и вещи в зарубежных интернет-магазинах с оплатой по реквизитам', [['steam.png', 'Steam'], ['amazon.png', 'Amazon']]],
            ] as [$title, $text, $services])
                <article>
                    <h3>{{ $title }}</h3><p>{{ $text }}</p>
                    <ul class="orange-service-logos">
                        @foreach ($services as [$logo, $service])
                            <li><img src="{{ asset('assets/images/black/services/'.$logo) }}" width="32" height="32" alt="" loading="lazy"><span>{{ $service }}</span></li>
                        @endforeach
                    </ul>
                </article>
            @endforeach
        </div>
        <a class="orange-text-link black-service-link" href="#service-question">Как проверить нужный сервис <span aria-hidden="true">↓</span></a>
    </section>

    <section class="orange-container orange-section black-account-panel">
        <div class="black-account-photo"><img src="{{ asset('assets/images/chatgpt.png') }}" width="1491" height="1055" alt="Работа с нейросетью за компьютером" loading="lazy"></div>
        <div class="black-account-copy">
            <h2>Платите<br>в своём аккаунте</h2>
            <p>Выбирайте тариф на сайте сервиса и оплачивайте его картой Black. Доступ к подписке и настройки остаются в вашем аккаунте</p>
            <ul>
                <li><strong>Оплачивайте самостоятельно</strong><span>Введите реквизиты Black на сайте или в приложении. Передавать кому-либо пароль от сервиса не нужно</span></li>
                <li><strong>Следите за продлением</strong><span>Проверяйте дату следующего списания в сервисе и пополняйте карту заранее</span></li>
                <li><strong>Управляйте картой онлайн</strong><span>Баланс, реквизиты и история покупок доступны в личном кабинете Можно</span></li>
            </ul>
        </div>
    </section>

    <section id="how-to-pay" class="orange-container orange-section">
        <div class="orange-section-heading"><h2>Как оплатить покупку<br>или подключить подписку</h2><p>Всё, что нужно для оплаты, есть в личном кабинете карты</p></div>
        <ol class="black-payment-steps">
            <li><span class="black-step-number">01</span><h3>Выберите оплату картой</h3><p>Откройте нужный сайт или приложение, выберите товар или тариф. На странице оплаты укажите банковскую карту</p><div class="black-step-example" aria-hidden="true"><span>Способ оплаты</span><strong>Банковская карта <span>◉</span></strong></div></li>
            <li><span class="black-step-number">02</span><h3>Введите реквизиты Black</h3><p>Скопируйте номер карты, срок действия и CVV из личного кабинета. Если нужен billing address, используйте раздел «Платёжный адрес»</p><div class="black-step-example" aria-hidden="true"><span>Данные для оплаты</span><strong>Номер · срок · CVV</strong></div></li>
            <li><span class="black-step-number">03</span><h3>Подтвердите покупку</h3><p>@if ($product?->three_ds_supported) Если сайт запросит подтверждение 3DS, откройте «3DS коды» на странице карты в личном кабинете.@else Следуйте инструкциям на странице оплаты.@endif Проверьте результат на сайте и операцию в истории карты</p><div class="black-step-example" aria-hidden="true"><span>В личном кабинете</span><strong>История операций @include('cards.partials.arrow-icon')</strong></div></li>
        </ol>
        <details class="orange-online-guide">
            <summary>Как настроить автоматическое продление подписки <span aria-hidden="true">+</span></summary>
            <ol>
                <li><strong>Сохраните Black в сервисе</strong><p>Укажите карту в настройках оплаты вашего аккаунта, если сервис поддерживает автоматическое продление.</p></li>
                <li><strong>Пополните баланс заранее</strong><p>На карте должно хватать денег на подписку и комиссии. Их размер указан в условиях Black ниже.</p></li>
                <li><strong>Управляйте подпиской в сервисе</strong><p>Дату списания, смену тарифа и отмену продления проверяйте там, где оформляли подписку. Настройки карты в Можно не отменяют подписку.</p></li>
            </ol>
        </details>
    </section>

    <section id="issue" class="orange-container orange-section">
        <div class="orange-section-heading"><h2>От регистрации<br>до первой покупки</h2><p>Оформите виртуальную карту онлайн и получите реквизиты в личном кабинете</p></div>
        <ol class="orange-steps">
            <li><span>01</span><h3>Создайте аккаунт</h3><p>Зарегистрируйте аккаунт в системе Можно</p></li>
            <li><span>02</span><h3>Выберите Black</h3><p>Откройте выпуск новой карты и выберите Black. Проверьте стоимость и условия</p></li>
            <li><span>03</span><h3>Оплатите выпуск</h3><p>После подтверждения оплаты начнётся выпуск. Статус заказа доступен в личном кабинете</p></li>
            <li><span>04</span><h3>Начните пользоваться</h3><p>Когда карта будет готова, пополните баланс и откройте реквизиты для первой онлайн-покупки</p></li>
        </ol>
        <div class="black-issue-action"><a class="orange-button" href="{{ $ctaUrl }}">@include('cards.partials.apply-button-label') @include('cards.partials.arrow-icon')</a></div>
    </section>

    <section class="orange-container orange-section">
        <div class="orange-control-panel">
            <div>
                <h2>Пополняйте с карт<br>российских банков</h2>
                <p>Выберите пополнение через СБП и оплатите рублями с любой карты российского банка. Проверяйте баланс, смотрите историю покупок и пополняйте карту в одном кабинете</p>
                <div class="black-topup-brand"><img src="{{ asset('assets/images/orange/payments/sbp.svg') }}" width="36" height="36" alt="Логотип СБП"><span>Пополнение рублями через СБП</span></div>
                <a class="orange-button orange-button-light" href="{{ $ctaUrl }}">@include('cards.partials.apply-button-label') @include('cards.partials.arrow-icon')</a>
            </div>
            <div class="orange-control-preview">
                <div class="orange-preview-header"><img src="{{ asset('assets/images/logo_min.svg') }}" width="32" height="32" alt=""><span>Всё в одном кабинете</span>@include('cards.partials.arrow-icon')</div>
                <div class="orange-preview-card"><img src="{{ asset('assets/images/blackcard.png') }}" width="510" height="300" alt="Карта Black в личном кабинете" loading="lazy"></div>
                <ul><li><span>Баланс карты</span>@include('cards.partials.arrow-icon')</li><li><span>История покупок</span>@include('cards.partials.arrow-icon')</li><li><span>Реквизиты для оплаты</span>@include('cards.partials.arrow-icon')</li></ul>
            </div>
        </div>
    </section>

    <section id="terms" class="orange-container orange-section">
        <div class="orange-section-heading"><h2>Стоимость и условия<br>карты Black</h2><p>Стоимость выпуска, комиссии и лимиты карты перед вами</p></div>
        <div class="orange-terms-layout">
            <div class="orange-price-card">
                <img src="{{ asset('assets/images/blackcard.png') }}" width="510" height="300" alt="Black" loading="lazy">
                <h3>Black</h3><p>Для онлайн-покупок и подписок</p>
                <strong>{{ $rub($product?->price_rub) }}</strong><span>за выпуск виртуальной карты</span>
                <a class="orange-button" href="{{ $ctaUrl }}">@include('cards.partials.apply-button-label') @include('cards.partials.arrow-icon')</a>
            </div>
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
                        <div><dt>Способ оплаты</dt><dd>Реквизитами онлайн</dd></div>
                        <div><dt>Подтверждение оплаты 3DS</dt><dd>{{ $product->three_ds_supported ? 'Поддерживается' : 'Не поддерживается' }}</dd></div>
                    </dl>
                @else
                    <p>Условия Black сейчас уточняются. Напишите в поддержку, чтобы узнать о выпуске карты.</p>
                @endif
                <a class="orange-text-link" href="{{ route('tariffs') }}">Полные тарифы и условия @include('cards.partials.arrow-icon')</a>
            </div>
        </div>
        @if ($product?->restricted_merchants || $product?->full_terms)
            <details class="orange-terms-details">
                <summary>Ограничения использования</summary>
                @if ($product->restricted_merchants)<div class="legal-doc-content">{!! $product->restricted_merchants !!}</div>@endif
                @if ($product->full_terms)<div class="legal-doc-content">{!! $product->full_terms !!}</div>@endif
            </details>
        @endif
    </section>

    <section id="questions" class="orange-container orange-section orange-faq">
        <div><h2>Ответы на вопросы<br>о карте Black</h2><p>Поможем разобраться с выпуском, пополнением и оплатой нужного сервиса</p><a class="orange-text-link" href="https://t.me/mojno_support">Написать в Telegram @include('cards.partials.arrow-icon')</a></div>
        <div>
            <details id="service-question"><summary>Подойдёт ли карта для моего сервиса?<span aria-hidden="true">+</span></summary><p>Black предназначена для онлайн-оплаты по реквизитам. Проверьте ограничения карты и способы оплаты на сайте сервиса. Логотипы выше показывают примеры сервисов, а возможность оплаты зависит от их правил и региона аккаунта. Если сомневаетесь, пришлите название сервиса в поддержку до выпуска карты.</p></details>
            @foreach ([
                ['Можно ли оплачивать сервисы из России?', 'Да, используйте реквизиты Black для онлайн-покупок на зарубежных сайтах. Доступ к самому сервису и доступность подписки определяются его правилами и регионом вашего аккаунта.'],
                ['Это виртуальная карта? Где взять реквизиты?', 'Black — виртуальная карта для онлайн-покупок. После выпуска номер, срок действия и CVV будут доступны на странице карты в личном кабинете Можно. Физическая карта для такой оплаты не нужна.'],
                ['Можно ли подключить автоматическое продление?', 'Если сервис поддерживает списания по сохранённой карте, укажите Black в настройках оплаты. Перед продлением пополните баланс на сумму подписки с учётом комиссий. Сменить тариф или отменить подписку можно в самом сервисе.'],
                ['Как пополнить карту рублями?', 'Откройте Black в личном кабинете, выберите пополнение через СБП и оплатите с любой карты российского банка. После обработки платежа и зачисления денег баланс обновится в кабинете. Комиссия и лимиты пополнения указаны в условиях карты.'],
                ['Нужно ли устанавливать приложение?', 'Для управления Black достаточно личного кабинета в браузере на телефоне или компьютере. Там можно открыть реквизиты, проверить баланс, пополнить карту и посмотреть историю покупок.'],
                ['Можно ли добавить Black в Apple Pay или Google Pay?', 'Black используется только для онлайн-покупок по реквизитам. Добавление в Apple Pay, Google Pay и Samsung Pay, а также оплата у терминалов для этой карты недоступны.'],
                ['Когда можно начать оплачивать покупки?', 'После выпуска карты и зачисления денег на баланс. Откройте реквизиты Black в личном кабинете и введите их на странице оплаты нужного сайта или приложения. Статус выпуска доступен в заказах.'],
                ['Где взять платёжный адрес?', 'Если при оплате просят billing address, откройте раздел «Платёжный адрес» на странице карты и скопируйте данные оттуда. Это адрес для заполнения платёжной формы; адрес доставки покупки указывается отдельно.'],
                ['Как подтверждается покупка?', $product?->three_ds_supported ? 'Если сервис запросит подтверждение 3DS, откройте раздел «3DS коды» на странице карты в личном кабинете. Используйте код для текущей покупки в форме подтверждения оплаты.' : 'Следуйте инструкции на странице оплаты. Текущая поддержка 3DS указана в условиях Black. Если сервис обязательно требует 3DS, проверьте этот пункт перед выпуском.'],
            ] as [$question, $answer])
                <details><summary>{{ $question }}<span aria-hidden="true">+</span></summary><p>{{ $answer }}</p></details>
            @endforeach
        </div>
    </section>

    <section class="orange-container orange-section orange-final">
        <div><h2>Ваши любимые сервисы<br>с картой <em>Black</em></h2><p>Оформите карту онлайн, пополните баланс и оплатите нужную покупку в своём аккаунте</p><a class="orange-button" href="{{ $ctaUrl }}">@include('cards.partials.apply-button-label') @include('cards.partials.arrow-icon')</a></div>
        <img src="{{ asset('assets/images/blackcard.png') }}" width="510" height="300" alt="Black — карта для онлайн-покупок" loading="lazy">
    </section>
</main>
@include('partials.site-footer')
<div class="orange-mobile-cta"><div><strong>Black</strong><span>{{ $rub($product?->price_rub) }} за выпуск</span></div><a class="orange-button" href="{{ $ctaUrl }}">@include('cards.partials.apply-button-label', ['mobile' => true])</a></div>
</body>
</html>
