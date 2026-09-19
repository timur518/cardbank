import { useEffect, useState } from 'react';
import { fetchReferralSettings } from '../../api/settings';
import { API_ROOT } from '../../api/client';
import type { ReferralSettings } from '../../api/types';
import { useAuth } from '../../context/AuthContext';

export function PartnershipPage() {
    const { profile } = useAuth();
    const [settings, setSettings] = useState<ReferralSettings | null>(null);
    const [copied, setCopied] = useState(false);

    useEffect(() => {
        fetchReferralSettings().then(setSettings);
    }, []);

    const referralLink = profile?.referral_code ? `${API_ROOT}/?ref=${profile.referral_code}` : null;

    const copyLink = () => {
        if (!referralLink) {
            return;
        }

        navigator.clipboard.writeText(referralLink).then(() => {
            setCopied(true);
            setTimeout(() => setCopied(false), 2000);
        });
    };

    return (
        <div className="flex flex-col gap-6">
            <h1 className="text-2xl font-extrabold tracking-tight text-ink">Партнёрство</h1>

            <div className="auth-panel flex flex-col gap-4 p-6">
                <p className="text-sm text-muted">
                    Приглашайте друзей по своей реферальной ссылке и получайте вознаграждение с их
                    выпуска карт и пополнений.
                </p>

                {referralLink ? (
                    <div className="flex flex-wrap items-center gap-3">
                        <code className="rounded-xl border border-border bg-bg px-4 py-2 text-sm">{referralLink}</code>
                        <button type="button" className="btn btn-primary" onClick={copyLink}>
                            {copied ? 'Скопировано' : 'Скопировать'}
                        </button>
                    </div>
                ) : (
                    <p className="text-sm text-muted">Реферальный код не назначен.</p>
                )}

                {settings && (settings.referral_issue_rate || settings.referral_topup_rate || settings.referral_hold_days) && (
                    <dl className="mt-2 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <dt className="text-xs font-bold text-muted">За выпуск карты другом</dt>
                            <dd className="text-lg font-extrabold text-ink">{settings.referral_issue_rate ?? '—'}%</dd>
                        </div>
                        <div>
                            <dt className="text-xs font-bold text-muted">За пополнение другом</dt>
                            <dd className="text-lg font-extrabold text-ink">{settings.referral_topup_rate ?? '—'}%</dd>
                        </div>
                        <div>
                            <dt className="text-xs font-bold text-muted">Срок удержания</dt>
                            <dd className="text-lg font-extrabold text-ink">{settings.referral_hold_days ?? '—'} дней</dd>
                        </div>
                    </dl>
                )}
            </div>
        </div>
    );
}
