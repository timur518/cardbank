import type { CardDetail } from '../../api/types';
import { formatBalanceHero, formatMoney } from '../../utils/format';
import { CARD_STATUS_LABELS, CARD_STATUS_TONES } from '../../utils/labels';
import { StatusPill } from '../common/StatusPill';

interface BalancePanelProps {
    card: CardDetail;
    monthTotal: number | null;
    onViewTransactions: () => void;
    onTopupClick: () => void;
}

// Виджет баланса на странице карты: сумма (в одной строке с кнопкой пополнения)
// и потраченное в этом месяце (сумма покупок с начала месяца — считается по
// CardDetailPage). Для карт вне статуса Active баланс пополнять нельзя — вместо
// суммы с кнопкой показывается статус.
export function BalancePanel({ card, monthTotal, onViewTransactions, onTopupClick }: BalancePanelProps) {
    const isActive = card.status === 'active';

    return (
        <div className="auth-panel p-6">
            <div className="flex items-center justify-between">
                <span className="text-xs font-extrabold uppercase tracking-wide text-muted">Баланс карты</span>
                <span className="rounded-full bg-[#ece8e4] px-3 py-1 text-xs font-bold text-muted">{card.currency}</span>
            </div>

            {isActive ? (
                <div className="mt-2 flex items-center justify-between gap-4">
                    <p className="text-4xl font-black tracking-tight text-ink">
                        {formatBalanceHero(card.balance, card.currency)}
                    </p>
                    <button type="button" className="btn btn-topup shrink-0" onClick={onTopupClick}>
                        Пополнить баланс
                    </button>
                </div>
            ) : (
                <div className="mt-3">
                    <StatusPill label={CARD_STATUS_LABELS[card.status]} tone={CARD_STATUS_TONES[card.status]} />
                </div>
            )}

            {isActive && (
                <div className="mt-5 flex items-center justify-between border-t border-border pt-4">
                    <div>
                        <p className="text-sm font-semibold text-ink">Потрачено в этом месяце</p>
                        <button type="button" className="text-xs font-bold text-orange-dark hover:underline" onClick={onViewTransactions}>
                            Подробнее →
                        </button>
                    </div>
                    <p className="text-lg font-extrabold text-ink">
                        {monthTotal === null ? '…' : formatMoney(monthTotal, card.currency)}
                    </p>
                </div>
            )}
        </div>
    );
}
