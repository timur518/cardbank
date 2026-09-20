import { MagnifyingGlassIcon } from '@heroicons/react/24/outline';
import { useEffect, useMemo, useState } from 'react';
import { Link } from 'react-router-dom';
import { fetchCards } from '../../api/cards';
import { fetchCardTransactions } from '../../api/transactions';
import type { Card, CardStatus } from '../../api/types';
import { CardGridCard } from '../../components/cards/CardGridCard';
import { CardsGridSkeleton } from '../../components/common/Skeleton';
import { startOfMonth, sumSuccessfulPurchases } from '../../utils/format';

type StatusFilter = 'all' | 'active' | 'frozen';

const FILTERS: { key: StatusFilter; label: string; matches: (status: CardStatus) => boolean }[] = [
    { key: 'all', label: 'Все', matches: () => true },
    { key: 'active', label: 'Активные', matches: (status) => status === 'active' },
    { key: 'frozen', label: 'Замороженные', matches: (status) => status === 'frozen' },
];

// Страница «Мои карты»: поиск и фильтр по статусу над сеткой карточек
// (CardGridCard). Траты за месяц запрашиваются отдельно для каждой активной
// карты (для остальных статусов трат не бывает) и подставляются в карточку по id.
export function CardsPage() {
    const [cards, setCards] = useState<Card[]>([]);
    const [isLoading, setIsLoading] = useState(true);
    const [search, setSearch] = useState('');
    const [statusFilter, setStatusFilter] = useState<StatusFilter>('all');
    const [monthSpend, setMonthSpend] = useState<Record<number, number | null>>({});

    useEffect(() => {
        fetchCards()
            .then(setCards)
            .finally(() => setIsLoading(false));
    }, []);

    useEffect(() => {
        const activeCards = cards.filter((card) => card.status === 'active');

        if (activeCards.length === 0) {
            return;
        }

        setMonthSpend((current) => {
            const next = { ...current };
            activeCards.forEach((card) => {
                if (!(card.id in next)) {
                    next[card.id] = null;
                }
            });
            return next;
        });

        activeCards.forEach((card) => {
            fetchCardTransactions(card.id, { type: 'purchase', date_from: startOfMonth(), per_page: 100 })
                .then((response) => {
                    setMonthSpend((current) => ({ ...current, [card.id]: sumSuccessfulPurchases(response.data) }));
                })
                .catch(() => {
                    setMonthSpend((current) => ({ ...current, [card.id]: 0 }));
                });
        });
    }, [cards]);

    const counts = useMemo(
        () => ({
            all: cards.length,
            active: cards.filter((card) => card.status === 'active').length,
            frozen: cards.filter((card) => card.status === 'frozen').length,
        }),
        [cards],
    );

    const filteredCards = useMemo(() => {
        const activeFilter = FILTERS.find((filter) => filter.key === statusFilter) ?? FILTERS[0];
        const query = search.trim().toLowerCase();

        return cards
            .filter((card) => activeFilter.matches(card.status))
            .filter((card) => {
                if (!query) {
                    return true;
                }

                return (
                    card.card_product.name.toLowerCase().includes(query) ||
                    (card.card_last4 ?? '').includes(query) ||
                    card.currency.toLowerCase().includes(query)
                );
            });
    }, [cards, statusFilter, search]);

    return (
        <div className="flex flex-col gap-6">
            <div className="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h1 className="text-3xl font-extrabold tracking-tight text-ink">Все карты</h1>
                </div>
                <Link to="/cards/new" className="btn btn-orange">
                    + Выпустить карту
                </Link>
            </div>

            <div className="flex flex-wrap items-center gap-3">
                <label className="cards-search">
                    <MagnifyingGlassIcon className="h-4 w-4 shrink-0" />
                    <input
                        type="text"
                        placeholder="Поиск по названию, последним 4 цифрам, валюте…"
                        value={search}
                        onChange={(event) => setSearch(event.target.value)}
                    />
                </label>

                <div className="cards-filter-tabs">
                    {FILTERS.map((filter) => (
                        <button
                            key={filter.key}
                            type="button"
                            className={`cards-filter-tab${statusFilter === filter.key ? ' is-active' : ''}`}
                            onClick={() => setStatusFilter(filter.key)}
                        >
                            {filter.label} {counts[filter.key]}
                        </button>
                    ))}
                </div>
            </div>

            {isLoading && <CardsGridSkeleton />}

            {!isLoading && filteredCards.length === 0 && (
                <p className="text-sm text-muted">
                    {cards.length === 0 ? 'У вас пока нет карт.' : 'По заданным условиям карты не найдены.'}
                </p>
            )}

            {!isLoading && filteredCards.length > 0 && (
                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-2 fade-in-up">
                    {filteredCards.map((card) => (
                        <CardGridCard key={card.id} card={card} monthSpend={monthSpend[card.id] ?? null} />
                    ))}
                </div>
            )}
        </div>
    );
}
