import './bootstrap';

document.addEventListener('DOMContentLoaded', () => {
    // Плавное появление блоков при прокрутке.
    const revealables = document.querySelectorAll('[data-reveal]');
    if ('IntersectionObserver' in window && revealables.length) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.15, rootMargin: '0px 0px -40px 0px' });

        revealables.forEach((el) => observer.observe(el));
    } else {
        revealables.forEach((el) => el.classList.add('is-visible'));
    }

    // Шапка: тонкая подложка и уменьшенная высота после начала прокрутки.
    const header = document.querySelector('[data-site-header]');
    if (header) {
        const updateHeader = () => header.classList.toggle('is-scrolled', window.scrollY > 12);
        updateHeader();
        window.addEventListener('scroll', updateHeader, { passive: true });
    }

    // Мобильное меню.
    const menuToggle = document.querySelector('[data-menu-toggle]');
    const mobileMenu = document.querySelector('[data-mobile-menu]');
    if (menuToggle && mobileMenu) {
        menuToggle.addEventListener('click', () => {
            const isOpen = mobileMenu.classList.toggle('hidden') === false;
            menuToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });

        mobileMenu.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', () => {
                mobileMenu.classList.add('hidden');
                menuToggle.setAttribute('aria-expanded', 'false');
            });
        });
    }

    // Аккордеон вопросов и ответов.
    document.querySelectorAll('[data-faq-item]').forEach((item) => {
        const button = item.querySelector('[data-faq-button]');
        const answer = item.querySelector('.faq-answer');
        if (!button || !answer) {
            return;
        }

        button.addEventListener('click', () => {
            const isOpen = item.getAttribute('data-open') === 'true';

            document.querySelectorAll('[data-faq-item]').forEach((other) => {
                other.setAttribute('data-open', 'false');
                other.querySelector('[data-faq-button]')?.setAttribute('aria-expanded', 'false');
                const otherAnswer = other.querySelector('.faq-answer');
                if (otherAnswer) {
                    otherAnswer.style.maxHeight = null;
                }
            });

            if (!isOpen) {
                item.setAttribute('data-open', 'true');
                button.setAttribute('aria-expanded', 'true');
                answer.style.maxHeight = answer.scrollHeight + 'px';
            }
        });
    });
});
