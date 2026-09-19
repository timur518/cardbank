import { useEffect, useState } from 'react';
import { fetchCardTransactions } from '../../api/transactions';
import type { CardDetail, CardTransaction, PaginationMeta } from '../../api/types';
import { TransactionsTable } from '../transactions/TransactionsTable';

export type CardTabKey = 'transactions' | 'limits' | 'codes';

const TABS: { key: CardTabKey; label: string; disabled?: boolean }[] = [
    { key: 'transactions', label: 'Транзакции' },
    { key: 'codes', label: 'Коды', disabled: true },
    { key: 'limits', label: 'Лимиты' },
];

const TRANSACTIONS_PER_PAGE = 15;

interface CardTabsSectionProps {
    card: CardDetail;
    activeTab: CardTabKey;
    onTabChange: (tab: CardTabKey) => void;
}

/**
 * Нижний блок страницы карты: переключатель вкладок и их содержимое.
 * «Транзакции» — полная история операций по карте с пагинацией (реальные данные,
 * GET /cards/{card}/transactions). «Лимиты» — границы пополнения из карточки
 * продукта (CardProduct.topup_min/max_amount). «Коды» — пересылка одноразовых
 * кодов подтверждения от продавцов пока не реализована, вкладка оставлена
 * неактивной, а не заполнена придуманными данными.
 */
export function CardTabsSection({ card, activeTab, onTabChange }: CardTabsSectionProps) {
    const [transactions, setTransactions] = useState<CardTransaction[]>([]);
    const [meta, setMeta] = useState<PaginationMeta | null>(null);
    const [page, setPage] = useState(1);
    const [isLoading, setIsLoading] = useState(true);
    const [justUpdated, setJustUpdated] = useState(false);

    function load() {
        setIsLoading(true);

        fetchCardTransactions(card.id, { page, per_page: TRANSACTIONS_PER_PAGE })
            .then((response) => {
                setTransactions(response.data);
                setMeta(response.meta);
                setJustUpdated(true);
                setTimeout(() => setJustUpdated(false), 4000);
            })
            .finally(() => setIsLoading(false));
    }

    useEffect(load, [card.id, page]);

    return (
        <div id="card-tabs-section" className="flex flex-col gap-4">
            <div className="flex gap-2 overflow-x-auto">
                {TABS.map((tab) => (
                    <button
                        key={tab.key}
                        type="button"
                        disabled={tab.disabled}
                        onClick={() => onTabChange(tab.key)}
                        className={`dashboard-nav-link shrink-0 !border-b-0 rounded-full border px-4 py-2 ${
                            activeTab === tab.key
                                ? 'border-orange bg-ink text-white'
                                : 'border-border bg-surface text-muted'
                        } ${tab.disabled ? 'cursor-not-allowed opacity-50' : ''}`}
                        title={tab.disabled ? 'Функция скоро появится' : undefined}
                    >
                        {tab.label}
                    </button>
                ))}
            </div>

            <div className="auth-panel p-6">
                {activeTab === 'transactions' && (
                    <>
                        <div className="mb-4 flex flex-wrap items-center justify-between gap-3">
                            <p className="text-xs text-muted">
                                {meta ? `${meta.total} операций` : '—'}
                                {justUpdated && ' · обновлено сейчас'}
                            </p>
                            <div className="flex items-center gap-2">
                                <button type="button" className="btn" disabled title="Формирование PDF-выписки скоро появится">
                                    Выписка PDF
                                </button>
                                <button type="button" className="btn" onClick={load} disabled={isLoading}>
                                    Обновить
                                </button>
                            </div>
                        </div>

                        {isLoading ? (
                            <p className="py-8 text-center text-sm text-muted">Загрузка…</p>
                        ) : (
                            <TransactionsTable transactions={transactions} />
                        )}

                        {meta && meta.last_page > 1 && (
                            <div className="mt-4 flex items-center justify-center gap-4">
                                <button
                                    type="button"
                                    className="btn"
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
                                    className="btn"
                                    disabled={page >= meta.last_page}
                                    onClick={() => setPage((value) => value + 1)}
                                >
                                    Далее
                                </button>
                            </div>
                        )}
                    </>
                )}

                {activeTab === 'limits' && (
                    <div className="flex flex-col gap-3">
                        <h2 className="mb-1 text-sm font-extrabold uppercase tracking-wide text-muted">
                            Лимиты пополнения
                        </h2>
                        <div className="flex items-center justify-between border-t border-border py-3 first:border-t-0">
                            <span className="text-sm text-muted">Минимальная сумма</span>
                            <span className="text-sm font-semibold text-ink">
                                {card.card_product.topup_min_amount ? `$${card.card_product.topup_min_amount}` : '—'}
                            </span>
                        </div>
                        <div className="flex items-center justify-between border-t border-border py-3">
                            <span className="text-sm text-muted">Максимальная сумма</span>
                            <span className="text-sm font-semibold text-ink">
                                {card.card_product.topup_max_amount ? `$${card.card_product.topup_max_amount}` : '—'}
                            </span>
                        </div>
                    </div>
                )}

                {activeTab === 'codes' && (
                    <p className="py-8 text-center text-sm text-muted">
                        Пересылка кодов подтверждения от продавцов скоро появится.
                    </p>
                )}
            </div>
        </div>
    );
}
