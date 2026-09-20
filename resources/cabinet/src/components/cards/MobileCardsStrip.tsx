import { Link } from 'react-router-dom';
import type { Card } from '../../api/types';
import { MobileCardsStripSkeleton } from '../common/Skeleton';
import { CardVisual } from './CardVisual';

interface MobileCardsStripProps {
    cards: Card[];
    isLoading: boolean;
}

// Ширина одной карты в ленте — не больше 2/3 видимой зоны экрана (виден край
// следующей карты — подсказка, что лента прокручивается), но не шире 280px
// на более широких мобильных/планшетных экранах.
const CARD_WIDTH_CLASS = 'w-[66.666vw] max-w-[280px] shrink-0';

/**
 * Горизонтальная прокручиваемая лента «Мои карты» над кнопками баланса на
 * мобильных экранах — там CardsSidebar скрыт (виден только от lg). Карты —
 * тот же визуал банковской карты (CardVisual), что и в сетке страницы «Мои
 * карты», просто меньшего размера. Последний элемент ленты, как и в
 * десктопном сайдбаре, не карта, а прерывистая плашка «+ Новая карта»
 * (.add-card-slot) той же ширины и пропорций, что и карты.
 */
export function MobileCardsStrip({ cards, isLoading }: MobileCardsStripProps) {
    return (
        <div className="flex gap-3 overflow-x-auto pb-1 lg:hidden">
            {isLoading ? (
                <MobileCardsStripSkeleton />
            ) : (
                cards.map((card) => (
                    <Link key={card.id} to={`/cards/${card.id}`} className={`${CARD_WIDTH_CLASS} fade-in-up`}>
                        <CardVisual card={card} />
                    </Link>
                ))
            )}

            <Link to="/cards/new" className={`add-card-slot aspect-[1.586] ${CARD_WIDTH_CLASS}`}>
                + Новая карта
            </Link>
        </div>
    );
}
