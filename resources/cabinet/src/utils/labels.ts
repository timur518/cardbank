import type { CardStatus, CardTransactionStatus, CardTransactionType } from '../api/types';
import type { StatusTone } from '../components/common/StatusPill';

// Зеркалит getLabel() соответствующих PHP-энамов (CardStatus, CardTransactionType,
// CardTransactionStatus), чтобы в ЛК показывались те же формулировки, что и в админке.

export const CARD_STATUS_LABELS: Record<CardStatus, string> = {
    waiting: 'Ожидает оплаты',
    pending: 'В процессе выпуска',
    active: 'Активна',
    frozen: 'Заморожена',
    closed: 'Закрыта',
    cancelled: 'Отменена',
    failed: 'Ошибка выпуска',
};

export const TRANSACTION_TYPE_LABELS: Record<CardTransactionType, string> = {
    purchase: 'Покупка',
    topup: 'Пополнение',
    fee: 'Комиссия',
    refund: 'Возврат',
    decline: 'Отклонённый платёж',
};

export const TRANSACTION_STATUS_LABELS: Record<CardTransactionStatus, string> = {
    pending: 'В обработке',
    success: 'Успешно',
    declined: 'Отклонена',
    reversed: 'Возвращена',
};

export const CARD_STATUS_TONES: Record<CardStatus, StatusTone> = {
    active: 'success',
    waiting: 'warning',
    pending: 'warning',
    frozen: 'warning',
    closed: 'gray',
    cancelled: 'gray',
    failed: 'danger',
};

export const TRANSACTION_STATUS_TONES: Record<CardTransactionStatus, StatusTone> = {
    success: 'success',
    pending: 'warning',
    declined: 'danger',
    reversed: 'gray',
};
