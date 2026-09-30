/**
 * Событие beforeinstallprompt (Chrome/Edge/Opera — Android и десктоп) не входит в
 * стандартные типы lib.dom.d.ts, поэтому описываем минимальный интерфейс сами.
 */
export interface BeforeInstallPromptEvent extends Event {
    readonly platforms: string[];
    readonly userChoice: Promise<{ outcome: 'accepted' | 'dismissed'; platform: string }>;
    prompt(): Promise<void>;
}

/**
 * beforeinstallprompt одноразовое и браузер может выстрелить его почти сразу после загрузки страницы —
 * раньше он перехватывался внутри useEffect компонента PwaInstallPrompt, вложенного глубоко в
 * дерево (рендерится только после проверки сессии и монтирования всего DashboardLayout) — из-за этой
 * задержки событие могло 1 раз уйти, так как оно одноразовое и безвозвратно теряется без подписки.
 * Чтобы никогда его не пропустить, подписываемся на него здесь, на уровне модуля (вызывается из
 * main.tsx синхронно при старте приложения, до любой асинхронной работы Аута/роутера), а
 * PwaInstallPrompt при своём монтировании просто читает уже сохранённое значение, если оно уже пришло.
 */
let capturedInstallPrompt: BeforeInstallPromptEvent | null = null;
let installPromptListener: ((event: BeforeInstallPromptEvent) => void) | null = null;

/** Вызывается ровно один раз из main.tsx как можно раньше — до рендера React-дерева. */
export function initInstallPromptCapture(): void {
    window.addEventListener('beforeinstallprompt', (event) => {
        event.preventDefault();
        capturedInstallPrompt = event as BeforeInstallPromptEvent;
        installPromptListener?.(capturedInstallPrompt);
    });
}

/**
 * Подписывает компонент на появление события; если оно уже было поймано до монтирования
 * (обычный случай) — вызывает колбэк сразу же с уже сохранённым значением.
 */
export function onInstallPromptCaptured(callback: (event: BeforeInstallPromptEvent) => void): () => void {
    installPromptListener = callback;
    if (capturedInstallPrompt) {
        callback(capturedInstallPrompt);
    }
    return () => {
        installPromptListener = null;
    };
}

/** Сбрасывает сохранённое событие после того как оно использовано (prompt() одноразов). */
export function clearCapturedInstallPrompt(): void {
    capturedInstallPrompt = null;
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
 * произвольном размере (обычно % от экрана) — фиксируем 770x850 сразу после запуска.
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

    window.resizeTo(770, 850);
}
