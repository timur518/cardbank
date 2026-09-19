import { useEffect, useMemo, useState } from 'react';
import { fetchCards } from '../../api/cards';
import { API_ROOT } from '../../api/client';
import { fetchTransactions } from '../../api/transactions';
import type { Card, CardTransaction } from '../../api/types';
import { CardsSidebar } from '../../components/cards/CardsSidebar';
import { TransactionsTable } from '../../components/transactions/TransactionsTable';
import { formatMoney } from '../../utils/format';

// Раздел «Заказать карту» лендинга — единственный сейчас существующий сценарий
// выпуска/первого пополнения карты (полноценная форма в самом ЛК ещё не построена).
const NEW_CARD_URL = `${API_ROOT}/#apply`;

export function DashboardPage() {
    const [cards, setCards] = useState<Card[]>([]);
    const [cardsLoading, setCardsLoading] = useState(true);
    const [transactions, setTransactions] = useState<CardTransaction[]>([]);
    const [transactionsLoading, setTransactionsLoading] = useState(true);
    const [showTotalBalance, setShowTotalBalance] = useState(false);

    useEffect(() => {
        fetchCards()
            .then(setCards)
            .finally(() => setCardsLoading(false));

        fetchTransactions({ per_page: 8 })
            .then((response) => setTransactions(response.data))
            .finally(() => setTransactionsLoading(false));
    }, []);

    // Сумма балансов активных карт по каждой валюте отдельно — карты в разных
    // валютах не складываются друг с другом.
    const totalsByCurrency = useMemo(() => {
        const totals = new Map<string, number>();

        for (const card of cards) {
            if (card.status !== 'active') {
                continue;
            }

            totals.set(card.currency, (totals.get(card.currency) ?? 0) + Number(card.balance));
        }

        return totals;
    }, [cards]);

    return (
        <div className="flex flex-col gap-8 lg:flex-row">
            <CardsSidebar cards={cards} isLoading={cardsLoading} newCardHref={NEW_CARD_URL} />

            <div className="flex flex-1 flex-col gap-8">
                <div className="flex flex-wrap gap-4">
                    <a href={NEW_CARD_URL} className="stat-btn">
                        <span className="stat-btn-icon">💳</span>
                        <span className="stat-btn-label">Выпустить новую карту</span>
                    </a>

                    <a href={NEW_CARD_URL} className="stat-btn">
                        <span className="stat-btn-icon">➕</span>
                        <span className="stat-btn-label">Пополнить баланс</span>
                    </a>

                    <button type="button" className="stat-btn" onClick={() => setShowTotalBalance((value) => !value)}>
                        <span className="stat-btn-icon">💰</span>
                        <span className="stat-btn-label">Баланс всех карт</span>
                        {showTotalBalance && (
                            <span className="stat-btn-value">
                                {totalsByCurrency.size === 0
                                    ? 'Нет активных карт'
                                    : Array.from(totalsByCurrency.entries())
                                          .map(([currency, total]) => formatMoney(total, currency))
                                          .join(' + ')}
                            </span>
                        )}
                    </button>
                </div>

                <div className="auth-panel p-6">
                    <h2 className="mb-4 text-sm font-extrabold uppercase tracking-wide text-muted">
                        История последних операций
                    </h2>

                    {transactionsLoading ? (
                        <p className="py-8 text-center text-sm text-muted">Загрузка…</p>
                    ) : (
                        <TransactionsTable transactions={transactions} />
                    )}
                </div>
            </div>
        </div>
    );
}
