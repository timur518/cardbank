import { useEffect, useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import { fetchCard, fetchCards } from '../../api/cards';
import { extractErrorMessage } from '../../api/client';
import { fetchCardTransactions } from '../../api/transactions';
import type { Card, CardDetail, CardTransaction, PaginationMeta } from '../../api/types';
import { CardArtwork } from '../../components/cards/CardArtwork';
import { CardsSidebar } from '../../components/cards/CardsSidebar';
import { StatusPill } from '../../components/common/StatusPill';
import { TransactionsTable } from '../../components/transactions/TransactionsTable';
import { CARD_STATUS_LABELS, CARD_STATUS_TONES } from '../../utils/labels';
import { formatMoney, formatRub, formatDateTime } from '../../utils/format';

const TRANSACTIONS_PER_PAGE = 15;

interface InfoRowProps {
    label: string;
    value: string;
}

function InfoRow({ label, value }: InfoRowProps) {
    return (
        <div className="flex items-center justify-between gap-4 border-t border-border py-3 first:border-t-0 first:pt-0">
            <span className="text-sm text-muted">{label}</span>
            <span className="text-sm font-semibold text-ink">{value}</span>
        </div>
    );
}

// Полная информация об одной карте: реквизиты, платёжный адрес и история операций
// по ней. Левый сайдбар «Мои карты» — тот же список, что и на главной странице.
export function CardDetailPage() {
    const { id } = useParams<{ id: string }>();

    const [cards, setCards] = useState<Card[]>([]);
    const [cardsLoading, setCardsLoading] = useState(true);

    const [card, setCard] = useState<CardDetail | null>(null);
    const [cardLoading, setCardLoading] = useState(true);
    const [cardError, setCardError] = useState<string | null>(null);

    const [transactions, setTransactions] = useState<CardTransaction[]>([]);
    const [transactionsLoading, setTransactionsLoading] = useState(true);
    const [meta, setMeta] = useState<PaginationMeta | null>(null);
    const [page, setPage] = useState(1);

    useEffect(() => {
        fetchCards()
            .then(setCards)
            .finally(() => setCardsLoading(false));
    }, []);

    useEffect(() => {
        if (!id) {
            return;
        }

        setCardLoading(true);
        setCardError(null);

        fetchCard(id)
            .then(setCard)
            .catch((error) => setCardError(extractErrorMessage(error, 'Не удалось загрузить карту.')))
            .finally(() => setCardLoading(false));
    }, [id]);

    useEffect(() => {
        setPage(1);
    }, [id]);

    useEffect(() => {
        if (!id) {
            return;
        }

        setTransactionsLoading(true);

        fetchCardTransactions(id, { page, per_page: TRANSACTIONS_PER_PAGE })
            .then((response) => {
                setTransactions(response.data);
                setMeta(response.meta);
            })
            .finally(() => setTransactionsLoading(false));
    }, [id, page]);

    return (
        <div className="flex flex-col gap-8 lg:flex-row">
            <CardsSidebar cards={cards} isLoading={cardsLoading} />

            <div className="flex flex-1 flex-col gap-8">
                <div>
                    <Link to="/cards" className="text-sm font-semibold text-muted hover:text-ink">
                        ← Мои карты
                    </Link>
                </div>

                {cardLoading && <p className="py-8 text-center text-sm text-muted">Загрузка…</p>}

                {!cardLoading && cardError && <p className="form-error-banner">{cardError}</p>}

                {!cardLoading && card && (
                    <>
                        <div className="auth-panel flex flex-col gap-6 p-6 sm:flex-row sm:items-start">
                            <CardArtwork
                                skin={card.card_product.skin}
                                productName={card.card_product.name}
                                last4={card.card_last4}
                            />

                            <div className="flex-1">
                                <div className="mb-4 flex flex-wrap items-center gap-3">
                                    <h1 className="text-xl font-extrabold tracking-tight text-ink">
                                        Карта {card.card_product.name}
                                    </h1>
                                    <StatusPill label={CARD_STATUS_LABELS[card.status]} tone={CARD_STATUS_TONES[card.status]} />
                                </div>

                                <InfoRow label="Номер карты" value={`•••• •••• •••• ${card.card_last4 ?? '••••'}`} />
                                <InfoRow label="Срок действия" value={card.expiry ?? '—'} />
                                <InfoRow label="Валюта" value={card.currency} />
                                <InfoRow label="Баланс" value={formatMoney(card.balance, card.currency)} />
                                <InfoRow label="Стоимость выпуска" value={formatRub(Number(card.price_rub))} />
                                <InfoRow
                                    label="Дата выпуска"
                                    value={card.issued_at ? formatDateTime(card.issued_at) : '—'}
                                />

                                {(card.billing_address.country ||
                                    card.billing_address.city ||
                                    card.billing_address.address) && (
                                    <>
                                        <h2 className="mt-6 mb-2 text-sm font-extrabold uppercase tracking-wide text-muted">
                                            Платёжный адрес
                                        </h2>
                                        <InfoRow label="Страна" value={card.billing_address.country ?? '—'} />
                                        <InfoRow label="Регион" value={card.billing_address.region ?? '—'} />
                                        <InfoRow label="Город" value={card.billing_address.city ?? '—'} />
                                        <InfoRow label="Адрес" value={card.billing_address.address ?? '—'} />
                                        <InfoRow label="Индекс" value={card.billing_address.post_code ?? '—'} />
                                    </>
                                )}
                            </div>
                        </div>

                        <div className="auth-panel p-6">
                            <h2 className="mb-4 text-sm font-extrabold uppercase tracking-wide text-muted">
                                История операций по карте
                            </h2>

                            {transactionsLoading ? (
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
                    </>
                )}
            </div>
        </div>
    );
}
