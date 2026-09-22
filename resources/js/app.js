import './bootstrap';

document.addEventListener('DOMContentLoaded', () => {
    // Reveal-анимация: элементы появляются один раз при попадании в viewport.
    const revealables = document.querySelectorAll('[data-reveal]');

    if ('IntersectionObserver' in window && revealables.length) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.12,
            rootMargin: '0px 0px -50px 0px',
        });

        revealables.forEach((element) => observer.observe(element));
    } else {
        revealables.forEach((element) => {
            element.classList.add('is-visible');
        });
    }

    // Шапка находится поверх hero и становится плотнее после начала прокрутки.
    const header = document.querySelector('[data-site-header]');

    if (header) {
        const updateHeader = () => {
            header.classList.toggle('is-scrolled', window.scrollY > 20);
        };

        updateHeader();

        window.addEventListener('scroll', updateHeader, {
            passive: true,
        });
    }

    // Мобильное меню.
    const menuToggle = document.querySelector('[data-menu-toggle]');
    const mobileMenu = document.querySelector('[data-mobile-menu]');

    if (menuToggle && mobileMenu) {
        menuToggle.addEventListener('click', () => {
            const isOpen = mobileMenu.classList.toggle('hidden') === false;

            menuToggle.setAttribute(
                'aria-expanded',
                isOpen ? 'true' : 'false'
            );
        });

        mobileMenu.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', () => {
                mobileMenu.classList.add('hidden');
                menuToggle.setAttribute('aria-expanded', 'false');
            });
        });
    }

    /*
     * Главный hero-carousel.
     *
     * Центральный элемент всегда один.
     * Соседние элементы частично видны по краям.
     * Переключение происходит с центрированием выбранного овала.
     */
    document.querySelectorAll('[data-hero-carousel]').forEach((carousel) => {
        const track = carousel.querySelector('[data-hero-track]');
        const slides = [
            ...carousel.querySelectorAll('[data-hero-slide]'),
        ];

        const prevButton = carousel.querySelector('[data-hero-prev]');
        const nextButton = carousel.querySelector('[data-hero-next]');

        const hero = carousel.closest('.hero');

        const dots = hero?.querySelector('[data-hero-dots]');
        const indexLabel = hero?.querySelector('[data-hero-index]');

        if (!track || !slides.length) {
            return;
        }

        let activeIndex = 0;
        let scrollFrame = null;

        /*
         * Центрирование слайда.
         *
         * offsetLeft используется вместо фиксированных размеров,
         * поэтому carousel корректно работает на разных ширинах экрана.
         */
        const centerSlide = (index, smooth = true) => {
            const slide = slides[index];

            if (!slide) {
                return;
            }

            const left =
                slide.offsetLeft -
                (track.clientWidth - slide.offsetWidth) / 2;

            track.scrollTo({
                left,
                behavior: smooth ? 'smooth' : 'auto',
            });
        };

        // Создание индикаторов.
        if (dots) {
            slides.forEach((_, index) => {
                const dot = document.createElement('button');

                dot.type = 'button';
                dot.className = 'hero-dot';
                dot.setAttribute(
                    'aria-label',
                    `Слайд ${index + 1}`
                );

                dot.addEventListener('click', () => {
                    goTo(index);
                });

                dots.appendChild(dot);
            });
        }

        /*
         * Определение ближайшего к центру слайда.
         *
         * Это позволяет корректно обновлять active-state
         * и при ручном drag/swipe, и при программном переключении.
         */
        const paintState = () => {
            const center =
                track.scrollLeft +
                track.clientWidth / 2;

            let closestIndex = 0;
            let closestDistance = Infinity;

            slides.forEach((slide, index) => {
                const slideCenter =
                    slide.offsetLeft +
                    slide.offsetWidth / 2;

                const distance = Math.abs(
                    center - slideCenter
                );

                if (distance < closestDistance) {
                    closestDistance = distance;
                    closestIndex = index;
                }
            });

            activeIndex = closestIndex;

            slides.forEach((slide, index) => {
                slide.classList.toggle(
                    'is-active',
                    index === activeIndex
                );
            });

            dots
                ?.querySelectorAll('.hero-dot')
                .forEach((dot, index) => {
                    dot.classList.toggle(
                        'is-active',
                        index === activeIndex
                    );
                });

            if (indexLabel) {
                indexLabel.textContent =
                    `${String(activeIndex + 1).padStart(2, '0')} / ` +
                    `${String(slides.length).padStart(2, '0')}`;
            }

            if (prevButton) {
                prevButton.disabled = activeIndex === 0;
            }

            if (nextButton) {
                nextButton.disabled =
                    activeIndex === slides.length - 1;
            }
        };

        const updateActive = () => {
            if (scrollFrame) {
                cancelAnimationFrame(scrollFrame);
            }

            scrollFrame = requestAnimationFrame(() => {
                paintState();
            });
        };

        const goTo = (index) => {
            const target = Math.max(
                0,
                Math.min(index, slides.length - 1)
            );

            centerSlide(target);
            paintState();
        };

        prevButton?.addEventListener('click', () => {
            goTo(activeIndex - 1);
        });

        nextButton?.addEventListener('click', () => {
            goTo(activeIndex + 1);
        });

        track.addEventListener(
            'scroll',
            updateActive,
            {
                passive: true,
            }
        );

        track.addEventListener('keydown', (event) => {
            if (event.key === 'ArrowLeft') {
                event.preventDefault();
                goTo(activeIndex - 1);
            }

            if (event.key === 'ArrowRight') {
                event.preventDefault();
                goTo(activeIndex + 1);
            }
        });

        window.addEventListener('resize', () => {
            centerSlide(activeIndex, false);
            paintState();
        });

        // Первоначальное центрирование.
        requestAnimationFrame(() => {
            centerSlide(0, false);
            paintState();
        });
    });

    /*
     * Второй carousel — бесконечная карусель овалов.
     *
     * Перед первым и после последнего реального слайда добавляется по одному клону,
     * чтобы у первого и последнего слайда тоже был виден кусочек соседнего с обеих
     * сторон. Когда прокрутка останавливается на клоне, происходит мгновенный (без
     * анимации) перескок на тот же по виду настоящий слайд — так создаётся иллюзия
     * бесконечной прокрутки в обе стороны.
     */
    document.querySelectorAll('[data-carousel]').forEach((carousel) => {
        const track = carousel.querySelector('[data-carousel-track]');
        const realSlides = [...carousel.querySelectorAll('[data-carousel-item]')];

        const prevButton = carousel.querySelector('[data-carousel-prev]');
        const nextButton = carousel.querySelector('[data-carousel-next]');
        const dots = carousel.querySelector('[data-carousel-dots]');

        if (!track || realSlides.length < 2) {
            return;
        }

        const realCount = realSlides.length;

        const firstClone = realSlides[0].cloneNode(true);
        const lastClone = realSlides[realCount - 1].cloneNode(true);

        [firstClone, lastClone].forEach((clone) => {
            clone.removeAttribute('data-reveal');
            clone.removeAttribute('style');
            clone.setAttribute('data-carousel-clone', 'true');
            clone.setAttribute('aria-hidden', 'true');
            clone.setAttribute('tabindex', '-1');
            clone.classList.add('is-visible');
        });

        track.insertBefore(lastClone, realSlides[0]);
        track.appendChild(firstClone);

        const slides = [...track.querySelectorAll('[data-carousel-item]')];
        const firstRealIndex = 1;
        const lastRealIndex = realCount;
        const lastSlideIndex = slides.length - 1;

        let activeIndex = firstRealIndex;
        let settleTimer = null;

        const centerSlide = (index, smooth = true) => {
            const slide = slides[index];

            if (!slide) {
                return;
            }

            const left = slide.offsetLeft - (track.clientWidth - slide.offsetWidth) / 2;

            track.scrollTo({
                left,
                behavior: smooth ? 'smooth' : 'auto',
            });
        };

        const realIndexOf = (index) => (((index - firstRealIndex) % realCount) + realCount) % realCount;

        const paintState = () => {
            const center = track.scrollLeft + track.clientWidth / 2;

            let closestIndex = activeIndex;
            let closestDistance = Infinity;

            slides.forEach((slide, index) => {
                const slideCenter = slide.offsetLeft + slide.offsetWidth / 2;
                const distance = Math.abs(center - slideCenter);

                if (distance < closestDistance) {
                    closestDistance = distance;
                    closestIndex = index;
                }
            });

            activeIndex = closestIndex;

            slides.forEach((slide, index) => {
                slide.classList.toggle('is-active', index === activeIndex);
            });

            const realIndex = realIndexOf(activeIndex);

            dots?.querySelectorAll('.carousel-dot').forEach((dot, index) => {
                dot.classList.toggle('is-active', index === realIndex);
            });
        };

        // Если после остановки прокрутки активен оказался клон — мгновенно переключаемся
        // на соответствующий настоящий слайд с той же картинкой.
        const settleOnRealSlide = () => {
            if (activeIndex === 0) {
                activeIndex = lastRealIndex;
                centerSlide(activeIndex, false);
                paintState();
            } else if (activeIndex === lastSlideIndex) {
                activeIndex = firstRealIndex;
                centerSlide(activeIndex, false);
                paintState();
            }
        };

        const goTo = (index) => {
            activeIndex = index;
            centerSlide(activeIndex);
        };

        // Индикаторы carousel — по одному на каждый настоящий слайд.
        realSlides.forEach((_, index) => {
            if (!dots) {
                return;
            }

            const dot = document.createElement('button');

            dot.type = 'button';
            dot.className = 'carousel-dot';
            dot.setAttribute('aria-label', `Слайд ${index + 1}`);

            dot.addEventListener('click', () => {
                goTo(firstRealIndex + index);
            });

            dots.appendChild(dot);
        });

        prevButton?.addEventListener('click', () => {
            goTo(activeIndex - 1);
        });

        nextButton?.addEventListener('click', () => {
            goTo(activeIndex + 1);
        });

        track.addEventListener(
            'scroll',
            () => {
                paintState();

                clearTimeout(settleTimer);
                settleTimer = setTimeout(settleOnRealSlide, 120);
            },
            {
                passive: true,
            }
        );

        track.addEventListener('keydown', (event) => {
            if (event.key === 'ArrowLeft') {
                event.preventDefault();
                goTo(activeIndex - 1);
            }

            if (event.key === 'ArrowRight') {
                event.preventDefault();
                goTo(activeIndex + 1);
            }
        });

        window.addEventListener('resize', () => {
            centerSlide(activeIndex, false);
            paintState();
        });

        // Первый реальный слайд открывается ровно по центру экрана.
        requestAnimationFrame(() => {
            activeIndex = firstRealIndex;
            centerSlide(firstRealIndex, false);
            paintState();
        });
    });

    // FAQ accordion.
    document.querySelectorAll('[data-faq-item]').forEach((item) => {
        const button = item.querySelector('[data-faq-button]');
        const answer = item.querySelector('.faq-answer');

        if (!button || !answer) {
            return;
        }

        button.addEventListener('click', () => {
            const isOpen =
                item.getAttribute('data-open') === 'true';

            document
                .querySelectorAll('[data-faq-item]')
                .forEach((other) => {
                    other.setAttribute(
                        'data-open',
                        'false'
                    );

                    other
                        .querySelector(
                            '[data-faq-button]'
                        )
                        ?.setAttribute(
                            'aria-expanded',
                            'false'
                        );

                    const otherAnswer =
                        other.querySelector(
                            '.faq-answer'
                        );

                    if (otherAnswer) {
                        otherAnswer.style.maxHeight = null;
                    }
                });

            if (!isOpen) {
                item.setAttribute(
                    'data-open',
                    'true'
                );

                button.setAttribute(
                    'aria-expanded',
                    'true'
                );

                answer.style.maxHeight =
                    `${answer.scrollHeight}px`;
            }
        });
    });

    // Форма оформления карты: селектор карты в сайдбаре.
    document.querySelectorAll('[data-card-selector]').forEach((selector) => {
        const options = [...selector.querySelectorAll('.apply-card-option')];

        options.forEach((option) => {
            const input = option.querySelector('input[type="radio"]');

            input?.addEventListener('change', () => {
                options.forEach((other) => {
                    other.classList.toggle('is-active', other === option);
                });
            });
        });
    });

    // Форма оформления карты: переход между шагами «Заявка» → «Загрузка» →
    // «Пополнение баланса». Чисто фронтенд, без отправки данных на бэкенд.
    document.querySelectorAll('[data-apply-form]').forEach((form) => {
        const wrap = form.closest('[data-apply-wrap]');

        if (!wrap) {
            return;
        }

        const loading = wrap.querySelector('[data-apply-step="loading"]');
        const topup = wrap.querySelector('[data-apply-step="topup"]');

        form.addEventListener('submit', (event) => {
            event.preventDefault();

            form.classList.remove('is-current');
            loading?.classList.add('is-current');

            setTimeout(() => {
                loading?.classList.remove('is-current');
                topup?.classList.add('is-current');
            }, 1600);
        });
    });

    // Форма пополнения баланса: отправка пока не подключена к платёжной системе —
    // только гасим перезагрузку страницы по умолчанию.
    document.querySelectorAll('[data-topup-form]').forEach((form) => {
        form.addEventListener('submit', (event) => event.preventDefault());
    });

    // Форма пополнения баланса: селектор способа оплаты.
    document.querySelectorAll('[data-pay-selector]').forEach((selector) => {
        const options = [...selector.querySelectorAll('.apply-pay-option')];

        options.forEach((option) => {
            const input = option.querySelector('input[type="radio"]');

            input?.addEventListener('change', () => {
                options.forEach((other) => {
                    other.classList.toggle('is-active', other === option);
                });
            });
        });
    });

    // Форма пополнения баланса: переключатель валюты суммы (₽ / $) меняет подпись поля.
    document.querySelectorAll('[data-currency-toggle]').forEach((toggle) => {
        const buttons = [...toggle.querySelectorAll('button')];
        const field = toggle.closest('.apply-field');
        const label = field?.querySelector('[data-topup-amount-label]');
        const input = field?.querySelector('[data-topup-amount-input]');

        const labels = {
            rub: 'Введите сколько заплатить',
            usd: 'Введите сколько зачислить на карту',
        };

        const placeholders = {
            rub: '5 000',
            usd: '50',
        };

        buttons.forEach((button) => {
            button.addEventListener('click', () => {
                buttons.forEach((other) => other.classList.toggle('is-active', other === button));

                const currency = button.dataset.currency;

                if (label && labels[currency]) {
                    label.textContent = labels[currency];
                }

                if (input && placeholders[currency]) {
                    input.placeholder = placeholders[currency];
                    input.value = '';
                }
            });
        });
    });

    // Форма пополнения баланса: итоговая сумма «К оплате» в рублях.
    //
    // Формула повторяет OrderController::issue()/convertTopup() из ЛК: клиент платит
    // цену карты (price_rub) плюс сумму пополнения, пересчитанную в рубли по курсу
    // (data-usd-rate) с добавлением комиссии провайдера за пополнение
    // (data-fee-percent из выбранной в сайдбаре карты) — сама сумма, которая попадёт
    // на карту, от этой комиссии не зависит, платит её клиент сверху.
    document.querySelectorAll('[data-topup-form]').forEach((form) => {
        const amountInput = form.querySelector('[data-topup-amount-input]');
        const totalEl = form.querySelector('[data-topup-total]');
        const toggle = form.querySelector('[data-currency-toggle]');
        const usdRate = parseFloat(form.dataset.usdRate) || 0;

        if (!amountInput || !totalEl) {
            return;
        }

        const formatRub = (value) => `${Math.round(value).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ')} ₽`;

        const selectedCard = () => document.querySelector('input[name="card_product"]:checked');

        const updateTotal = () => {
            const currency = toggle?.querySelector('button.is-active')?.dataset.currency ?? 'usd';
            const raw = parseFloat(amountInput.value.replace(/[^\d.,]/g, '').replace(',', '.')) || 0;
            const card = selectedCard();
            const priceRub = parseFloat(card?.dataset.priceRub) || 0;
            const feePercent = parseFloat(card?.dataset.feePercent) || 0;

            const topupUsd = currency === 'usd' ? raw : (usdRate > 0 ? raw / usdRate : 0);
            const feeUsd = topupUsd * (feePercent / 100);
            const topupRub = (topupUsd + feeUsd) * usdRate;
            const totalRub = priceRub + topupRub;

            totalEl.textContent = `К оплате: ${formatRub(totalRub)}`;
        };

        amountInput.addEventListener('input', updateTotal);
        toggle?.querySelectorAll('button').forEach((button) => button.addEventListener('click', updateTotal));
        document.querySelectorAll('input[name="card_product"]').forEach((input) => input.addEventListener('change', updateTotal));

        updateTotal();
    });

    // Ротация подсказок внизу сайдбара формы оформления карты.
    document.querySelectorAll('[data-rotating-tip]').forEach((tip) => {
        const items = [...tip.querySelectorAll('[data-tip]')];

        if (items.length < 2) {
            return;
        }

        let activeIndex = items.findIndex((item) => item.classList.contains('is-active'));

        if (activeIndex === -1) {
            activeIndex = 0;
        }

        setInterval(() => {
            items[activeIndex].classList.remove('is-active');
            activeIndex = (activeIndex + 1) % items.length;
            items[activeIndex].classList.add('is-active');
        }, 3000);
    });

    /*
     * Транслитерация ФИО в реальном времени.
     *
     * Первая буква каждого транслитерируемого символа наследует регистр исходной
     * кириллической буквы (Х → Kh, а не KH), остальные символы приводятся к нижнему.
     */
    const translitMap = {
        а: 'a', б: 'b', в: 'v', г: 'g', д: 'd', е: 'e', ё: 'e', ж: 'zh', з: 'z',
        и: 'i', й: 'y', к: 'k', л: 'l', м: 'm', н: 'n', о: 'o', п: 'p', р: 'r',
        с: 's', т: 't', у: 'u', ф: 'f', х: 'kh', ц: 'ts', ч: 'ch', ш: 'sh',
        щ: 'shch', ъ: '', ы: 'y', ь: '', э: 'e', ю: 'yu', я: 'ya',
    };

    const transliterate = (value) => value.replace(/[а-яёА-ЯЁ]/g, (char) => {
        const lower = char.toLowerCase();
        const mapped = translitMap[lower];

        if (mapped === undefined) {
            return char;
        }

        if (char === lower) {
            return mapped;
        }

        return mapped.charAt(0).toUpperCase() + mapped.slice(1);
    });

    document.querySelectorAll('[data-translit-input]').forEach((input) => {
        input.addEventListener('input', () => {
            const transliterated = transliterate(input.value);

            if (transliterated !== input.value) {
                input.value = transliterated;
            }
        });
    });

    // Маска даты рождения: дд.мм.гггг.
    document.querySelectorAll('[data-dob-input]').forEach((input) => {
        input.addEventListener('input', () => {
            const digits = input.value.replace(/\D/g, '').slice(0, 8);

            let formatted = digits.slice(0, 2);

            if (digits.length > 2) {
                formatted += `.${digits.slice(2, 4)}`;
            }

            if (digits.length > 4) {
                formatted += `.${digits.slice(4, 8)}`;
            }

            input.value = formatted;
        });
    });

    // Маска российского мобильного телефона: +7 (___) ___-__-__.
    document.querySelectorAll('[data-phone-input]').forEach((input) => {
        input.addEventListener('input', () => {
            let digits = input.value.replace(/\D/g, '');

            if (!digits) {
                input.value = '';
                return;
            }

            if (digits.startsWith('8')) {
                digits = `7${digits.slice(1)}`;
            } else if (!digits.startsWith('7')) {
                digits = `7${digits}`;
            }

            digits = digits.slice(0, 11);

            let formatted = '+7';

            if (digits.length > 1) {
                formatted += ` (${digits.slice(1, 4)}`;
            }

            if (digits.length >= 4) {
                formatted += ')';
            }

            if (digits.length >= 5) {
                formatted += ` ${digits.slice(4, 7)}`;
            }

            if (digits.length >= 8) {
                formatted += `-${digits.slice(7, 9)}`;
            }

            if (digits.length >= 10) {
                formatted += `-${digits.slice(9, 11)}`;
            }

            input.value = formatted;
        });
    });
});
