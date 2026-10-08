import { useCallback, useEffect, useState } from 'react';
import { fetchBitbankerStatus } from '../../api/bitbanker';
import { extractErrorMessage } from '../../api/client';
import { startKycVerification } from '../../api/kyc';
import type { AppKycStatus, BitbankerStatus, PaymentMethod } from '../../api/types';
import { useAuth } from '../../context/AuthContext';
import { KycVerificationModal } from '../kyc/KycVerificationModal';
import { BitbankerOfferModal } from './BitbankerOfferModal';
import { BITBANKER_BADGE, BitbankerTile, type BitbankerTileState } from './BitbankerTile';
import { PaymentMethodOption } from './PaymentMethodOption';

const BITBANKER_GATEWAY_CODE = 'bitbanker';

interface PaymentMethodsListProps {
    methods: PaymentMethod[];
    selectedMethodId: number | null;
    onSelect: (id: number) => void;
    /** Перезапросить GET /v1/payment-methods — вызывается после успешного принятия
     * оферты BitBanker, чтобы способ сразу попал в список, если проверки уже пройдены. */
    onMethodsRefresh: () => void;
    className?: string;
}

/** Выбирает состояние плитки BitBanker (1-3a) по kyc_status/offer_accepted/check_status —
 * вызывается, только пока BitBanker ещё не присутствует среди methods — как только
 * он становится доступен, его отображает уже обычная ветка списка methods. */
function resolveBitbankerTileState(kycStatus: AppKycStatus, status: BitbankerStatus): BitbankerTileState {
    if (kycStatus !== 'approved') {
        return 1;
    }

    if (!status.offer_accepted) {
        return 2;
    }

    if (status.check_status === 'completed') {
        // is_verified_for_sbp === true здесь на практике не встречается — в этом случае BitBanker уже
        // попадает в methods через allowedPaymentMethods, и эта ветка вообще не вызывается
        // (см. комментарий к функции выше). Показываем «на рассмотрении» как безопасный
        // фолбэк на случай рассинхронизации.
        return status.is_verified_for_sbp ? 3 : '3a';
    }

    return 3;
}

/**
 * Общий список способов оплаты для шага оплаты при выпуске карты (NewCardOrderPage) и
 * пополнении (TopupModal) — заменяет инлайновый `methods.map(...)`. BitBanker всегда первый:
 * обычная выбираемая плитка, если он уже есть в methods (gateway_code === 'bitbanker'), иначе —
 * некликабельная призывная плитка BitbankerTile с нужным CTA (пройти верификацию/
 * принять оферту/ожидание).
 */
export function PaymentMethodsList({ methods, selectedMethodId, onSelect, onMethodsRefresh, className }: PaymentMethodsListProps) {
    const { profile, refreshProfile } = useAuth();

    const [bitbankerStatus, setBitbankerStatus] = useState<BitbankerStatus | null>(null);
    const [kycModalUrl, setKycModalUrl] = useState<string | null>(null);
    const [isStartingKyc, setIsStartingKyc] = useState(false);
    const [kycError, setKycError] = useState<string | null>(null);
    const [isOfferModalOpen, setIsOfferModalOpen] = useState(false);

    const refreshBitbankerStatus = useCallback(() => {
        fetchBitbankerStatus()
            .then(setBitbankerStatus)
            .catch(() => {
                // Тихий сбой — BitBanker просто не появится первым элементом списка.
            });
    }, []);

    useEffect(() => {
        refreshBitbankerStatus();
    }, [refreshBitbankerStatus]);

    const bitbankerMethod = methods.find((method) => method.gateway_code === BITBANKER_GATEWAY_CODE) ?? null;
    const otherMethods = methods.filter((method) => method.gateway_code !== BITBANKER_GATEWAY_CODE);
    const kycStatus = (profile?.kyc_status ?? 'not_started') as AppKycStatus;

    async function handleStartKyc() {
        setKycError(null);
        setIsStartingKyc(true);

        try {
            const session = await startKycVerification();
            setKycModalUrl(session.url);
        } catch (error) {
            setKycError(extractErrorMessage(error, 'Не удалось запустить верификацию. Попробуйте ещё раз.'));
        } finally {
            setIsStartingKyc(false);
        }
    }

    const handleKycCompleted = useCallback(() => {
        setKycModalUrl(null);
        void refreshProfile();
        window.setTimeout(() => void refreshProfile(), 4000);
    }, [refreshProfile]);

    function handleOfferAccepted() {
        setIsOfferModalOpen(false);
        onMethodsRefresh();
        refreshBitbankerStatus();
    }

    return (
        <div className={`apply-pay-list ${className ?? ''}`}>
            {bitbankerMethod ? (
                <PaymentMethodOption
                    method={bitbankerMethod}
                    selected={bitbankerMethod.id === selectedMethodId}
                    onSelect={() => onSelect(bitbankerMethod.id)}
                    badge={BITBANKER_BADGE}
                />
            ) : (
                // bitbankerStatus.method_name === null значит «в админке нет активной записи BitBanker»
                // (отключён в «Способах оплаты») — без этой проверки призывная плитка
                // BitbankerTile показывалась бы даже при отключённом способе оплаты (methods его
                // уже не содержит, но GET /v1/bitbanker/status по-прежнему отдаёт 200 с остальными полями).
                bitbankerStatus?.method_name && (
                    <BitbankerTile
                        state={resolveBitbankerTileState(kycStatus, bitbankerStatus)}
                        methodName={bitbankerStatus.method_name}
                        isActionLoading={isStartingKyc}
                        onAction={() => {
                            if (kycStatus !== 'approved') {
                                void handleStartKyc();
                            } else {
                                setIsOfferModalOpen(true);
                            }
                        }}
                    />
                )
            )}

            {otherMethods.map((method) => (
                <PaymentMethodOption
                    key={method.id}
                    method={method}
                    selected={method.id === selectedMethodId}
                    onSelect={() => onSelect(method.id)}
                />
            ))}

            {kycError && <p className="form-error-banner mt-2">{kycError}</p>}

            {kycModalUrl && (
                <KycVerificationModal url={kycModalUrl} onClose={() => setKycModalUrl(null)} onCompleted={handleKycCompleted} />
            )}

            {isOfferModalOpen && bitbankerStatus && (
                <BitbankerOfferModal
                    offerText={bitbankerStatus.offer_text}
                    onClose={() => setIsOfferModalOpen(false)}
                    onAccepted={handleOfferAccepted}
                />
            )}
        </div>
    );
}
