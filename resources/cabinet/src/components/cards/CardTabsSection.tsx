import { ShieldCheckIcon } from '@heroicons/react/24/outline';
import { useEffect, useState } from 'react';
import { fetchCardOtpCodes } from '../../api/cards';
import { fetchCardTransactions } from '../../api/transactions';
import type { CardDetail, CardOtpCode, CardTransaction, PaginationMeta } from '../../api/types';
import { CopyButton } from '../common/CopyButton';
import { Skeleton, TransactionsSkeleton } from '../common/Skeleton';
import { formatTime, groupByDate } from '../../utils/dateGroups';
import { TransactionsTable } from '../transactions/TransactionsTable';

export type CardTabKey = 'transactions' | 'limits' | 'codes';

const TABS: { key: CardTabKey; label: string }[] = [
    { key: 'transactions', label: 'Транзакции' },
    { key: 'codes', label: '3DS коды' },
    { key: 'limits', label: 'Лимиты' },
];

const TRANSACTIONS_PER_PAGE = 15;

interface CardTabsSectionProps {
    card: CardDetail;
    activeTab: CardTabKey;
    onTabChange: (tab: CardTabKey) => void;
}

/**
 * Нижний блок страницы карты — вкладки-ярлычки (.card-tab), примыкающие к панели с
 * содержимым вкладки. «Транзакции» — полная история операций по карте с пагинацией
 * (реальные данные, GET /cards/{card}/transactions). «Лимиты» — границы пополнения,
 * запрещённые магазины и полный текст условий — всё из карточки продукта в админке
 * (CardProduct.topup_min/max_amount, restricted_merchants, full_terms). «3DS коды» —
 * OTP-коды card holder verification, GET /cards/{card}/otp-codes (живой запрос к
 * провайдеру, без локального хранилища — CardsPro не хранит их дольше нескольких
 * последних штук).
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

    // OTP(3DS)-коды — живой запрос к провайдеру (не фоновая синхронизация, как у
    // транзакций), поэтому грузится лениво при первом открытии вкладки, а дальше —
    // только по кнопке «Обновить».
    const [otpCodes, setOtpCodes] = useState<CardOtpCode[]>([]);
    const [otpLoading, setOtpLoading] = useState(false);
    const [otpLoaded, setOtpLoaded] = useState(false);

    function loadOtpCodes() {
        setOtpLoading(true);

        fetchCardOtpCodes(card.id)
            .then((codes) => {
                setOtpCodes(codes);
                setOtpLoaded(true);
            })
            .finally(() => setOtpLoading(false));
    }

    useEffect(() => {
        if (activeTab === 'codes' && !otpLoaded) {
            loadOtpCodes();
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [activeTab]);

    return (
        <div id="card-tabs-section" className="flex flex-col">
            <div className="no-scrollbar flex gap-1 overflow-x-auto">
                {TABS.map((tab) => (
                    <button
                        key={tab.key}
                        type="button"
                        onClick={() => onTabChange(tab.key)}
                        className={`card-tab shrink-0 ${activeTab === tab.key ? 'card-tab-active' : ''}`}
                    >
                        {tab.label}
                    </button>
                ))}
            </div>

            <div
                className={`auth-panel card-tabs-panel p-6 ${activeTab !== 'transactions' ? 'card-tabs-panel-all-corners' : ''}`}
            >
                {activeTab === 'transactions' && (
                    <>
                        <div className="mb-4 flex flex-wrap items-center justify-between gap-3">
                            <p className="text-xs text-muted">
                                {meta ? `${meta.total} операций` : <Skeleton className="h-3 w-20" />}
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
                            <TransactionsSkeleton />
                        ) : (
                            <div className="fade-in-up">
                                <TransactionsTable transactions={transactions} />
                            </div>
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
                    <div className="flex flex-col gap-6">
                        <div className="flex flex-col gap-3">
                            <h2 className="mb-1 text-sm font-extrabold uppercase tracking-wide text-muted">
                                Страна карты
                            </h2>
                            <div className="flex items-center justify-between border-t border-border py-3 first:border-t-0">
                                <span className="text-sm text-muted">Страна выпуска</span>
                                <span className="flex items-center gap-2 text-sm font-semibold text-ink">
                                    {card.card_product.card_country_flag_url && (
                                        <img
                                            src={card.card_product.card_country_flag_url}
                                            alt=""
                                            className="h-4 w-4 rounded-full object-cover"
                                        />
                                    )}
                                    {card.card_product.card_country_label ?? '—'}
                                </span>
                            </div>
                        </div>

                        <div className="flex flex-col gap-3 border-t border-border pt-5">
                            <h2 className="mb-1 text-sm font-extrabold uppercase tracking-wide text-muted">Комиссии</h2>
                            <div className="flex items-center justify-between border-t border-border py-3 first:border-t-0">
                                <span className="text-sm text-muted">Успешная оплата</span>
                                <span className="text-sm font-semibold text-ink">
                                    {card.card_product.successful_payment_fee_usd ? `$${card.card_product.successful_payment_fee_usd}` : '—'}
                                </span>
                            </div>
                            <div className="flex items-center justify-between border-t border-border py-3">
                                <span className="text-sm text-muted">Неуспешная оплата</span>
                                <span className="text-sm font-semibold text-ink">
                                    {card.card_product.decline_fee_usd ? `$${card.card_product.decline_fee_usd}` : '—'}
                                </span>
                            </div>
                            <div className="flex items-center justify-between border-t border-border py-3">
                                <span className="text-sm text-muted">Оплата в другой валюте</span>
                                <span className="text-sm font-semibold text-ink">
                                    {card.card_product.non_usd_payment_fee || '—'}
                                </span>
                            </div>
                            <div className="flex items-center justify-between border-t border-border py-3">
                                <span className="text-sm text-muted">Попытка оплаты запрещённой площадки</span>
                                <span className="text-sm font-semibold text-ink">
                                    {card.card_product.risk_operation_fee_usd ? `$${card.card_product.risk_operation_fee_usd}` : '—'}
                                </span>
                            </div>
                        </div>

                        <div className="flex flex-col gap-3 border-t border-border pt-5">
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

                        {card.card_product.restricted_merchants && (
                            <div className="flex flex-col gap-2 border-t border-border pt-5">
                                <h2 className="text-sm font-extrabold uppercase tracking-wide text-muted">
                                    Запрещённые магазины
                                </h2>
                                <div
                                    className="rich-text"
                                    dangerouslySetInnerHTML={{ __html: card.card_product.restricted_merchants }}
                                />
                            </div>
                        )}

                        {card.card_product.full_terms && (
                            <div className="flex flex-col gap-2 border-t border-border pt-5">
                                <h2 className="text-sm font-extrabold uppercase tracking-wide text-muted">
                                    Остальные условия
                                </h2>
                                <div
                                    className="rich-text"
                                    dangerouslySetInnerHTML={{ __html: card.card_product.full_terms }}
                                />
                            </div>
                        )}
                    </div>
                )}

                {activeTab === 'codes' && (
                    <>
                        <div className="mb-4 flex flex-wrap items-center justify-between gap-3">
                            <p className="text-xs text-muted">
                                {otpLoaded ? `${otpCodes.length} кодов` : <Skeleton className="h-3 w-20" />}
                            </p>
                            <button type="button" className="btn" onClick={loadOtpCodes} disabled={otpLoading}>
                                Обновить
                            </button>
                        </div>

                        {otpLoading && !otpLoaded ? (
                            <TransactionsSkeleton />
                        ) : otpCodes.length === 0 ? (
                            <p className="py-8 text-center text-sm text-muted">
                                Кодов 3DS-подтверждения пока не было.
                            </p>
                        ) : (
                            <div className="tx-list fade-in-up">
                                {groupByDate(otpCodes, (item) => item.occurred_at).map((group) => (
                                    <div key={group.title} className="tx-group">
                                        <div className="tx-group-title">{group.title}</div>
                                        {group.items.map((item, index) => (
                                            <OtpCodeRow key={`${item.occurred_at}-${index}`} item={item} />
                                        ))}
                                    </div>
                                ))}
                            </div>
                        )}
                    </>
                )}
            </div>
        </div>
    );
}

// Строка одного OTP(3DS)-кода — тот же визуальный язык, что и .tx-row в TransactionsTable
// (иконка-кружок слева, текст справа), но вместо суммы — сам код и кнопка копирования.
// CardsPro отдаёт только сам код и время выдачи — без привязки к конкретной операции/сумме.
function OtpCodeRow({ item }: { item: CardOtpCode }) {
    return (
        <div className="tx-row">
            <span className="tx-icon">
                <ShieldCheckIcon />
            </span>
            <span className="tx-info">
                <span className="tx-title">Код 3DS-подтверждения</span>
                <span className="tx-subtitle">{formatTime(item.occurred_at)}</span>
            </span>
            <span className="font-mono text-lg font-extrabold tracking-[0.2em] text-ink">{item.code}</span>
            <CopyButton value={item.code} />
        </div>
    );
}
