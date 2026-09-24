import type { CardTransaction } from '../../api/types';
import { DeclineIcon } from '../common/Icons';
import { TRANSACTION_TYPE_ICONS } from '../../utils/labels';

interface TxIconProps {
    tx: CardTransaction;
    /** lg — крупный кружок для шапки попапа детализации (TransactionDetailModal), sm (по умолчанию) — строка списка (TransactionsTable). */
    size?: 'sm' | 'lg';
}

/** Кружок с иконкой операции — лого мерчанта из справочника (если найден), иначе первая
 * буква названия или общая иконка типа операции; для отклонённых всегда красный крестик.
 * Общая логика для строки списка (TransactionsTable) и шапки попапа детализации. */
export function TxIcon({ tx, size = 'sm' }: TxIconProps) {
    const isDeclined = tx.status === 'declined';
    const merchant = isDeclined ? undefined : tx.merchant_info;
    const Icon = TRANSACTION_TYPE_ICONS[tx.type];

    return (
        <span
            className={`tx-icon${size === 'lg' ? ' tx-icon-lg' : ''}${isDeclined ? ' tx-icon-danger' : ''}`}
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
    );
}
