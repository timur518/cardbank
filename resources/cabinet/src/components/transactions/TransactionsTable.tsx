import type { CardTransaction } from '../../api/types';
import { formatDateTime, formatMoney } from '../../utils/format';
import { TRANSACTION_STATUS_LABELS, TRANSACTION_STATUS_TONES, TRANSACTION_TYPE_LABELS } from '../../utils/labels';
import { StatusPill } from '../common/StatusPill';

interface TransactionsTableProps {
    transactions: CardTransaction[];
    emptyMessage?: string;
}

// Таблица операций по картам — переиспользуется и в блоке «последние операции» на
// главной странице, и на полной странице «Операции».
export function TransactionsTable({ transactions, emptyMessage = 'Операций пока нет.' }: TransactionsTableProps) {
    if (transactions.length === 0) {
        return <p className="py-8 text-center text-sm text-muted">{emptyMessage}</p>;
    }

    return (
        <div className="overflow-x-auto">
            <table className="data-table">
                <thead>
                    <tr>
                        <th>Дата</th>
                        <th>Тип</th>
                        <th>Продавец</th>
                        <th>Сумма</th>
                        <th>Статус</th>
                    </tr>
                </thead>
                <tbody>
                    {transactions.map((tx) => (
                        <tr key={tx.id}>
                            <td className="whitespace-nowrap">{formatDateTime(tx.occurred_at)}</td>
                            <td>{TRANSACTION_TYPE_LABELS[tx.type]}</td>
                            <td>{tx.merchant ?? '—'}</td>
                            <td className="font-semibold whitespace-nowrap">{formatMoney(tx.amount, tx.currency)}</td>
                            <td>
                                <StatusPill
                                    label={TRANSACTION_STATUS_LABELS[tx.status]}
                                    tone={TRANSACTION_STATUS_TONES[tx.status]}
                                />
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
}
