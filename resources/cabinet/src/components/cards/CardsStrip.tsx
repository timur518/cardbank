import { Link } from 'react-router-dom';
import type { Card } from '../../api/types';
import { CardsStripSkeleton } from '../common/Skeleton';
import { CardVisual } from './CardVisual';

interface CardsStripProps {
    cards: Card[];
    isLoading: boolean;
}

// Ширина одной карты в ленте — не больше 2/3 видимой зоны экрана на мобильных
// (виден край следующей карты — подсказка, что лента прокручивается), на
// более широких экранах (планшет/десктоп) упирается в потолок 280px.
const CARD_WIDTH_CLASS = 'w-[66.666vw] max-w-[280px] shrink-0';

/**
 * Горизонтальная прокручиваемая лента «Мои карты» — заменяет собой прежний
 * левый сайдбар (CardsSidebar) на десктопе: вместо отдельной колонки рядом с
 * основным контентом теперь один общий узкий (max-w-830px, DashboardLayout)
 * центральный столбец, а список карт — прокручивается горизонтально сверху,
 * одинаково на мобильных и на десктопе. Карты — тот же визуал банковской
 * карты (CardVisual), что и в сетке страницы «Мои карты», просто меньшего
 * размера. Последний элемент ленты — не карта, а прерывистая плашка
 * «+ Новая карта» (.add-card-slot) той же ширины и пропорций, что и карты.
 */
export function CardsStrip({ cards, isLoading }: CardsStripProps) {
    return (
        <div className="no-scrollbar flex gap-3 overflow-x-auto pb-1">
            {isLoading ? (
                <CardsStripSkeleton />
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
