<footer class="border-t border-[#3A3C40]/10 bg-[#f3f0ee] px-6 py-16 lg:px-10">
    <div class="mx-auto max-w-[1400px]">
        <div class="grid gap-12 lg:grid-cols-[1.5fr_1fr_1fr_1fr]">
            <div>
                <p class="brand-mark"><a href="/" class="brand-mark"><img src="/assets/images/logo.svg" width="155px"></a></p>
                <p class="mt-4 max-w-sm text-sm leading-relaxed text-[#3A3C40]/50">Виртуальные карты для платежей, подписок и покупок по всему миру.</p>
            </div>
            <div>
                <p class="footer-title">Продукт</p>
                <ul class="footer-links">
                    <li><a href="/#lifestyle">Возможности</a></li>
                    <li><a href="/#products">Карты</a></li>
                    <li><a href="/#how">Как это работает</a></li>
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
                    <li><a href="#">Тарифы</a></li>
                </ul>
            </div>
        </div>
        <p class="mt-16 max-w-4xl text-xs leading-relaxed text-[#3A3C40]/35">Можно не является банком и не выпускает карты самостоятельно. Карты эмитирует лицензированный партнёр-эмитент.</p>
        <p class="mt-5 text-xs text-[#3A3C40]/30">© {{ date('Y') }} Можно. Все права защищены.</p>
    </div>
</footer>
