import { useEffect, useState } from 'react';
import { fetchTransactions } from '../../api/transactions';
import type { CardTransaction, PaginationMeta } from '../../api/types';
import { TransactionsTable } from '../../components/transactions/TransactionsTable';

export function TransactionsPage() {
    const [transactions, setTransactions] = useState<CardTransaction[]>([]);
    const [meta, setMeta] = useState<PaginationMeta | null>(null);
    const [page, setPage] = useState(1);
    const [isLoading, setIsLoading] = useState(true);

    useEffect(() => {
        setIsLoading(true);

        fetchTransactions({ page, per_page: 20 })
            .then((response) => {
                setTransactions(response.data);
                setMeta(response.meta);
            })
            .finally(() => setIsLoading(false));
    }, [page]);

    return (
        <div className="flex flex-col gap-6">
            <h1 className="text-2xl font-extrabold tracking-tight text-ink">Операции</h1>

            <div className="auth-panel p-6">
                {isLoading ? (
                    <p className="py-8 text-center text-sm text-muted">Загрузка…</p>
                ) : (
                    <TransactionsTable transactions={transactions} />
                )}

                {meta && meta.last_page > 1 && (
                    <div className="mt-4 flex items-center justify-center gap-4">
                        <button
                            type="button"
                            className="btn btn-primary"
                            disabled={page <= 1}
                            onClick={() => setPage((value) => value - 1)}
                        >
                            Назад
                        </button>
                        <span className="text-sm text-muted">
                            Страница {meta.current_page} из {meta.last_page}
                        </span>
                        <button
                            type="button"
                            className="btn btn-primary"
                            disabled={page >= meta.last_page}
                            onClick={() => setPage((value) => value + 1)}
                        >
                            Далее
                        </button>
                    </div>
                )}
            </div>
        </div>
    );
}
