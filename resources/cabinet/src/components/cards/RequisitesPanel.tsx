import { useState, type ReactNode } from 'react';
import type { CardDetail, CardRequisites } from '../../api/types';
import { CopyButton } from '../common/CopyButton';

interface RequisitesPanelProps {
    card: CardDetail;
    cardholderName: string;
    requisites: CardRequisites | null;
    requisitesLoading: boolean;
    showCvv: boolean;
    onShowCvv: () => void;
}

interface RequisiteRowProps {
    label: string;
    value: string;
    copyValue: string | null;
    action?: ReactNode;
}

function RequisiteRow({ label, value, copyValue, action }: RequisiteRowProps) {
    return (
        <div className="flex items-center justify-between gap-4 border-t border-border px-1 py-4 first:border-t-0">
            <div>
                <p className="text-xs font-semibold text-muted">{label}</p>
                <p className="mt-1 font-mono text-base font-semibold text-ink">{value}</p>
            </div>
            <div className="flex items-center gap-1">
                {action}
                <CopyButton value={copyValue} />
            </div>
        </div>
    );
}

const HAS_ADDRESS = (card: CardDetail) =>
    Boolean(card.billing_address.country || card.billing_address.city || card.billing_address.address);

function formatAddress(card: CardDetail): string {
    return [card.billing_address.country, card.billing_address.city, card.billing_address.address, card.billing_address.post_code]
        .filter(Boolean)
        .join(', ');
}

// Правая колонка страницы карты: реквизиты для оплаты (имя/номер/срок/CVV) и
// платёжный адрес (AVS). Полный номер подгружается автоматически (CardDetailPage),
// CVV — только по кнопке «Показать CVV», адрес — только по кнопке «Показать
// платёжный адрес», с анимированным раскрытием (CSS grid-template-rows 0fr → 1fr).
export function RequisitesPanel({ card, cardholderName, requisites, requisitesLoading, showCvv, onShowCvv }: RequisitesPanelProps) {
    const [showAddress, setShowAddress] = useState(false);

    const maskedNumber = `•••• •••• •••• ${card.card_last4 ?? '••••'}`;
    const numberValue = requisites?.card_number ?? maskedNumber;
    const cvvValue = showCvv && requisites ? requisites.cvv : '•••';

    return (
        <div className="auth-panel p-6">
            <div className="mb-2 flex items-center gap-3">
                <span className="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-[#f3f0ee] text-ink">
                    <svg className="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={1.8}>
                        <rect x="2.5" y="5" width="19" height="14" rx="2.5" />
                        <path strokeLinecap="round" d="M2.5 9.5h19" />
                    </svg>
                </span>
                <div>
                    <h2 className="text-base font-extrabold tracking-tight text-ink">Данные для оплаты</h2>
                    <p className="text-xs text-muted">Имя, номер, срок, CVV и платёжный адрес</p>
                </div>
            </div>

            <RequisiteRow label="Имя на карте" value={cardholderName} copyValue={cardholderName} />
            <RequisiteRow label="Номер карты" value={numberValue} copyValue={requisites?.card_number ?? null} />
            <RequisiteRow label="Срок действия" value={card.expiry ?? '—'} copyValue={card.expiry} />
            <RequisiteRow
                label="CVV"
                value={cvvValue}
                copyValue={showCvv ? (requisites?.cvv ?? null) : null}
                action={
                    !showCvv && (
                        <button type="button" className="btn" disabled={requisitesLoading} onClick={onShowCvv}>
                            Показать CVV
                        </button>
                    )
                }
            />

            {HAS_ADDRESS(card) && (
                <div className="mt-5 rounded-2xl border border-border bg-[#fdfaf6] p-5">
                    <div className="mb-3 flex flex-wrap items-center justify-between gap-3">
                        <h3 className="flex items-center gap-2 text-sm font-extrabold text-ink">
                            <svg className="h-4 w-4 text-orange" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={2}>
                                <path d="M12 21s-7-6.1-7-11a7 7 0 0 1 14 0c0 4.9-7 11-7 11z" />
                                <circle cx="12" cy="10" r="2.5" />
                            </svg>
                            Платёжный адрес
                        </h3>
                        {showAddress && <CopyButton value={formatAddress(card)} label="Скопировать адрес" />}
                    </div>
                    <p className="mb-4 text-xs text-muted">
                        Вводите на сайте именно его, латиницей — не свой домашний. Из-за чужого адреса магазины
                        отклоняют оплату чаще всего.
                    </p>

                    {!showAddress && (
                        <button type="button" className="btn w-full" onClick={() => setShowAddress(true)}>
                            Показать платёжный адрес
                        </button>
                    )}

                    <div
                        className={`grid transition-all duration-300 ease-out ${
                            showAddress ? 'grid-rows-[1fr] opacity-100' : 'grid-rows-[0fr] opacity-0'
                        }`}
                    >
                        <div className="overflow-hidden">
                            <RequisiteRow label="Страна (Country)" value={card.billing_address.country ?? '—'} copyValue={card.billing_address.country} />
                            <RequisiteRow label="Город (City)" value={card.billing_address.city ?? '—'} copyValue={card.billing_address.city} />
                            <RequisiteRow label="Адрес (Address)" value={card.billing_address.address ?? '—'} copyValue={card.billing_address.address} />
                            <RequisiteRow label="Индекс (ZIP)" value={card.billing_address.post_code ?? '—'} copyValue={card.billing_address.post_code} />
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}
