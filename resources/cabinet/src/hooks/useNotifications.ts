import { useCallback, useEffect, useRef, useState } from 'react';
import { fetchNotifications, markNotificationsRead } from '../api/notifications';
import type { AppNotification } from '../api/types';

const POLL_INTERVAL_MS = 20_000;

/**
 * Состояние попапа «Уведомления» в шапке ЛК (DashboardLayout) — один вызов на
 * весь лейаут, кнопка и обе версии попапа (десктоп/мобильная, см.
 * components/notifications/NotificationsPanel.tsx) читают общее состояние.
 *
 * Список опрашивается раз в POLL_INTERVAL_MS фоном, независимо от того, открыт
 * ли попап — так бейдж непрочитанных в шапке обновляется в реальном времени.
 */
export function useNotifications() {
    const [items, setItems] = useState<AppNotification[]>([]);
    const [unreadCount, setUnreadCount] = useState(0);
    const [loading, setLoading] = useState(true);
    const [open, setOpen] = useState(false);

    const triggerRef = useRef<HTMLButtonElement>(null);
    const desktopPanelRef = useRef<HTMLDivElement>(null);
    const mobilePanelRef = useRef<HTMLDivElement>(null);

    const refresh = useCallback(async () => {
        try {
            const { data, meta } = await fetchNotifications();
            setItems(data);
            setUnreadCount(meta.unread_count ?? 0);
        } catch {
            // Сетевой сбой фонового опроса не должен показывать пользователю ошибку —
            // просто попробуем ещё раз на следующем тике.
        } finally {
            setLoading(false);
        }
    }, []);

    useEffect(() => {
        refresh();
        const timer = window.setInterval(refresh, POLL_INTERVAL_MS);
        return () => window.clearInterval(timer);
    }, [refresh]);

    // Открытие попапа — все непрочитанные сразу считаются прочитанными (и локально,
    // для мгновенного скрытия бейджа, и на бэкенде, чтобы это пережило следующий опрос).
    const openPopover = useCallback(() => {
        setOpen(true);
        setUnreadCount(0);
        setItems((prev) => prev.map((item) => (item.is_read ? item : { ...item, is_read: true })));
        markNotificationsRead().catch(() => {});
    }, []);

    const closePopover = useCallback(() => setOpen(false), []);

    const toggle = useCallback(() => {
        if (open) {
            closePopover();
        } else {
            openPopover();
        }
    }, [open, openPopover, closePopover]);

    // Клик/тап вне кнопки и обеих панелей — закрыть попап (десктопное поведение
    // дропдауна); Escape — то же самое на любом экране.
    useEffect(() => {
        if (!open) {
            return;
        }

        function handlePointerDown(event: PointerEvent) {
            const target = event.target as Node;
            const inside =
                triggerRef.current?.contains(target) ||
                desktopPanelRef.current?.contains(target) ||
                mobilePanelRef.current?.contains(target);

            if (!inside) {
                closePopover();
            }
        }

        function handleKeyDown(event: KeyboardEvent) {
            if (event.key === 'Escape') {
                closePopover();
            }
        }

        document.addEventListener('pointerdown', handlePointerDown);
        document.addEventListener('keydown', handleKeyDown);
        return () => {
            document.removeEventListener('pointerdown', handlePointerDown);
            document.removeEventListener('keydown', handleKeyDown);
        };
    }, [open, closePopover]);

    return {
        items,
        unreadCount,
        loading,
        open,
        toggle,
        close: closePopover,
        triggerRef,
        desktopPanelRef,
        mobilePanelRef,
    };
}
