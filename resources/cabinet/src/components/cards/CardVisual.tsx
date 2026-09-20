import type { Card } from '../../api/types';
import { formatBalanceHero } from '../../utils/format';
import { CARD_STATUS_LABELS, CARD_STATUS_TONES } from '../../utils/labels';
import { StatusPill } from '../common/StatusPill';

interface CardVisualProps {
    card: Card;
}

// Тёмный визуал банковской карты (иконка+название продукта, маска номера,
// баланс/статус, срок действия) — общий «вид карты», переиспользуется в
// сетке страницы «Мои карты» (CardGridCard) и в горизонтальной ленте карт
// (CardsStrip). Ширину/масштаб задаёт обёртка снаружи —
// сам визуал всегда aspect-[1.586] (пропорции банковской карты).
export function CardVisual({ card }: CardVisualProps) {
    const isActive = card.status === 'active';

    return (
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
    );
}
