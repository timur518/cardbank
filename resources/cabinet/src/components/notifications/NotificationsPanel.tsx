import { XMarkIcon } from '@heroicons/react/24/outline';
import { useEffect, useState, type RefObject } from 'react';
import type { AppNotification } from '../../api/types';
import { groupByDate } from '../../utils/dateGroups';
import { NotificationsSkeleton } from '../common/Skeleton';
import { NotificationRow } from './NotificationRow';

interface NotificationsPanelProps {
    /** desktop — выпадающая панель под кнопкой (в шапке); mobile — полноэкранный лист. */
    mode: 'desktop' | 'mobile';
    open: boolean;
    loading: boolean;
    items: AppNotification[];
    onClose: () => void;
    panelRef: RefObject<HTMLDivElement | null>;
}

/**
 * Попап «Уведомления» — рендерится ДВАЖДЫ (mode="desktop" внутри шапки, mode="mobile"
 * отдельным элементом в DashboardLayout, см. .notif-panel-desktop/.notif-panel-mobile
 * в index.css) вместо одного элемента на медиа-запросах: десктопной версии нужно
 * абсолютное позиционирование от кнопки (натурально, без ручных расчётов координат),
 * а мобильной — z-index НИЖЕ шапки и нижнего меню, что требует отдельного узла на
 * уровне DashboardLayout, а не вложенного в шапку (иначе шапка красилась бы под
 * полноэкранным попапом, т.к. дочерний элемент всегда поверх фона родителя).
 *
 * Анимация открытия/закрытия — тот же приём, что в Modal.tsx: класс is-visible
 * навешивается через requestAnimationFrame после монтирования, размонтирование
 * при закрытии откладывается на время transition.
 */
export function NotificationsPanel({ mode, open, loading, items, onClose, panelRef }: NotificationsPanelProps) {
    const [mounted, setMounted] = useState(open);
    const [visible, setVisible] = useState(false);

    useEffect(() => {
        if (open) {
            setMounted(true);
            const raf = requestAnimationFrame(() => setVisible(true));
            return () => cancelAnimationFrame(raf);
        }

        setVisible(false);
        const timeout = setTimeout(() => setMounted(false), 260);
        return () => clearTimeout(timeout);
    }, [open]);

    if (!mounted) {
        return null;
    }

    return (
        <div
            ref={panelRef}
            className={`notif-panel notif-panel-${mode}${visible ? ' is-visible' : ''}`}
            role="dialog"
            aria-modal={mode === 'mobile' ? true : undefined}
            aria-label="Уведомления"
        >
            <div className="notif-panel-header">
                <h3 className="notif-panel-title">Уведомления</h3>
                {mode === 'mobile' && (
                    <button type="button" className="icon-btn" onClick={onClose} aria-label="Закрыть">
                        <XMarkIcon className="h-4 w-4" />
                    </button>
                )}
            </div>
            <div className="notif-panel-body no-scrollbar">
                {loading ? (
                    <NotificationsSkeleton />
                ) : items.length === 0 ? (
                    <p className="notif-empty">Уведомлений пока нет.</p>
                ) : (
                    // Группировка по дате создания — та же логика и визуал заголовка (чёрточка-разделитель),
                    // что и в списке операций (TransactionsTable, .tx-group-title в index.css).
                    groupByDate(items, (item) => item.created_at).map((group) => (
                        <div key={group.title} className="notif-group">
                            <div className="tx-group-title">{group.title}</div>
                            {group.items.map((item) => (
                                <NotificationRow key={item.id} item={item} onNavigate={onClose} />
                            ))}
                        </div>
                    ))
                )}
            </div>
        </div>
    );
}
