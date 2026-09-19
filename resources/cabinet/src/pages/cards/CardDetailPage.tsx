import { useEffect, useMemo, useState } from 'react';
import { Link, useParams, useSearchParams } from 'react-router-dom';
import { fetchCard, fetchCardRequisites, fetchCards } from '../../api/cards';
import { extractErrorMessage } from '../../api/client';
import { fetchCardTransactions } from '../../api/transactions';
import type { Card, CardDetail, CardRequisites, CardTransaction } from '../../api/types';
import { BalancePanel } from '../../components/cards/BalancePanel';
import { CardFace } from '../../components/cards/CardFace';
import { CardTabsSection, type CardTabKey } from '../../components/cards/CardTabsSection';
import { CardsSidebar } from '../../components/cards/CardsSidebar';
import { RequisitesPanel } from '../../components/cards/RequisitesPanel';
import { TopupModal } from '../../components/cards/TopupModal';
import { useAuth } from '../../context/AuthContext';
import { startOfMonth, sumSuccessfulPurchases } from '../../utils/format';
import { transliterateFio } from '../../utils/masks';

// Полная информация об одной карте: слева — визуал карты с переключателем
// лицевой/оборотной стороны и виджет баланса, справа — реквизиты для оплаты и
// платёжный адрес, ниже — вкладки с историей операций/лимитов. Левый
// сайдбар «Мои карты» — тот же список, что и на главной странице.
export function CardDetailPage() {
    const { id } = useParams<{ id: string }>();
    const { profile } = useAuth();
    const [searchParams, setSearchParams] = useSearchParams();

    const [cards, setCards] = useState<Card[]>([]);
    const [cardsLoading, setCardsLoading] = useState(true);

    const [card, setCard] = useState<CardDetail | null>(null);
    const [cardLoading, setCardLoading] = useState(true);
    const [cardError, setCardError] = useState<string | null>(null);

    const [flipped, setFlipped] = useState(false);
    const [requisites, setRequisites] = useState<CardRequisites | null>(null);
    const [requisitesLoading, setRequisitesLoading] = useState(false);
    const [requisitesError, setRequisitesError] = useState<string | null>(null);
    const [showCvv, setShowCvv] = useState(false);

    const [monthPurchases, setMonthPurchases] = useState<CardTransaction[] | null>(null);
    const [activeTab, setActiveTab] = useState<CardTabKey>('transactions');
    const [topupModalOpen, setTopupModalOpen] = useState(false);

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
        setRequisites(null);
        setShowCvv(false);
        setFlipped(false);
        setMonthPurchases(null);
        setActiveTab('transactions');

        fetchCard(id)
            .then(setCard)
            .catch((error) => setCardError(extractErrorMessage(error, 'Не удалось загрузить карту.')))
            .finally(() => setCardLoading(false));
    }, [id]);

    // Ссылка с ?topup=1 (нижнее меню «Пополнить» → TopupEntryPage) — автоматически
    // открывает модалку пополнения, как только карта загружена и активна, и сразу
    // убирает параметр из URL, чтобы он не открывался повторно при обновлении страницы.
    useEffect(() => {
        if (card?.status === 'active' && searchParams.get('topup') === '1') {
            setTopupModalOpen(true);
            setSearchParams((params) => {
                params.delete('topup');
                return params;
            }, { replace: true });
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [card?.status, card?.id]);

    // Полный номер подгружается автоматически при открытии страницы — без отдельной кнопки-гейта,
    // так как он нужен для оплаты в интернете. CVV остаётся под отдельным затвором —
    // его запрашивает handleShowCvv() через тот же ensureRequisites().
    useEffect(() => {
        if (id) {
            ensureRequisites();
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [id]);

    useEffect(() => {
        if (!id || !card || card.status !== 'active') {
            return;
        }

        fetchCardTransactions(id, { type: 'purchase', date_from: startOfMonth(), per_page: 100 })
            .then((response) => setMonthPurchases(response.data))
            .catch(() => setMonthPurchases([]));
    }, [id, card?.status]);

    const monthTotal = useMemo(
        () => (monthPurchases ? sumSuccessfulPurchases(monthPurchases) : null),
        [monthPurchases],
    );

    const cardholderName = useMemo(() => {
        if (!profile) {
            return '';
        }

        return transliterateFio(`${profile.first_name} ${profile.last_name}`).toUpperCase();
    }, [profile]);

    async function ensureRequisites(): Promise<CardRequisites | null> {
        if (requisites) {
            return requisites;
        }

        if (!id) {
            return null;
        }

        setRequisitesLoading(true);
        setRequisitesError(null);

        try {
            const data = await fetchCardRequisites(id);
            setRequisites(data);
            return data;
        } catch (error) {
            setRequisitesError(extractErrorMessage(error, 'Не удалось загрузить реквизиты карты.'));
            return null;
        } finally {
            setRequisitesLoading(false);
        }
    }

    async function handleShowCvv() {
        if (await ensureRequisites()) {
            setShowCvv(true);
        }
    }

    function handleViewTransactions() {
        setActiveTab('transactions');
        document.getElementById('card-tabs-section')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    return (
        <div className="flex flex-col gap-8 lg:flex-row">
            <CardsSidebar cards={cards} isLoading={cardsLoading} />

            <div className="flex flex-1 flex-col gap-6">
                <div>
                    <Link to="/cards" className="text-sm font-semibold text-muted hover:text-ink">
                        ← Мои карты
                    </Link>
                </div>

                {cardLoading && <p className="py-8 text-center text-sm text-muted">Загрузка…</p>}

                {!cardLoading && cardError && <p className="form-error-banner">{cardError}</p>}

                {!cardLoading && card && (
                    <>
                        <div className="grid gap-6 lg:grid-cols-2">
                            <div className="flex flex-col gap-4">
                                <CardFace
                                    flipped={flipped}
                                    onFlip={() => setFlipped((value) => !value)}
                                    productName={card.card_product.name}
                                    subtitle={null}
                                    maskedNumber={`•••• •••• •••• ${card.card_last4 ?? '••••'}`}
                                    fullNumber={requisites?.card_number ?? null}
                                    expiry={card.expiry}
                                    cardholderName={cardholderName}
                                    cvv={requisites?.cvv ?? null}
                                    showCvv={showCvv}
                                />

                                <BalancePanel
                                    card={card}
                                    monthTotal={monthTotal}
                                    onViewTransactions={handleViewTransactions}
                                    onTopupClick={() => setTopupModalOpen(true)}
                                />
                            </div>

                            <div className="flex flex-col gap-2">
                                <RequisitesPanel
                                    card={card}
                                    cardholderName={cardholderName}
                                    requisites={requisites}
                                    requisitesLoading={requisitesLoading}
                                    showCvv={showCvv}
                                    onShowCvv={handleShowCvv}
                                />
                                {requisitesError && <p className="form-error-banner">{requisitesError}</p>}
                            </div>
                        </div>

                        <CardTabsSection card={card} activeTab={activeTab} onTabChange={setActiveTab} />

                        {topupModalOpen && <TopupModal card={card} onClose={() => setTopupModalOpen(false)} />}
                    </>
                )}
            </div>
        </div>
    );
}
