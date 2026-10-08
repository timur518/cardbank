import { CheckCircleIcon } from '@heroicons/react/24/outline';
import { useEffect, useRef, useState } from 'react';
import { fetchCard } from '../../api/cards';
import type { CardDetail } from '../../api/types';
import { Modal } from '../common/Modal';

const POLL_INTERVAL_MS = 5_000;
// Чисто фронтовый обратный отсчёт — BitBanker не присылает срок истечения отдельно
// фронту (dt_expiration есть только внутри вебхука), час — ориентир для пользователя.
const COUNTDOWN_MS = 60 * 60 * 1000;

interface BitbankerQrPaymentModalProps {
    qrCode: string;
    fallbackUrl: string | null;
    cardId: string;
    mode: 'issue' | 'topup';
    /** Баланс карты до оплаты — обязателен для mode='topup', где признак оплаты —
     * изменение баланса (у выпуска карты признак другой — pending_payment становится null). */
    initialBalance?: string;
    onPaid: (card: CardDetail) => void;
    onClose: () => void;
}

function formatCountdown(msLeft: number): string {
    const totalSeconds = Math.max(0, Math.floor(msLeft / 1000));
    const minutes = Math.floor(totalSeconds / 60);
    const seconds = totalSeconds % 60;

    return `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
}

/**
 * Экран оплаты по QR для BitBanker (вместо редиректа на payment_url, как у ParityPay) —
 * показывается после «Оплатить»/«Оплатить и выпустить», если бэкенд вернул qr_code.
 * Опрашивает GET /v1/cards/{uuid},
 * пока не увидит признак оплаты, затем коротко показывает успех и передаёт
 * актуальную карту наверх (onPaid) — переход на страницу карты/закрытие решает вызывающий код.
 */
export function BitbankerQrPaymentModal({ qrCode, fallbackUrl, cardId, mode, initialBalance, onPaid, onClose }: BitbankerQrPaymentModalProps) {
    const deadlineRef = useRef(Date.now() + COUNTDOWN_MS);
    const [msLeft, setMsLeft] = useState(COUNTDOWN_MS);
    const [paidCard, setPaidCard] = useState<CardDetail | null>(null);

    const onPaidRef = useRef(onPaid);
    onPaidRef.current = onPaid;

    useEffect(() => {
        const timer = window.setInterval(() => setMsLeft(Math.max(0, deadlineRef.current - Date.now())), 1000);
        return () => window.clearInterval(timer);
    }, []);

    useEffect(() => {
        let stopped = false;

        function isPaid(card: CardDetail): boolean {
            return mode === 'issue' ? card.pending_payment === null : initialBalance !== undefined && card.balance !== initialBalance;
        }

        async function poll() {
            try {
                const card = await fetchCard(cardId);
                if (!stopped && isPaid(card)) {
                    stopped = true;
                    window.clearInterval(timer);
                    setPaidCard(card);
                }
            } catch {
                // Сетевой сбой фонового опроса — попробуем ещё раз на следующем тике.
            }
        }

        const timer = window.setInterval(poll, POLL_INTERVAL_MS);
        void poll();

        return () => {
            stopped = true;
            window.clearInterval(timer);
        };
    }, [cardId, mode, initialBalance]);

    // Короткая пауза на экране успеха перед тем, как передать управление наверх.
    useEffect(() => {
        if (!paidCard) {
            return;
        }

        const timer = window.setTimeout(() => onPaidRef.current(paidCard), 2000);
        return () => window.clearTimeout(timer);
    }, [paidCard]);

    return (
        <Modal title="Оплата по СБП" onClose={onClose}>
            {paidCard ? (
                <div className="flex flex-col items-center gap-3 py-6 text-center">
                    <CheckCircleIcon className="h-12 w-12 text-[#2e9c5d]" />
                    <p className="text-sm font-semibold text-ink">Оплата получена!</p>
                </div>
            ) : (
                <div className="bitbanker-qr">
                    <img src={`data:image/png;base64,${qrCode}`} alt="QR-код для оплаты по СБП" className="bitbanker-qr-image" />
                    <p className="bitbanker-qr-timer">Оплатите в течение {formatCountdown(msLeft)}</p>
                    {fallbackUrl && (
                        <a href={fallbackUrl} target="_blank" rel="noreferrer" className="btn btn-block">
                            Открыть в приложении банка
                        </a>
                    )}
                </div>
            )}
        </Modal>
    );
}
