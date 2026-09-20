import type { CardTransaction } from '../../api/types';
import { ClockIcon } from '../common/Icons';
import { formatMoney } from '../../utils/format';
import { TRANSACTION_TYPE_ICONS, TRANSACTION_TYPE_LABELS } from '../../utils/labels';

interface TransactionsTableProps {
    transactions: CardTransaction[];
    emptyMessage?: string;
}

const MONTHS = [
    'января', 'февраля', 'марта', 'апреля', 'мая', 'июня',
    'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря',
];

function isSameDay(a: Date, b: Date): boolean {
    return a.getFullYear() === b.getFullYear() && a.getMonth() === b.getMonth() && a.getDate() === b.getDate();
}

// Заголовок группы по дате выполнения операции: «Сегодня» / «Вчера» для последних
// суток, «ДД месяц» для остальных операций текущего года, «ДД месяц ГГГГ» — для
// операций прошлых лет.
function groupTitle(occurredAt: string): string {
    const date = new Date(occurredAt);
    const now = new Date();
    const yesterday = new Date(now);
    yesterday.setDate(now.getDate() - 1);

    if (isSameDay(date, now)) {
        return 'Сегодня';
    }
    if (isSameDay(date, yesterday)) {
        return 'Вчера';
    }

    const dayMonth = `${date.getDate()} ${MONTHS[date.getMonth()]}`;
    return date.getFullYear() === now.getFullYear() ? dayMonth : `${dayMonth} ${date.getFullYear()}`;
}

function formatTime(occurredAt: string): string {
    return new Date(occurredAt).toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit' });
}

interface TransactionGroup {
    title: string;
    items: CardTransaction[];
}

// Список операций уже приходит с бэкенда отсортированным по occurred_at по убыванию,
// поэтому группы формируются простым проходом по порядку — новая группа открывается
// при смене заголовка даты у соседних операций.
function groupTransactions(transactions: CardTransaction[]): TransactionGroup[] {
    const groups: TransactionGroup[] = [];

    for (const tx of transactions) {
        const title = groupTitle(tx.occurred_at);
        const current = groups[groups.length - 1];

        if (current && current.title === title) {
            current.items.push(tx);
        } else {
            groups.push({ title, items: [tx] });
        }
    }

    return groups;
}

// Список операций по картам — переиспользуется и в блоке «последние операции» на
// главной странице, и на полной странице «Операции», и во вкладке карты.
export function TransactionsTable({ transactions, emptyMessage = 'Операций пока нет.' }: TransactionsTableProps) {
    if (transactions.length === 0) {
        return <p className="py-8 text-center text-sm text-muted">{emptyMessage}</p>;
    }

    const groups = groupTransactions(transactions);

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
    const merchant = tx.merchant_info;
    const merchantName = merchant?.name ?? tx.merchant;
    const title = merchantName ?? typeLabel;

    return (
        <div className="tx-row">
            <span
                className="tx-icon"
                style={merchant ? { background: merchant.color ?? undefined, color: '#fff' } : undefined}
            >
                {merchant?.logo_svg ? (
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
                <span className="tx-amount">{formatMoney(tx.amount, tx.currency)}</span>
            </span>
        </div>
    );
}
