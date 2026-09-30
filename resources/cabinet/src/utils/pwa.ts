/**
 * Событие beforeinstallprompt (Chrome/Edge/Opera — Android и десктоп) не входит в
 * стандартные типы lib.dom.d.ts, поэтому описываем минимальный интерфейс сами.
 */
export interface BeforeInstallPromptEvent extends Event {
    readonly platforms: string[];
    readonly userChoice: Promise<{ outcome: 'accepted' | 'dismissed'; platform: string }>;
    prompt(): Promise<void>;
}

/** Приложение уже установлено и открыто как отдельное окно (а не вкладка браузера). */
export function isRunningStandalone(): boolean {
    return (
        window.matchMedia('(display-mode: standalone)').matches ||
        // Легаси-флаг iOS Safari для PWA, добавленных на экран "Домой" — в display-mode он не отражается.
        (window.navigator as Navigator & { standalone?: boolean }).standalone === true
    );
}

/**
 * Регистрирует минимальный service worker (public/sw.js) — обязательное условие
 * "installability" для Chrome/Edge (без него beforeinstallprompt не сработает).
 */
export function registerServiceWorker(): void {
    if (!('serviceWorker' in navigator)) {
        return;
    }

    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch(() => {
            // Тихо игнорируем — установленное приложение продолжит работать как обычный
            // сайт, просто без подсказки об установке и офлайн-заглушки каркаса.
        });
    });
}

/**
 * На десктопе (Windows/Mac/Linux) установленное PWA-окно по умолчанию открывается в
 * произвольном размере (обычно % от экрана) — фиксируем 1024x800 сразу после запуска.
 * window.resizeTo() работает только для окон, которые браузер считает "приложением"
 * (display-mode: standalone/window-controls-overlay), в обычной вкладке браузера вызов
 * молча игнорируется — отдельная проверка на мобильные устройства не нужна.
 */
export function applyDesktopStandaloneWindowSize(): void {
    if (!isRunningStandalone()) {
        return;
    }

    // Хук пропускаем на мобильных — там окно и так на весь экран, resizeTo не применяется.
    const isTouchOnly = window.matchMedia('(pointer: coarse)').matches;
    if (isTouchOnly) {
        return;
    }

    window.resizeTo(1024, 800);
}
