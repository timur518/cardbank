<footer class="border-t border-[#3A3C40]/10 bg-[#f3f0ee] px-6 py-16 lg:px-10">
    <div class="mx-auto max-w-[1400px]">
        <div class="grid gap-12 lg:grid-cols-[1.5fr_1fr_1fr_1fr]">
            <div>
                <p class="brand-mark"><a href="/" class="brand-mark"><img src="/assets/images/logo.svg" width="155px"></a></p>
                <p class="mt-4 max-w-sm text-sm leading-relaxed text-[#3A3C40]/50">Виртуальные карты для платежей, подписок и покупок по всему миру.</p>
                <p class="footer-title mt-6">Служба поддержки</p>
                <p class="mt-2 flex items-center gap-2 text-sm text-[#3A3C40]/50">
                    <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18v12H3z"/><path d="m3 7 9 6 9-6"/></svg>
                    <a href="mailto:info@mojno.cc" class="hover:text-[#3A3C40]">info@mojno.cc</a>
                </p>
                <p class="mt-2 flex items-center gap-2 text-sm text-[#3A3C40]/50">
                    <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="currentColor"><path d="M21.94 4.7a1.4 1.4 0 0 0-1.46-.24L2.9 11.2a1.3 1.3 0 0 0 .07 2.43l4.57 1.5 1.76 5.64a1.3 1.3 0 0 0 2.17.5l2.5-2.4 4.55 3.36a1.3 1.3 0 0 0 2.06-.77l3.03-14.4a1.4 1.4 0 0 0-.67-1.36ZM9.1 14.4l9.4-7.17-7.87 8.68-.37 3.14-1.16-4.65Z"/></svg>
                    <a href="https://t.me/mojno_support" target="_blank" rel="noopener" class="hover:text-[#3A3C40]">@mojno_support</a>
                </p>
            </div>
            <div>
                <p class="footer-title">Продукт</p>
                <ul class="footer-links">
                    <li><a href="/#lifestyle">Возможности</a></li>
                    <li><a href="/#products">Карты</a></li>
                    <li><a href="/#how">Как это работает</a></li>
                    <li><a href="{{ route('tariffs') }}">Тарифы</a></li>
                </ul>
            </div>
            <div>
                <p class="footer-title">Помощь</p>
                <ul class="footer-links">
                    <li><a href="/#faq">Вопросы</a></li>
                    <li><a href="/#support">Поддержка</a></li>
                    <li><a href="#">Вход</a></li>
                </ul>
            </div>
            <div>
                <p class="footer-title">Документы</p>
                <ul class="footer-links">
                    <li><a href="{{ route('legal.offer') }}">Публичная оферта</a></li>
                    <li><a href="{{ route('legal.privacy-policy') }}">Политика конфиденциальности</a></li>
                    <li><a href="{{ route('legal.kyc-aml') }}">Политика KYC/AML</a></li>
                    <li><a href="{{ route('legal.personal-data-consent') }}">Согласие на обработку персональных данных</a></li>
                    <li><a href="{{ route('legal.messaging-consent') }}">Согласие на получение сообщений</a></li>
                    <li><a href="{{ route('legal.cookie-policy') }}">Политика использования cookie</a></li>
                </ul>
            </div>
        </div>
        <div class="mt-10">
            <p class="max-w-4xl text-xs leading-relaxed text-[#3A3C40]/35">Сервис «Можно» не является банком и не выпускает карты самостоятельно. Карты эмитирует лицензированный партнёр-эмитент.</p>
            <p class="mt-2 text-xs text-[#3A3C40]/30">Platega test</p>
        </div>
    </div>
</footer>
