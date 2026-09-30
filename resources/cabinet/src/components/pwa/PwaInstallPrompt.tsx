import { ArrowDownTrayIcon, XMarkIcon } from '@heroicons/react/24/outline';
import { useEffect, useState } from 'react';
import { clearCapturedInstallPrompt, isRunningStandalone, onInstallPromptCaptured, type BeforeInstallPromptEvent } from '../../utils/pwa';

// Сколько дней не показывать попап повторно после того, как пользователь его закрыл
// (не путать с установкой — после успешной установки приложение просто уходит в standalone-режим
// и isRunningStandalone() навсегда отключает показ).
const DISMISS_COOLDOWN_DAYS = 14;
const DISMISS_STORAGE_KEY = 'mojno_pwa_install_dismissed_at';
// Небольшая задержка перед показом — чтобы попап не перекрывал первый экран сразу после логина/загрузки.
const SHOW_DELAY_MS = 2500;

type Platform = 'prompt' | 'ios' | 'mac-safari';

function wasRecentlyDismissed(): boolean {
    const raw = localStorage.getItem(DISMISS_STORAGE_KEY);
    if (!raw) {
        return false;
    }
    const dismissedAt = Number(raw);
    return Number.isFinite(dismissedAt) && Date.now() - dismissedAt < DISMISS_COOLDOWN_DAYS * 24 * 60 * 60 * 1000;
}

function markDismissed(): void {
    localStorage.setItem(DISMISS_STORAGE_KEY, String(Date.now()));
}

/**
 * Попап-предложение установить ЛК как приложение («Можно») — на рабочий стол на десктопе
 * (Windows/Mac/Linux) и на экран "Домой" на телефоне. Два сценария:
 *
 * 1. Chrome/Edge/Opera (Android и десктоп) поддерживают событие beforeinstallprompt —
 *    браузер сам решает, когда предложение доступно (манифест + service worker +
 *    минимальный "engagement"), мы только перехватываем стандартный мини-инфобар и
 *    показываем свой попап с кнопкой, которая дёргает per-браузерный prompt().
 * 2. iOS Safari и macOS Safari (Sonoma+) этот API не реализуют вообще — показываем
 *    текстовую инструкцию (кнопка "Поделиться" → "На экран Домой" / "Добавить в Dock"),
 *    установка там только ручная.
 *
 * Firefox и прочие браузеры без поддержки установки — попап не показывается вообще.
 */
export function PwaInstallPrompt() {
    const [deferredPrompt, setDeferredPrompt] = useState<BeforeInstallPromptEvent | null>(null);
    const [platform, setPlatform] = useState<Platform | null>(null);
    const [visible, setVisible] = useState(false);

    useEffect(() => {
        if (isRunningStandalone() || wasRecentlyDismissed()) {
            return;
        }

        let showTimer: ReturnType<typeof setTimeout> | undefined;

        // Событие уже могло быть перехвачено раньше (см. main.tsx/initInstallPromptCapture) — через
        // модульный синглтон, а не прямой addEventListener здесь, чтобы не пропустить его из-за
        // задержки монтирования DashboardLayout (проверка сессии, прелоадер).
        const unsubscribe = onInstallPromptCaptured((event) => {
            setDeferredPrompt(event);
            setPlatform('prompt');
            showTimer = setTimeout(() => setVisible(true), SHOW_DELAY_MS);
        });

        // iOS/macOS Safari: beforeinstallprompt никогда не придёт — определяем по UA сразу.
        const ua = window.navigator.userAgent;
        const isIos = /iphone|ipad|ipod/i.test(ua);
        const isMacSafari = /macintosh/i.test(ua) && /safari/i.test(ua) && !/chrome|crios|edgios|firefox/i.test(ua);
        if (isIos || isMacSafari) {
            setPlatform(isIos ? 'ios' : 'mac-safari');
            showTimer = setTimeout(() => setVisible(true), SHOW_DELAY_MS);
        }

        return () => {
            unsubscribe();
            if (showTimer) {
                clearTimeout(showTimer);
            }
        };
    }, []);

    function dismiss() {
        markDismissed();
        setVisible(false);
    }

    async function handleInstall() {
        if (!deferredPrompt) {
            return;
        }

        await deferredPrompt.prompt();
        const { outcome } = await deferredPrompt.userChoice;
        // Событие одноразовое — очищаем модульный кэш сразу после prompt(). И при согласии, и при
        // отказе больше не показываем — отказ трактуем как "не сейчас", а не "спрашивать на каждой загрузке".
        clearCapturedInstallPrompt();
        markDismissed();
        setDeferredPrompt(null);
        setVisible(false);
        void outcome;
    }

    if (!visible || !platform) {
        return null;
    }

    return (
        <div className="pwa-install-toast" role="dialog" aria-label="Установить приложение">
            <img src="/icons/icon-192.png" alt="" className="pwa-install-icon" />
            <div className="pwa-install-copy">
                <p className="pwa-install-title">Установите «Можно»</p>
                <p className="pwa-install-text">
                    {platform === 'ios' && 'Нажмите «Поделиться» внизу экрана и выберите «На экран «Домой»».'}
                    {platform === 'mac-safari' && 'Нажмите «Поделиться» в Safari и выберите «Добавить в Dock».'}
                    {platform === 'prompt' && 'Быстрый доступ без браузера и адресной строки — как обычное приложение.'}
                </p>
            </div>
            <div className="pwa-install-actions">
                {platform === 'prompt' && (
                    <button type="button" className="btn btn-primary" onClick={handleInstall}>
                        <ArrowDownTrayIcon className="h-4 w-4" />
                        {/* На мобилке подпись скрыта (см. .pwa-install-btn-label) — остаётся только иконка,
                            чтобы логотип, текст, кнопка и закрытие вмещались в одну строку. */}
                        <span className="pwa-install-btn-label">Установить</span>
                    </button>
                )}
                <button type="button" className="pwa-install-close" onClick={dismiss} aria-label="Закрыть">
                    <XMarkIcon className="h-4 w-4" />
                </button>
            </div>
        </div>
    );
}
