import { Link } from 'react-router-dom';
import type { Card } from '../../api/types';
import { formatMoney } from '../../utils/format';
import { CARD_STATUS_LABELS, CARD_STATUS_TONES } from '../../utils/labels';
import { StatusPill } from '../common/StatusPill';
import { CardThumbnail } from './CardThumbnail';

interface CardListItemProps {
    card: Card;
}

// Строка карты в сайдбаре «Мои карты»: слева — картинка карты и маска номера,
// справа — баланс и название продукта. Для карт, ещё не выпущенных/не активных
// (waiting/pending/frozen/...), баланс показывать нечего и небезопасно вводить
// в заблуждение — вместо него выводится статус карты.
export function CardListItem({ card }: CardListItemProps) {
    return (
        <Link
            to={`/cards/${card.id}`}
            className="flex items-center justify-between gap-3 rounded-2xl border border-border bg-surface px-3 py-3 transition hover:border-orange"
        >
            <div className="flex items-center gap-3">
                <CardThumbnail skin={card.card_product.skin} name={card.card_product.name} />
                <span className="text-sm font-semibold tracking-wide text-ink">•••• {card.card_last4 ?? '••••'}</span>
            </div>
            <div className="text-right">
                {card.status === 'active' ? (
                    <p className="text-sm font-extrabold text-ink">{formatMoney(card.balance, card.currency)}</p>
                ) : (
                    <StatusPill label={CARD_STATUS_LABELS[card.status]} tone={CARD_STATUS_TONES[card.status]} />
                )}
                <p className="text-xs text-muted">{card.card_product.name}</p>
            </div>
        </Link>
    );
}
