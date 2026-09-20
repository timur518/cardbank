import { useEffect, useState } from 'react';
import { Link, Navigate, useNavigate } from 'react-router-dom';
import { fetchCards } from '../../api/cards';
import type { Card } from '../../api/types';
import { CardThumbnail } from '../../components/cards/CardThumbnail';
import { TopupCardsSkeleton } from '../../components/common/Skeleton';
import { formatMoney } from '../../utils/format';

/**
 * Точка входа для «Пополнить» (нижнее меню на мобильных, MobileTabBar) — сама
 * по себе не форма, а маршрутизатор к пополнению конкретной карты (форма
 * пополнения общая для всех карт, TopupModal, и открывается на странице
 * карты). Если активна ровно одна карта — сразу переходим в неё с ?topup=1,
 * что открывает модалку автоматически (см. CardDetailPage). Если карт
 * несколько — даём выбрать; если активных карт нет — предлагаем выпустить.
 */
export function TopupEntryPage() {
    const navigate = useNavigate();
    const [cards, setCards] = useState<Card[] | null>(null);

    useEffect(() => {
        fetchCards().then(setCards);
    }, []);

    const activeCards = cards?.filter((card) => card.status === 'active') ?? null;

    useEffect(() => {
        if (activeCards && activeCards.length === 1) {
            navigate(`/cards/${activeCards[0].id}?topup=1`, { replace: true });
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [activeCards]);

    if (activeCards === null) {
        return (
            <div className="flex flex-col gap-6">
                <h1 className="text-2xl font-extrabold tracking-tight text-ink">Пополнить карту</h1>
                <TopupCardsSkeleton />
            </div>
        );
    }

    if (activeCards.length === 1) {
        return <Navigate to={`/cards/${activeCards[0].id}?topup=1`} replace />;
    }

    if (activeCards.length === 0) {
        return (
            <div className="flex flex-col gap-6">
                <h1 className="text-2xl font-extrabold tracking-tight text-ink">Пополнить карту</h1>
                <div className="auth-panel flex flex-col items-center gap-4 p-8 text-center fade-in-up">
                    <p className="text-sm text-muted">Нет активных карт для пополнения.</p>
                    <Link to="/cards/new" className="btn btn-primary">
                        Выпустить карту
                    </Link>
                </div>
            </div>
        );
    }

    return (
        <div className="flex flex-col gap-6">
            <h1 className="text-2xl font-extrabold tracking-tight text-ink">Выберите карту для пополнения</h1>

            <div className="flex flex-col gap-3 fade-in-up">
                {activeCards.map((card) => (
                    <Link
                        key={card.id}
                        to={`/cards/${card.id}?topup=1`}
                        className="flex items-center justify-between gap-3 rounded-2xl bg-surface px-4 py-3 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
                    >
                        <div className="flex items-center gap-3">
                            <CardThumbnail skin={card.card_product.skin} name={card.card_product.name} />
                            <div>
                                <p className="text-sm font-semibold text-ink">{card.card_product.name}</p>
                                <p className="text-xs text-muted">•••• {card.card_last4 ?? '••••'}</p>
                            </div>
                        </div>
                        <p className="text-sm font-extrabold text-ink">{formatMoney(card.balance, card.currency)}</p>
                    </Link>
                ))}
            </div>
        </div>
    );
}
