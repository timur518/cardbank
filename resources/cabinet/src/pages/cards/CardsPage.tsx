import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { fetchCards } from '../../api/cards';
import type { Card } from '../../api/types';
import { CardListItem } from '../../components/cards/CardListItem';

export function CardsPage() {
    const [cards, setCards] = useState<Card[]>([]);
    const [isLoading, setIsLoading] = useState(true);

    useEffect(() => {
        fetchCards()
            .then(setCards)
            .finally(() => setIsLoading(false));
    }, []);

    return (
        <div className="flex flex-col gap-6">
            <h1 className="text-2xl font-extrabold tracking-tight text-ink">Мои карты</h1>

            {isLoading && <p className="text-sm text-muted">Загрузка…</p>}

            {!isLoading && (
                <div className="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    {cards.map((card) => (
                        <CardListItem key={card.id} card={card} />
                    ))}

                    <Link to="/cards/new" className="add-card-slot">
                        + Новая карта
                    </Link>
                </div>
            )}
        </div>
    );
}
