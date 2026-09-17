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
});
