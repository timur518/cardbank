import { CheckCircleIcon } from '@heroicons/react/24/outline';
import { useEffect, useRef, useState } from 'react';
import { acceptBitbankerOffer } from '../../api/bitbanker';
import { extractErrorMessage } from '../../api/client';
import { Modal } from '../common/Modal';

interface BitbankerOfferModalProps {
    offerText: string;
    onClose: () => void;
    /** Оферта успешно принята (сам факт регистрации, независимо от is_verified_for_sbp —
     * доступность оплаты определяется отдельно состояниями 3/3a/4 плитки BitBanker) —
     * колбэк наверх, чтобы PaymentMethodsList перезапросил способы оплаты и статус BitBanker.
     * Вызывается и по авто-закрытию через 10 секунд, и по ручному закрытию попапа после успеха. */
    onAccepted: () => void;
}

type ModalState = 'idle' | 'loading' | 'success';

const SUCCESS_AUTOCLOSE_MS = 10_000;

/**
 * Попап принятия оферты BitBanker перед первой регистрацией — открывается из
 * PaymentMethodsList/BitbankerTile, когда пользователь прошёл KYC, но ещё не принял оферту.
 */
export function BitbankerOfferModal({ offerText, onClose, onAccepted }: BitbankerOfferModalProps) {
    const [agreed, setAgreed] = useState(false);
    const [state, setState] = useState<ModalState>('idle');
    const [error, setError] = useState<string | null>(null);

    const onAcceptedRef = useRef(onAccepted);
    onAcceptedRef.current = onAccepted;

    useEffect(() => {
        if (state !== 'success') {
            return;
        }

        const timer = window.setTimeout(() => onAcceptedRef.current(), SUCCESS_AUTOCLOSE_MS);
        return () => window.clearTimeout(timer);
    }, [state]);

    async function handleAccept() {
        setError(null);
        setState('loading');

        try {
            await acceptBitbankerOffer();
            setState('success');
        } catch (acceptError) {
            setError(extractErrorMessage(acceptError, 'Не удалось зарегистрировать вас в BitBanker — обратитесь в поддержку.'));
            setState('idle');
        }
    }

    return (
        <Modal title="Оферта BitBanker" onClose={state === 'success' ? onAccepted : onClose}>
            {state === 'success' ? (
                <div className="flex flex-col items-center gap-3 py-6 text-center">
                    <CheckCircleIcon className="h-12 w-12 text-[#2e9c5d]" />
                    <p className="text-sm font-semibold text-ink">Теперь вам доступен более выгодный способ пополнения карты!</p>
                </div>
            ) : (
                <>
                    <div className="bitbanker-offer-text rich-text" dangerouslySetInnerHTML={{ __html: offerText }} />

                    <label className="bitbanker-offer-agree">
                        <input type="checkbox" checked={agreed} onChange={(event) => setAgreed(event.target.checked)} />
                        <span>Я принимаю условия оферты BitBanker</span>
                    </label>

                    {error && <p className="form-error-banner mt-3">{error}</p>}

                    <button
                        type="button"
                        className="btn btn-primary apply-submit"
                        onClick={handleAccept}
                        disabled={!agreed || state === 'loading'}
                    >
                        {state === 'loading' ? 'Регистрируем…' : 'Принять'}
                    </button>
                </>
            )}
        </Modal>
    );
}
