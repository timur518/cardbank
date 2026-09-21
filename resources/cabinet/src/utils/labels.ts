import type { AppNotificationType, CardStatus, CardTransactionStatus, CardTransactionType } from '../api/types';
import type { ComponentType } from 'react';
import {
    CardNotificationIcon,
    DeclineIcon,
    FeeIcon,
    PaymentNotificationIcon,
    PromoNotificationIcon,
    PurchaseIcon,
    RefundIcon,
    SecurityNotificationIcon,
    SystemNotificationIcon,
    TopupTxIcon,
} from '../components/common/Icons';
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

// Иконка строки в списке операций (TransactionsTable) — подбирается по CardTransactionType.
export const TRANSACTION_TYPE_ICONS: Record<CardTransactionType, ComponentType> = {
    purchase: PurchaseIcon,
    topup: TopupTxIcon,
    fee: FeeIcon,
    refund: RefundIcon,
    decline: DeclineIcon,
};

// Зеркалит getLabel() энама App\Enums\NotificationType на бэкенде.
export const NOTIFICATION_TYPE_LABELS: Record<AppNotificationType, string> = {
    system: 'Системное',
    card: 'Карта',
    payment: 'Платежи',
    security: 'Безопасность',
    promo: 'Акции и предложения',
};

// Иконка в кружочке слева от уведомления (NotificationsPanel) — по AppNotificationType.
export const NOTIFICATION_TYPE_ICONS: Record<AppNotificationType, ComponentType> = {
    system: SystemNotificationIcon,
    card: CardNotificationIcon,
    payment: PaymentNotificationIcon,
    security: SecurityNotificationIcon,
    promo: PromoNotificationIcon,
};

// Цвет кружочка с иконкой — зеркалит getColor() того же энама (классы .notif-icon-* в index.css).
export const NOTIFICATION_TYPE_ICON_CLASSES: Record<AppNotificationType, string> = {
    system: 'notif-icon-gray',
    card: 'notif-icon-brand',
    payment: 'notif-icon-success',
    security: 'notif-icon-danger',
    promo: 'notif-icon-warning',
};
