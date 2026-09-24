import { useState } from 'react';
import type { CardTransaction } from '../../api/types';
import { ClockIcon } from '../common/Icons';
import { formatMoney } from '../../utils/format';
import { groupByDate, formatTime } from '../../utils/dateGroups';
import { TRANSACTION_TYPE_LABELS } from '../../utils/labels';
import { TransactionDetailModal } from './TransactionDetailModal';
import { TxIcon } from './TxIcon';

interface TransactionsTableProps {
    transactions: CardTransaction[];
    emptyMessage?: string;
}

// Список операций по картам — переиспользуется и в блоке «последние операции» на
// главной странице, и на полной странице «Операции», и во вкладке карты. Каждая
// строка кликабельна — открывает попап детализации (TransactionDetailModal).
export function TransactionsTable({ transactions, emptyMessage = 'Операций пока нет.' }: TransactionsTableProps) {
    const [selected, setSelected] = useState<CardTransaction | null>(null);

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
                        <TransactionRow key={tx.id} tx={tx} onSelect={() => setSelected(tx)} />
                    ))}
                </div>
            ))}

            {selected && <TransactionDetailModal tx={selected} onClose={() => setSelected(null)} />}
        </div>
    );
}

function TransactionRow({ tx, onSelect }: { tx: CardTransaction; onSelect: () => void }) {
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
        <button type="button" className="tx-row" onClick={onSelect}>
            <TxIcon tx={tx} />
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
        </button>
    );
}
