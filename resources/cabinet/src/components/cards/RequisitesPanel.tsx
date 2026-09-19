import { useEffect, useRef, useState, type ReactNode, type TransitionEvent } from 'react';
import type { CardDetail, CardRequisites } from '../../api/types';
import { CopyButton } from '../common/CopyButton';

type PanelMode = 'card' | 'address';

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

const hasAddress = (card: CardDetail) =>
    Boolean(card.billing_address.country || card.billing_address.city || card.billing_address.address);

/**
 * Правая колонка страницы карты: реквизиты для оплаты (имя/номер/срок/CVV) и
 * платёжный адрес (AVS) — не два отдельных блока, а одно и то же место: по
 * кнопке содержимое анимированно (крос-фейд + подстройка высоты, как у
 * AuthTransition между страницами входа/регистрации) заменяется с данных
 * карты на данные адреса и обратно. Полный номер подгружается автоматически
 * (CardDetailPage), CVV — только по кнопке «Показать CVV».
 */
export function RequisitesPanel({ card, cardholderName, requisites, requisitesLoading, showCvv, onShowCvv }: RequisitesPanelProps) {
    const [mode, setMode] = useState<PanelMode>('card');
    const [displayedMode, setDisplayedMode] = useState<PanelMode>('card');
    const [visible, setVisible] = useState(true);

    const wrapperRef = useRef<HTMLDivElement>(null);
    const contentRef = useRef<HTMLDivElement>(null);
    const pendingModeRef = useRef<PanelMode | null>(null);

    function switchMode(next: PanelMode) {
        if (next === mode) {
            return;
        }

        setMode(next);
        pendingModeRef.current = next;
        setVisible(false);
    }

    function handleTransitionEnd(event: TransitionEvent<HTMLDivElement>) {
        if (event.target !== contentRef.current || visible || pendingModeRef.current === null) {
            return;
        }

        const next = pendingModeRef.current;
        pendingModeRef.current = null;
        setDisplayedMode(next);
        requestAnimationFrame(() => requestAnimationFrame(() => setVisible(true)));
    }

    // Подгоняем высоту обёртки под реальную высоту текущего содержимого, чтобы
    // переключение карта/адрес (разное число символов в значениях) не дёргало вёрстку.
    useEffect(() => {
        const content = contentRef.current;
        const wrapper = wrapperRef.current;

        if (!content || !wrapper) {
            return;
        }

        const sync = () => {
            wrapper.style.height = `${content.offsetHeight}px`;
        };
        sync();

        const observer = new ResizeObserver(sync);
        observer.observe(content);
        return () => observer.disconnect();
    }, [displayedMode]);

    const maskedNumber = `•••• •••• •••• ${card.card_last4 ?? '••••'}`;
    const numberValue = requisites?.card_number ?? maskedNumber;
    const cvvValue = showCvv && requisites ? requisites.cvv : '•••';
    const showAddressToggle = hasAddress(card);

    return (
        <div className="auth-panel p-6">
            <div className="mb-2 flex flex-wrap items-center justify-between gap-3">
                <div className="flex items-center gap-3">
                    <span className="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-[#f3f0ee] text-ink">
                        {displayedMode === 'card' ? (
                            <svg className="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={1.8}>
                                <rect x="2.5" y="5" width="19" height="14" rx="2.5" />
                                <path strokeLinecap="round" d="M2.5 9.5h19" />
                            </svg>
                        ) : (
                            <svg className="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={1.8}>
                                <path d="M12 21s-7-6.1-7-11a7 7 0 0 1 14 0c0 4.9-7 11-7 11z" />
                                <circle cx="12" cy="10" r="2.5" />
                            </svg>
                        )}
                    </span>
                    <div>
                        <h2 className="text-base font-extrabold tracking-tight text-ink">
                            {displayedMode === 'card' ? 'Данные для оплаты' : 'Платёжный адрес'}
                        </h2>
                        <p className="text-xs text-muted">
                            {displayedMode === 'card'
                                ? 'Имя, номер, срок и CVV'
                                : 'Вводите на сайте именно его, латиницей — не свой домашний'}
                        </p>
                    </div>
                </div>

                {showAddressToggle && (
                    <button type="button" className="btn" onClick={() => switchMode(mode === 'card' ? 'address' : 'card')}>
                        {mode === 'card' ? 'Показать платёжный адрес' : 'Показать данные карты'}
                    </button>
                )}
            </div>

            <div ref={wrapperRef} className="auth-transition-wrapper">
                <div
                    ref={contentRef}
                    className={`auth-transition-content${visible ? ' is-visible' : ''}`}
                    onTransitionEnd={handleTransitionEnd}
                >
                    {displayedMode === 'card' ? (
                        <>
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
                        </>
                    ) : (
                        <>
                            <RequisiteRow label="Страна (Country)" value={card.billing_address.country ?? '—'} copyValue={card.billing_address.country} />
                            <RequisiteRow label="Город (City)" value={card.billing_address.city ?? '—'} copyValue={card.billing_address.city} />
                            <RequisiteRow label="Адрес (Address)" value={card.billing_address.address ?? '—'} copyValue={card.billing_address.address} />
                            <RequisiteRow label="Индекс (ZIP)" value={card.billing_address.post_code ?? '—'} copyValue={card.billing_address.post_code} />
                        </>
                    )}
                </div>
            </div>
        </div>
    );
}
