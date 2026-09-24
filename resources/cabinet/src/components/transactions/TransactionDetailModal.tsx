import type { ReactNode } from 'react';
import type { CardTransaction } from '../../api/types';
import { dateGroupTitle, formatTime } from '../../utils/dateGroups';
import { formatMoney } from '../../utils/format';
import { TRANSACTION_STATUS_LABELS, TRANSACTION_STATUS_TONES, TRANSACTION_TYPE_LABELS } from '../../utils/labels';
import { CopyButton } from '../common/CopyButton';
import { Modal } from '../common/Modal';
import { StatusPill } from '../common/StatusPill';
import { TxIcon } from './TxIcon';

interface TransactionDetailModalProps {
    tx: CardTransaction;
    onClose: () => void;
}

interface DetailRowProps {
    label: string;
    value: ReactNode;
    danger?: boolean;
    mono?: boolean;
    truncate?: boolean;
    copyValue?: string | null;
}

function DetailRow({ label, value, danger, mono, truncate, copyValue }: DetailRowProps) {
    return (
        <div className="tx-detail-row">
            <span className="tx-detail-row-label">{label}</span>
            <span
                className={`tx-detail-row-value${mono ? ' font-mono' : ''}${truncate ? ' truncate' : ''}${danger ? ' tx-amount-danger' : ''}`}
            >
                {value}
            </span>
            {copyValue !== undefined && <CopyButton value={copyValue} />}
        </div>
    );
}

/**
 * Попап детализации операции — открывается кликом по строке в TransactionsTable.
 * Тот же .modal-overlay/.modal-sheet, что и «Пополнить карту» (заблюренный фон,
 * нижний лист на мобильных / центрированный диалог на десктопе). Содержимое —
 * компактная «квитанция»: крупная иконка+сумма+статус сверху, ниже список
 * реквизитов операции — всё должно помещаться без скролла и на десктопе, и на
 * мобилке, поэтому строк намеренно немного и каждая в одну строку.
 */
export function TransactionDetailModal({ tx, onClose }: TransactionDetailModalProps) {
    const typeLabel = TRANSACTION_TYPE_LABELS[tx.type];
    const isDeclined = tx.status === 'declined';
    const isSuccessTopup = tx.type === 'topup' && tx.status === 'success';
    const merchant = isDeclined ? undefined : tx.merchant_info;
    const merchantName = merchant?.name ?? tx.merchant;
    const title = merchantName ?? typeLabel;
    // Сырое описание от провайдера показываем отдельной строкой только если оно
    // содержательно отличается от заголовка (например, мерчант найден по общему
    // названию, а в описании есть номер точки/город) — не дублируем очевидное.
    const rawDescription = tx.merchant && tx.merchant !== title ? tx.merchant : null;
    const commission = tx.commission_amount ?? tx.decline_fee;

    return (
        <Modal title="Операция" onClose={onClose}>
            <div className="tx-detail">
                <div className="tx-detail-hero">
                    <TxIcon tx={tx} size="lg" />
                    <p className={`tx-detail-amount${isSuccessTopup ? ' tx-amount-success' : ''}${isDeclined ? ' tx-amount-danger' : ''}`}>
                        {formatMoney(tx.amount, tx.currency)}
                    </p>
                    <p className="tx-detail-title">{title}</p>
                    <StatusPill
                        label={TRANSACTION_STATUS_LABELS[tx.status]}
                        tone={TRANSACTION_STATUS_TONES[tx.status]}
                        loading={tx.status === 'pending'}
                    />
                </div>

                <div className="tx-detail-rows">
                    <DetailRow label="Дата и время" value={`${dateGroupTitle(tx.occurred_at)}, ${formatTime(tx.occurred_at)}`} />
                    <DetailRow label="Тип операции" value={typeLabel} />
                    <DetailRow label="Карта" value={`${tx.card.product_name} •• ${tx.card.last4 ?? '••••'}`} />
                    {rawDescription && <DetailRow label="Описание" value={rawDescription} />}
                    {commission && <DetailRow label="Комиссия за операцию" value={formatMoney(commission, tx.currency)} />}
                    {isDeclined && tx.decline_reason && (
                        <DetailRow label="Причина отклонения" value={tx.decline_reason} danger />
                    )}
                    <DetailRow label="ID транзакции" value={tx.id} mono truncate copyValue={tx.id} />
                </div>
            </div>
        </Modal>
    );
}
