import { XMarkIcon } from '@heroicons/react/24/outline';
import { useEffect, useState, type ReactNode, type RefObject } from 'react';
import type { AppNotification } from '../../api/types';
import { groupByDate } from '../../utils/dateGroups';
import { NotificationsSkeleton } from '../common/Skeleton';
import { NotificationRow } from './NotificationRow';

interface NotificationsPanelProps {
    /** desktop — выпадающая панель под кнопкой (в шапке); mobile — нижний лист
     * (bottom sheet), визуально и по анимации идентичный Modal.tsx/TopupModal. */
    mode: 'desktop' | 'mobile';
    open: boolean;
    loading: boolean;
    items: AppNotification[];
    onClose: () => void;
    panelRef: RefObject<HTMLDivElement | null>;
}

function NotificationsList({ loading, items, onClose }: Pick<NotificationsPanelProps, 'loading' | 'items' | 'onClose'>) {
    if (loading) {
        return <NotificationsSkeleton />;
    }

    if (items.length === 0) {
        return <p className="notif-empty">Уведомлений пока нет.</p>;
    }

    // Группировка по дате создания — та же логика и визуал заголовка (чёрточка-разделитель),
    // что и в списке операций (TransactionsTable, .tx-group-title в index.css).
    return (
        <>
            {groupByDate(items, (item) => item.created_at).map((group) => (
                <div key={group.title} className="notif-group">
                    <div className="tx-group-title">{group.title}</div>
                    {group.items.map((item) => (
                        <NotificationRow key={item.id} item={item} onNavigate={onClose} />
                    ))}
                </div>
            ))}
        </>
    );
}

/**
 * Попап «Уведомления» — рендерится ДВАЖДЫ с общим состоянием из useNotifications:
 * mode="desktop" — выпадающий дропдаун под кнопкой в шапке (абсолютно спозиционирован
 * от неё, см. .notif-panel-desktop в index.css); mode="mobile" — нижний лист (bottom
 * sheet), выезжающий снизу вверх ТОЧНО так же, как модалка пополнения (переиспользует
 * те же классы .modal-overlay/.modal-sheet-*, что и Modal.tsx/TopupModal, — не общий
 * компонент, а те же CSS-классы, чтобы получить идентичный визуал и тайминг анимации
 * без риска рассинхронизировать их при будущих правках одного из двух мест).
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
            // Двойной rAF (вместо одиночного, как в Modal.tsx) — надёжно именно здесь, потому что в
            // отличие от TopupModal (где <Modal> монтируется/размонтируется целиком через условный
            // рендер родителя), этот компонент постоянный — открытие это просто переключение
            // его собственного состояния, без дорогостоящего монтирования нового DOM-узла —
            // оба обновления (mounted=true и затем visible=true) могут успеть схлопнуться в один
            // кадр отрисовки, если ждать только один rAF — тогда браузер никогда не отрисует
            // промежуточное «закрытое» положение, и transition не сыграет — попап появляется резко.
            let raf2 = 0;
            const raf1 = requestAnimationFrame(() => {
                raf2 = requestAnimationFrame(() => setVisible(true));
            });
            return () => {
                cancelAnimationFrame(raf1);
                cancelAnimationFrame(raf2);
            };
        }

        setVisible(false);
        const timeout = setTimeout(() => setMounted(false), mode === 'mobile' ? 320 : 260);
        return () => clearTimeout(timeout);
    }, [open, mode]);

    if (!mounted) {
        return null;
    }

    const list: ReactNode = <NotificationsList loading={loading} items={items} onClose={onClose} />;

    if (mode === 'mobile') {
        return (
            <div
                className={`modal-overlay notif-mobile-overlay${visible ? ' is-visible' : ''}`}
                onClick={onClose}
            >
                <div
                    ref={panelRef}
                    className={`modal-sheet${visible ? ' is-visible' : ''}`}
                    role="dialog"
                    aria-modal="true"
                    aria-label="Уведомления"
                    onClick={(event) => event.stopPropagation()}
                >
                    <div className="modal-sheet-handle" />
                    <div className="modal-sheet-header">
                        <h2 className="text-lg font-extrabold tracking-tight text-ink">Уведомления</h2>
                        <button type="button" className="icon-btn" onClick={onClose} aria-label="Закрыть">
                            <XMarkIcon className="h-4 w-4" />
                        </button>
                    </div>
                    <div className="modal-sheet-body no-scrollbar">{list}</div>
                </div>
            </div>
        );
    }

    return (
        <div
            ref={panelRef}
            className={`notif-panel notif-panel-desktop${visible ? ' is-visible' : ''}`}
            role="dialog"
            aria-label="Уведомления"
        >
            <div className="notif-panel-header">
                <h3 className="notif-panel-title">Уведомления</h3>
            </div>
            <div className="notif-panel-body no-scrollbar">{list}</div>
        </div>
    );
}
