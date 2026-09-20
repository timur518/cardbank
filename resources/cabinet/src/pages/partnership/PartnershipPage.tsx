import { BanknotesIcon, ClockIcon, CreditCardIcon, WalletIcon } from '@heroicons/react/24/outline';
import { useEffect, useState } from 'react';
import { API_ROOT } from '../../api/client';
import { fetchReferralSettings } from '../../api/settings';
import type { ReferralSettings } from '../../api/types';
import { CopyButton } from '../../components/common/CopyButton';
import { PartnershipSkeleton } from '../../components/common/Skeleton';
import { useAuth } from '../../context/AuthContext';

// Партнёрская программа: приглашение по личной ссылке даёт вознаграждение с
// платежей приглашённого — карту оформили банкиры, а не «реферальная
// система», поэтому термины и оформление страницы такие же официальные,
// как в остальном личном кабинете. Условия (проценты, срок выдержки,
// минимальная сумма вывода) приходят из настроек банка (ReferralSettings)
// и могут быть ещё не заданы администратором — тогда вместо цифры «—».
export function PartnershipPage() {
    const { profile } = useAuth();
    const [settings, setSettings] = useState<ReferralSettings | null>(null);

    useEffect(() => {
        fetchReferralSettings().then(setSettings);
    }, []);

    const referralLink = profile?.referral_code ? `${API_ROOT}/?ref=${profile.referral_code}` : null;

    const terms = [
        {
            icon: BanknotesIcon,
            title: `${settings?.referral_topup_rate ?? '—'}% с пополнений`,
            description: 'С каждого пополнения баланса приглашённым — на всё время, что он пользуется картой, а не разово.',
        },
        {
            icon: CreditCardIcon,
            title: `${settings?.referral_issue_rate ?? '—'}% с выпуска карты`,
            description: 'С оплаты за каждую выпущенную карту приглашённого, включая вторую и последующие.',
        },
        {
            icon: ClockIcon,
            title: `Начисление через ${settings?.referral_hold_days ?? '—'} дней`,
            description: 'Вознаграждение выдерживается перед зачислением — это защита от возвратов платежей.',
        },
        {
            icon: WalletIcon,
            title: 'Вывод вознаграждения',
            description: `На баланс в личном кабинете — от ${settings?.referral_min_wallet_rub ?? '—'} ₽, на банковскую карту — от ${settings?.referral_min_bank_rub ?? '—'} ₽.`,
        },
    ];

    return (
        <div className="flex flex-col gap-6">
            <div>
                <p className="text-sm text-muted">Приглашайте друзей и получайте вознаграждение с их платежей</p>
                <h1 className="text-2xl font-extrabold tracking-tight text-ink">Партнёрская программа</h1>
            </div>

            {!settings ? (
                <PartnershipSkeleton />
            ) : (
                <div className="flex flex-col gap-6 fade-in-up">
                    <div className="auth-panel flex flex-col gap-4 p-6 sm:p-8">
                        <div>
                            <h2 className="text-sm font-extrabold uppercase tracking-wide text-muted">Ваша ссылка</h2>
                            <p className="mt-1 text-sm text-muted">
                                Работает с любой страницы сайта. Тот, кто перейдёт по ней и оформит карту, закрепляется
                                за вами навсегда.
                            </p>
                        </div>

                        {referralLink ? (
                            <div className="flex flex-wrap items-center gap-3 rounded-2xl bg-bg px-4 py-3">
                                <code className="min-w-0 flex-1 truncate text-sm font-semibold text-ink">
                                    {referralLink}
                                </code>
                                <CopyButton value={referralLink} label="Скопировать" />
                            </div>
                        ) : (
                            <p className="text-sm text-muted">Ссылка появится после подключения к партнёрской программе.</p>
                        )}
                    </div>

                    <div>
                        <h2 className="mb-4 text-sm font-extrabold uppercase tracking-wide text-muted">
                            Условия вознаграждения
                        </h2>

                        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            {terms.map(({ icon: Icon, title, description }) => (
                                <div key={title} className="flex items-start gap-4 rounded-2xl bg-surface p-5 shadow-sm">
                                    <span className="tx-icon">
                                        <Icon />
                                    </span>
                                    <div>
                                        <p className="text-sm font-extrabold text-ink">{title}</p>
                                        <p className="mt-1 text-sm text-muted">{description}</p>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}
