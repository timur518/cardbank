import type { Card } from '../../api/types';
import logoMark from '../../assets/logo-mark.svg';
import { formatBalanceHero } from '../../utils/format';
import { CARD_STATUS_LABELS, CARD_STATUS_TONES } from '../../utils/labels';
import { NetworkBadge } from '../common/NetworkBadge';
import { StatusPill } from '../common/StatusPill';

interface CardVisualProps {
    card: Card;
    /** Флаг страны выпуска рядом с логотипом платёжной системы — только в сетке «Мои
     * карты» (CardGridCard); на главном экране (CardsStrip) только логотип системы. */
    showCountryFlag?: boolean;
}

// Тёмный визуал банковской карты (иконка+название продукта, маска номера,
// баланс/статус, срок действия) — общий «вид карты», переиспользуется в
// сетке страницы «Мои карты» (CardGridCard) и в горизонтальной ленте карт
// (CardsStrip). Ширину/масштаб задаёт обёртка снаружи —
// сам визуал всегда aspect-[1.586] (пропорции банковской карты).
export function CardVisual({ card, showCountryFlag = false }: CardVisualProps) {
    const isActive = card.status === 'active';

    return (
        <div className="relative aspect-[1.586] w-full overflow-hidden rounded-2xl bg-gradient-to-br from-[#2b2a28] via-[#1c1b19] to-[#0e0e0d] p-3.5 text-white sm:p-5">
            <img src={logoMark} alt="" className="pointer-events-none absolute right-0 top-0 w-[140px] max-w-[45%] select-none" />

            <div className="relative flex h-full flex-col justify-between gap-1">
                <div className="flex items-center gap-1.5">
                    <p className="text-xs font-semibold leading-tight sm:text-sm">{card.card_product.name}</p>
                    <div className="ml-auto flex shrink-0 items-center gap-1.5">
                        {showCountryFlag && card.card_product.card_country_flag_url && (
                            <img
                                src={card.card_product.card_country_flag_url}
                                alt={card.card_product.card_country_label ?? ''}
                                title={card.card_product.card_country_label ?? undefined}
                                className="h-4 w-4 shrink-0 rounded-full object-cover"
                            />
                        )}
                        <NetworkBadge network={card.card_product.network} tone="mono" />
                    </div>
                </div>

                <p className="font-mono text-sm leading-tight tracking-[0.18em] text-white/90 sm:text-lg sm:tracking-[0.25em]">
                    •••• {card.card_last4 ?? '••••'}
                </p>

                <div className="flex items-end justify-between gap-3">
                    {isActive ? (
                        <div>
                            <p className="text-[8px] font-semibold leading-tight tracking-widest text-white/45 sm:text-[9px]">БАЛАНС</p>
                            <p className="font-mono text-base font-extrabold leading-tight sm:text-2xl">
                                {formatBalanceHero(card.balance, card.currency)}
                            </p>
                        </div>
                    ) : (
                        <StatusPill
                            label={CARD_STATUS_LABELS[card.status]}
                            tone={CARD_STATUS_TONES[card.status]}
                            loading={card.status === 'pending'}
                        />
                    )}
                    <p className="font-mono text-[10px] leading-tight text-white/60 sm:text-xs">{card.expiry ?? '—/—'}</p>
                </div>
            </div>
        </div>
    );
}
