import { Link } from 'react-router-dom';
import type { Card } from '../../api/types';
import { CardsListSkeleton } from '../common/Skeleton';
import { CardListItem } from './CardListItem';

interface CardsSidebarProps {
    cards: Card[];
    isLoading: boolean;
}

// Левый сайдбар «Мои карты»: список карт пользователя + слот для заказа новой. Скрыт на
// мобильных экранах — там список карт дублирует пункт «Карты» нижнего меню (MobileTabBar).
export function CardsSidebar({ cards, isLoading }: CardsSidebarProps) {
    return (
        <aside className="hidden flex-col gap-3 lg:flex lg:w-[280px] lg:shrink-0">
            <h2 className="text-sm font-extrabold uppercase tracking-wide text-muted">Мои карты</h2>

            {isLoading && <CardsListSkeleton />}

            {!isLoading && cards.length === 0 && (
                <p className="text-sm text-muted">У вас пока нет карт.</p>
            )}

            {!isLoading && cards.length > 0 && (
                <div className="flex flex-col gap-3 fade-in-up">
                    {cards.map((card) => (
                        <CardListItem key={card.id} card={card} />
                    ))}
                </div>
            )}

            <Link to="/cards/new" className="add-card-slot">
                + Новая карта
            </Link>
        </aside>
    );
}
