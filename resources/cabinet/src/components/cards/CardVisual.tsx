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
    /**
     * Уменьшённые отступы/шрифты — только для узких карточек в CardsStrip на главной
     * странице (там ширина карты ограничена 280px независимо от ширины экрана, поэтому
     * размер шрифта не должен зависеть от viewport через sm:, а всегда оставаться компактным);
     * в сетке «Мои карты» (CardGridCard) и везде ещё — всегда false, внешний вид не меняется.
     */
    compact?: boolean;
}

// Тёмный визуал банковской карты (иконка+название продукта, маска номера,
// баланс/статус, срок действия) — общий «вид карты», переиспользуется в
// сетке страницы «Мои карты» (CardGridCard) и в горизонтальной ленте карт
// (CardsStrip). Ширину/масштаб задаёт обёртка снаружи —
// сам визуал всегда пропорции банковской карты (85.6×53.98мм, ISO 7810 ID-1 ≈ 1.586:1).
// Размеры шрифтов/отступов — исходные (как в CardGridCard), если compact=false; уменьшаются
// только при compact=true (узкие карточки CardsStrip на главной странице), независимо от
// ширины экрана.
export function CardVisual({ card, showCountryFlag = false, compact = false }: CardVisualProps) {
    const isActive = card.status === 'active';

    return (
        <div className="relative w-full overflow-hidden rounded-2xl bg-gradient-to-br from-[#2b2a28] via-[#1c1b19] to-[#0e0e0d] text-white">
            {/* Высота задаётся классическим приёмом padding-top в % от ширины (1/1.586 ≈ 63.05%)
                вместо CSS aspect-ratio: на реальных iPhone (iOS Safari и Chrome — оба на движке
                WebKit) aspect-ratio у карточки внутри горизонтально прокручиваемого flex-контейнера
                (CardsStrip) пересчитывался некорректно — карта визуально становилась ниже, чем
                нужно для её же контента, и баланс/срок действия обрезались снизу overflow-hidden.
                Баг не воспроизводился в эмуляции мобильного экрана через DevTools на компьютере,
                потому что там рендерит Blink/Chromium, а не WebKit. padding-top — надёжный кросс-
                браузерный приём на обычном блочном layout, не зависящий от поддержки aspect-ratio
                конкретным движком. */}
            <div style={{ paddingTop: '63.0517%' }} />

            <img src={logoMark} alt="" className="pointer-events-none absolute right-0 top-0 w-[140px] max-w-[45%] select-none" />

            <div
                className={`absolute inset-0 flex flex-col justify-between ${compact ? 'gap-1 p-3.5' : 'p-5'}`}
            >
                <div className="flex items-center gap-2">
                    <p
                        className={`font-semibold ${compact ? 'min-w-0 flex-1 truncate text-xs leading-tight' : 'text-sm'}`}
                    >
                        {card.card_product.name}
                    </p>
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

                <p
                    className={`font-mono text-white/90 ${compact ? 'text-sm leading-tight tracking-[0.18em]' : 'text-lg tracking-[0.25em]'}`}
                >
                    •••• {card.card_last4 ?? '••••'}
                </p>

                <div className="flex items-end justify-between gap-3">
                    {isActive ? (
                        <div>
                            <p
                                className={`font-semibold tracking-widest text-white/45 ${compact ? 'text-[8px] leading-tight' : 'text-[9px]'}`}
                            >
                                БАЛАНС
                            </p>
                            <p className={`font-mono font-extrabold ${compact ? 'text-base leading-tight' : 'text-2xl'}`}>
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
                    <p className={`font-mono text-white/60 ${compact ? 'text-[10px] leading-tight' : 'text-xs'}`}>{card.expiry ?? '—/—'}</p>
                </div>
            </div>
        </div>
    );
}
