import { Link } from 'react-router-dom';
import type { Card } from '../../api/types';
import { formatBalanceHero, formatMoney } from '../../utils/format';
import { CARD_STATUS_LABELS, CARD_STATUS_TONES } from '../../utils/labels';
import { StatusPill } from '../common/StatusPill';

interface CardGridCardProps {
    card: Card;
    monthSpend: number | null;
}

function hasAddress(card: Card): boolean {
    return Boolean(card.billing_address.address || card.billing_address.city || card.billing_address.country);
}

function shortAddress(card: Card): string | null {
    return card.billing_address.address ?? card.billing_address.city ?? card.billing_address.country;
}

function PinIcon() {
    return (
        <svg className="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={2}>
            <path d="M12 21s-7-6.1-7-11a7 7 0 0 1 14 0c0 4.9-7 11-7 11z" />
            <circle cx="12" cy="10" r="2.5" />
        </svg>
    );
}

// Карточка одной карты в сетке страницы «Мои карты»: сверху — тёмный визуал
// (иконка + название продукта, маска номера, баланс/статус, срок действия),
// снизу — название, маска+валюта, статус, траты за месяц и платёжный адрес.
export function CardGridCard({ card, monthSpend }: CardGridCardProps) {
    const isActive = card.status === 'active';

    return (
        <Link
            to={`/cards/${card.id}`}
            className="flex flex-col rounded-[28px] border border-border bg-surface p-4 shadow-sm transition hover:-translate-y-1 hover:shadow-lg"
        >
            <div className="relative aspect-[1.586] w-full overflow-hidden rounded-2xl bg-gradient-to-br from-[#2b2a28] via-[#1c1b19] to-[#0e0e0d] p-5 text-white">
                <span className="pointer-events-none absolute -right-4 -top-8 text-[9rem] leading-none font-black text-white/5 select-none">
                    {card.card_product.name.charAt(0)}
                </span>

                <div className="relative flex h-full flex-col justify-between">
                    <div className="flex items-center gap-2">
                        <span className="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-orange text-xs font-extrabold">
                            {card.card_product.name.charAt(0)}
                        </span>
                        <p className="text-sm font-semibold">{card.card_product.name}</p>
                    </div>

                    <p className="font-mono text-lg tracking-[0.25em] text-white/90">
                        •••• {card.card_last4 ?? '••••'}
                    </p>

                    <div className="flex items-end justify-between gap-3">
                        {isActive ? (
                            <div>
                                <p className="text-[9px] font-semibold tracking-widest text-white/45">БАЛАНС</p>
                                <p className="font-mono text-2xl font-extrabold">{formatBalanceHero(card.balance, card.currency)}</p>
                            </div>
                        ) : (
                            <StatusPill label={CARD_STATUS_LABELS[card.status]} tone={CARD_STATUS_TONES[card.status]} />
                        )}
                        <p className="font-mono text-xs text-white/60">{card.expiry ?? '—/—'}</p>
                    </div>
                </div>
            </div>

            <div className="mt-4 flex flex-col gap-3">
                <div className="flex items-center justify-between gap-2">
                    <p className="text-base font-extrabold text-ink">{card.card_product.name}</p>
                    <StatusPill label={CARD_STATUS_LABELS[card.status]} tone={CARD_STATUS_TONES[card.status]} />
                </div>
                <p className="-mt-2 text-sm text-muted">
                    •••• {card.card_last4 ?? '••••'} · {card.currency}
                </p>

                {isActive && (
                    <div className="flex items-center justify-between border-t border-border pt-3 text-sm">
                        <span className="text-muted">Потрачено в этом месяце</span>
                        <span className="font-semibold text-ink">
                            {monthSpend === null ? '…' : formatMoney(monthSpend, card.currency)}
                        </span>
                    </div>
                )}

                {hasAddress(card) && (
                    <div className="flex items-center gap-2 border-t border-border pt-3 text-sm text-muted">
                        <PinIcon />
                        <span className="truncate">
                            <span className="font-semibold text-ink">Адрес</span> · {shortAddress(card)}
                        </span>
                    </div>
                )}
            </div>
        </Link>
    );
}
