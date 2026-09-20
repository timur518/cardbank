import { Link } from 'react-router-dom';
import type { Card } from '../../api/types';
import { MobileCardsStripSkeleton } from '../common/Skeleton';
import { CardListItem } from './CardListItem';

interface MobileCardsStripProps {
    cards: Card[];
    isLoading: boolean;
}

/**
 * Горизонтальная прокручиваемая лента «Мои карты» над кнопками баланса на
 * мобильных экранах — там CardsSidebar скрыт (виден только от lg). Последний
 * элемент ленты, как и в десктопном сайдбаре, не карта, а прерывистая
 * плашка «+ Новая карта» (.add-card-slot) со ссылкой на оформление карты.
 */
export function MobileCardsStrip({ cards, isLoading }: MobileCardsStripProps) {
    return (
        <div className="flex gap-3 overflow-x-auto pb-1 lg:hidden">
            {isLoading ? (
                <MobileCardsStripSkeleton />
            ) : (
                cards.map((card) => (
                    <div key={card.id} className="w-[220px] shrink-0 fade-in-up">
                        <CardListItem card={card} />
                    </div>
                ))
            )}

            <Link to="/cards/new" className="add-card-slot w-[150px] shrink-0">
                + Новая карта
            </Link>
        </div>
    );
}
