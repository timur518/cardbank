import { useEffect, useState } from 'react';
import { useAuth } from '../../context/AuthContext';
import { isRunningStandalone } from '../../utils/pwa';
import { PinSetupModal } from './PinSetupModal';

// Сколько дней не предлагать установку ПИН-кода повторно после закрытия попапа без установки.
const DISMISS_COOLDOWN_DAYS = 14;
const DISMISS_STORAGE_KEY = 'mojno_pin_setup_dismissed_at';
// Задержка перед показом — не перекрывать первый экран сразу после открытия приложения.
const SHOW_DELAY_MS = 2500;

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
 * Ненавязчивое предложение установить ПИН-код — только для тех, кто уже установил ЛК как
 * PWA (isRunningStandalone()) и ещё не завёл ПИН (profile.has_pin === false). Показывается
 * с задержкой после открытия приложения, не чаще раза в DISMISS_COOLDOWN_DAYS дней после закрытия.
 */
export function PinSetupPrompt() {
    const { profile } = useAuth();
    const [visible, setVisible] = useState(false);

    useEffect(() => {
        if (!profile || profile.has_pin || !isRunningStandalone() || wasRecentlyDismissed()) {
            return;
        }

        const timer = window.setTimeout(() => setVisible(true), SHOW_DELAY_MS);
        return () => window.clearTimeout(timer);
    }, [profile]);

    if (!visible) {
        return null;
    }

    function handleClose() {
        markDismissed();
        setVisible(false);
    }

    return <PinSetupModal mode="create" onClose={handleClose} />;
}
