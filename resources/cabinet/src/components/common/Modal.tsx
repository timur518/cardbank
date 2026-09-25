import { XMarkIcon } from '@heroicons/react/24/outline';
import { useEffect, useState, type ReactNode } from 'react';

interface ModalProps {
    title: string;
    onClose: () => void;
    children: ReactNode;
    /** 'lg' — шире и без внутренних отступов у body (встраиваемый iframe, см. KycVerificationModal). */
    size?: 'default' | 'lg';
}

/**
 * Универсальное модальное окно приложения (сейчас используется в TopupModal).
 * На мобильных экранах (< 640px) выезжает снизу вверх как нижний лист (bottom
 * sheet) с ручкой-индикатором — привычный паттерн нативных приложений; на
 * широких экранах остаётся обычным центрированным диалогом. Анимации открытия
 * и закрытия — через класс is-visible (см. .modal-* в index.css), закрытие
 * откладывается на время transition, чтобы лист/диалог успел доехать до края.
 */
export function Modal({ title, onClose, children, size = 'default' }: ModalProps) {
    const [visible, setVisible] = useState(false);

    useEffect(() => {
        const raf = requestAnimationFrame(() => setVisible(true));
        return () => cancelAnimationFrame(raf);
    }, []);

    function handleClose() {
        setVisible(false);
        setTimeout(onClose, 280);
    }

    return (
        <div className={`modal-overlay${visible ? ' is-visible' : ''}`} onClick={handleClose}>
            <div
                className={`modal-sheet${visible ? ' is-visible' : ''}${size === 'lg' ? ' modal-sheet-lg' : ''}`}
                role="dialog"
                aria-modal="true"
                onClick={(event) => event.stopPropagation()}
            >
                <div className="modal-sheet-handle" />
                <div className="modal-sheet-header">
                    <h2 className="text-lg font-extrabold tracking-tight text-ink">{title}</h2>
                    <button type="button" className="icon-btn" onClick={handleClose} aria-label="Закрыть">
                        <XMarkIcon className="h-4 w-4" />
                    </button>
                </div>
                <div className={`modal-sheet-body${size === 'lg' ? ' modal-sheet-body-flush' : ''}`}>{children}</div>
            </div>
        </div>
    );
}
