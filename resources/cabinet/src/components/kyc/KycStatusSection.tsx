import { IdentificationIcon } from '@heroicons/react/24/outline';
import { useCallback, useState } from 'react';
import { startKycVerification } from '../../api/kyc';
import type { AppKycStatus } from '../../api/types';
import { extractErrorMessage } from '../../api/client';
import { useAuth } from '../../context/AuthContext';
import { KYC_STATUS_LABELS, KYC_STATUS_TONES } from '../../utils/labels';
import { StatusPill } from '../common/StatusPill';
import { KycVerificationModal } from './KycVerificationModal';

// Блок «Верификация личности» на странице профиля — размещается перед блоком
// «Мои данные» (см. ProfilePage). Открывает попап с iframe Didit; итоговый статус
// приходит асинхронно через вебхук (DiditWebhookHandler), поэтому после закрытия
// попапа профиль перезапрашивается дважды: сразу и повторно через паузу — на случай,
// если вебхук ещё не успел обработаться к моменту события didit:completed.
export function KycStatusSection() {
    const { profile, refreshProfile } = useAuth();
    const [modalUrl, setModalUrl] = useState<string | null>(null);
    const [error, setError] = useState<string | null>(null);
    const [isStarting, setIsStarting] = useState(false);

    const status = (profile?.kyc_status ?? 'not_started') as AppKycStatus;

    async function handleStart() {
        setError(null);
        setIsStarting(true);

        try {
            const session = await startKycVerification();
            setModalUrl(session.url);
        } catch (submitError) {
            setError(extractErrorMessage(submitError, 'Не удалось запустить верификацию. Попробуйте ещё раз.'));
        } finally {
            setIsStarting(false);
        }
    }

    const handleCompleted = useCallback(() => {
        setModalUrl(null);
        void refreshProfile();
        window.setTimeout(() => void refreshProfile(), 4000);
    }, [refreshProfile]);

    return (
        <section className="auth-panel p-6">
            <div className="mb-5 flex items-center gap-3">
                <span className="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-[#f3f0ee] text-ink">
                    <IdentificationIcon className="h-5 w-5" />
                </span>
                <div className="flex-1">
                    <h2 className="text-base font-extrabold tracking-tight text-ink">Верификация личности</h2>
                    <p className="text-xs text-muted">Подтвердите личность, чтобы снять ограничения на операции</p>
                </div>
                <StatusPill label={KYC_STATUS_LABELS[status]} tone={KYC_STATUS_TONES[status]} loading={status === 'pending'} />
            </div>

            {error && <div className="form-error-banner mb-4">{error}</div>}
            {status === 'declined' && profile?.kyc_decline_reason && (
                <p className="mb-4 text-sm text-[#c0392b]">{profile.kyc_decline_reason}</p>
            )}

            {status !== 'pending' && status !== 'approved' && (
                <button type="button" className="btn btn-primary self-start" onClick={handleStart} disabled={isStarting}>
                    {isStarting ? 'Открываем…' : status === 'declined' ? 'Пройти повторно' : 'Пройти верификацию'}
                </button>
            )}

            {modalUrl && <KycVerificationModal url={modalUrl} onClose={() => setModalUrl(null)} onCompleted={handleCompleted} />}
        </section>
    );
}
