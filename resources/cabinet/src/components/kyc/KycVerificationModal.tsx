import { useEffect } from 'react';
import { Modal } from '../common/Modal';

// Домен, с которого Didit шлёт postMessage о завершении сессии внутри iframe —
// см. https://docs.didit.me/integration/web-sdks/incontext-iframe#listening-for-events-postmessage.
// Если в консоли Didit подключён свой white-label домен, сюда нужно будет добавить и его.
const DIDIT_MESSAGE_ORIGIN = 'https://verify.didit.me';

interface KycVerificationModalProps {
    url: string;
    onClose: () => void;
    /** Пришло сообщение didit:completed из iframe — сама сессия могла завершиться
     * с любым статусом (Approved/Declined/...), фактический результат синхронизирует
     * вебхук (см. DiditWebhookHandler), здесь только повод обновить профиль. */
    onCompleted: () => void;
}

/**
 * Классический стилизованный попап с встроенным iframe верификации Didit — переиспользует
 * общий компонент Modal (тот же блюр/оверлей, что и у TopupModal), но в увеличенном
 * варианте (size="lg") без внутренних отступов у тела, чтобы iframe занимал всё окно.
 */
export function KycVerificationModal({ url, onClose, onCompleted }: KycVerificationModalProps) {
    useEffect(() => {
        function handleMessage(event: MessageEvent) {
            if (event.origin !== DIDIT_MESSAGE_ORIGIN) {
                return;
            }

            if (event.data?.type === 'didit:completed') {
                onCompleted();
            }
        }

        window.addEventListener('message', handleMessage);
        return () => window.removeEventListener('message', handleMessage);
    }, [onCompleted]);

    return (
        <Modal title="Верификация личности" onClose={onClose} size="lg">
            <iframe
                src={url}
                title="Didit Verification"
                allow="camera; microphone; fullscreen; autoplay; encrypted-media"
                className="kyc-iframe"
            />
        </Modal>
    );
}
