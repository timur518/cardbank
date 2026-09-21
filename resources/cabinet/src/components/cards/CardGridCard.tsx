import { Link } from 'react-router-dom';
import type { Card } from '../../api/types';
import { formatMoney } from '../../utils/format';
import { CARD_STATUS_LABELS, CARD_STATUS_TONES } from '../../utils/labels';
import { StatusPill } from '../common/StatusPill';
import { CardVisual } from './CardVisual';

interface CardGridCardProps {
    card: Card;
    monthSpend: number | null;
}

// Карточка одной карты в сетке страницы «Мои карты»
export function CardGridCard({ card, monthSpend }: CardGridCardProps) {
    const isActive = card.status === 'active';

    return (
        <Link
            to={`/cards/${card.id}`}
            className="flex flex-col rounded-[28px] bg-surface p-4 shadow-sm transition hover:-translate-y-1 hover:shadow-lg"
        >
            <CardVisual card={card} />

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
            </div>
        </Link>
    );
}
