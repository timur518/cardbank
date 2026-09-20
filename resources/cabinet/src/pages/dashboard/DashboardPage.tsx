import { useEffect, useMemo, useState } from 'react';
import { Link } from 'react-router-dom';
import { fetchCards } from '../../api/cards';
import { fetchTransactions } from '../../api/transactions';
import type { Card, CardTransaction } from '../../api/types';
import { CardsSidebar } from '../../components/cards/CardsSidebar';
import { NewCardIcon, PlusIcon } from '../../components/common/Icons';
import { TransactionsTable } from '../../components/transactions/TransactionsTable';
import { formatMoney } from '../../utils/format';
import { useFitFontSize } from '../../utils/useFitFontSize';

export function DashboardPage() {
    const [cards, setCards] = useState<Card[]>([]);
    const [cardsLoading, setCardsLoading] = useState(true);
    const [transactions, setTransactions] = useState<CardTransaction[]>([]);
    const [transactionsLoading, setTransactionsLoading] = useState(true);

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

    // Текст суммы неизвестен заранее (число валют, количество цифр зависит от балансов карт),
    // поэтому кегль подбирается автоматически по фактической ширине через useFitFontSize
    // (от 28px для коротких сумм до 14px для длинных, например суммы в нескольких валютах сразу).
    const balanceText =
        totalsByCurrency.size === 0
            ? '—'
            : Array.from(totalsByCurrency.entries())
                  .map(([currency, total]) => formatMoney(total, currency))
                  .join(' + ');
    const balanceRef = useFitFontSize(balanceText, 28, 14);

    return (
        <div className="flex flex-col gap-8 lg:flex-row">
            <CardsSidebar cards={cards} isLoading={cardsLoading} />

            <div className="flex flex-1 flex-col gap-8">
                <div className="flex flex-nowrap gap-2 sm:flex-wrap sm:gap-4">
                    <div className="stat-btn stat-btn-static">
                        <span className="stat-balance-amount" ref={balanceRef}>
                            {balanceText}
                        </span>
                        <span className="stat-btn-value">Текущий остаток</span>
                    </div>

                    <Link to="/topup" className="stat-btn">
                        <span className="stat-btn-icon"><PlusIcon /></span>
                        <span className="stat-btn-label">Пополнить баланс</span>
                    </Link>

                    <Link to="/cards/new" className="stat-btn">
                        <span className="stat-btn-icon"><NewCardIcon /></span>
                        <span className="stat-btn-label">Новая карта</span>
                    </Link>
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
