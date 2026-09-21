import type { CardTransaction } from '../../api/types';
import { ClockIcon, DeclineIcon } from '../common/Icons';
import { formatMoney } from '../../utils/format';
import { groupByDate, formatTime } from '../../utils/dateGroups';
import { TRANSACTION_TYPE_ICONS, TRANSACTION_TYPE_LABELS } from '../../utils/labels';

interface TransactionsTableProps {
    transactions: CardTransaction[];
    emptyMessage?: string;
}

// Список операций по картам — переиспользуется и в блоке «последние операции» на
// главной странице, и на полной странице «Операции», и во вкладке карты.
export function TransactionsTable({ transactions, emptyMessage = 'Операций пока нет.' }: TransactionsTableProps) {
    if (transactions.length === 0) {
        return <p className="py-8 text-center text-sm text-muted">{emptyMessage}</p>;
    }

    const groups = groupByDate(transactions, (tx) => tx.occurred_at);

    return (
        <div className="tx-list">
            {groups.map((group) => (
                <div key={group.title} className="tx-group">
                    <div className="tx-group-title">{group.title}</div>
                    {group.items.map((tx) => (
                        <TransactionRow key={tx.id} tx={tx} />
                    ))}
                </div>
            ))}
        </div>
    );
}

function TransactionRow({ tx }: { tx: CardTransaction }) {
    const Icon = TRANSACTION_TYPE_ICONS[tx.type];
    const typeLabel = TRANSACTION_TYPE_LABELS[tx.type];
    const isPending = tx.status === 'pending';
    // Отклонённый платёж/пополнение (например, провайдер отклонил из-за
    // нехватки средств на мастер-балансе) — всегда красная иконка/сумма,
    // независимо от того, что это было — покупка или пополнение.
    const isDeclined = tx.status === 'declined';
    const isSuccessTopup = tx.type === 'topup' && tx.status === 'success';
    const merchant = isDeclined ? undefined : tx.merchant_info;
    const merchantName = merchant?.name ?? tx.merchant;
    const title = merchantName ?? typeLabel;

    return (
        <div className="tx-row">
            <span
                className={`tx-icon${isDeclined ? ' tx-icon-danger' : ''}`}
                style={merchant ? { background: merchant.color ?? undefined, color: '#fff' } : undefined}
            >
                {isDeclined ? (
                    <DeclineIcon />
                ) : merchant?.logo_svg ? (
                    <svg viewBox="0 0 24 24" fill="currentColor" dangerouslySetInnerHTML={{ __html: merchant.logo_svg }} />
                ) : merchant ? (
                    <span className="tx-icon-letter">{merchant.name.charAt(0).toUpperCase()}</span>
                ) : (
                    <Icon />
                )}
            </span>
            <span className="tx-info">
                <span className="tx-title">{title}</span>
                <span className="tx-subtitle">
                    {merchantName ? typeLabel : formatTime(tx.occurred_at)}
                </span>
            </span>
            <span className="tx-amount-wrap">
                {isPending && (
                    <span className="tx-pending-icon" title="В обработке на стороне эмитента">
                        <ClockIcon />
                    </span>
                )}
                <span
                    className={`tx-amount${isSuccessTopup ? ' tx-amount-success' : ''}${isDeclined ? ' tx-amount-danger' : ''}`}
                >
                    {formatMoney(tx.amount, tx.currency)}
                </span>
            </span>
        </div>
    );
}
