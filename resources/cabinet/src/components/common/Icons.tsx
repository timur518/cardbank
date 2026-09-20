/**
 * Общий набор SVG-иконок приложения — единый стиль (viewBox 24×24, stroke
 * currentColor, скруглённые концы линий) переиспользуется и в нижнем
 * мобильном меню (MobileTabBar), и в кнопках главного экрана (DashboardPage),
 * чтобы не дублировать разметку и не расходиться в толщине линий/пропорциях.
 */

export function HomeIcon() {
    return (
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={1.8}>
            <path strokeLinecap="round" strokeLinejoin="round" d="M4 11.5 12 4l8 7.5" />
            <path strokeLinecap="round" strokeLinejoin="round" d="M6 10v9a1 1 0 0 0 1 1h3v-5a2 2 0 0 1 4 0v5h3a1 1 0 0 0 1-1v-9" />
        </svg>
    );
}

export function CardsIcon() {
    return (
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={1.8}>
            <rect x="2.5" y="5" width="19" height="14" rx="2.5" />
            <path strokeLinecap="round" d="M2.5 9.5h19" />
        </svg>
    );
}

export function HistoryIcon() {
    return (
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={1.8}>
            <circle cx="12" cy="13" r="8" />
            <path strokeLinecap="round" strokeLinejoin="round" d="M12 9v4l3 2" />
            <path strokeLinecap="round" d="M9 3h6" />
        </svg>
    );
}

export function ProfileIcon() {
    return (
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={1.8}>
            <circle cx="12" cy="8" r="3.5" />
            <path strokeLinecap="round" d="M4.5 20c1.4-4 4.2-6 7.5-6s6.1 2 7.5 6" />
        </svg>
    );
}

export function PlusIcon() {
    return (
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={2.2}>
            <path strokeLinecap="round" d="M12 5v14M5 12h14" />
        </svg>
    );
}

// Новая карта — плюс поверх карты вместо просто плюса, чтобы визуально
// отличаться от кнопки «Пополнить» с той же PlusIcon.
export function NewCardIcon() {
    return (
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={1.8}>
            <rect x="2.5" y="5" width="19" height="14" rx="2.5" />
            <path strokeLinecap="round" d="M2.5 9.5h19" />
            <path strokeLinecap="round" d="M12 12.5v5M9.5 15h5" />
        </svg>
    );
}

// Далее — иконки для строк истории операций (TransactionsTable) — по одной на каждый
// CardTransactionType (purchase/topup/fee/refund/decline) и отдельно ClockIcon для
// пометки операций в статусе pending («в обработке на стороне эмитента»).

export function PurchaseIcon() {
    return (
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={1.8}>
            <path strokeLinecap="round" strokeLinejoin="round" d="M6 8h12l-1 12a2 2 0 0 1-2 2H9a2 2 0 0 1-2-2L6 8Z" />
            <path strokeLinecap="round" strokeLinejoin="round" d="M9 8V6a3 3 0 0 1 6 0v2" />
        </svg>
    );
}

export function TopupTxIcon() {
    return (
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={1.8}>
            <path strokeLinecap="round" strokeLinejoin="round" d="M12 5v14" />
            <path strokeLinecap="round" strokeLinejoin="round" d="M6 13l6 6 6-6" />
        </svg>
    );
}

export function FeeIcon() {
    return (
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={1.8}>
            <circle cx="12" cy="12" r="9" />
            <path strokeLinecap="round" d="M9 15 15 9" />
            <circle cx="9.5" cy="9.5" r="1" fill="currentColor" stroke="none" />
            <circle cx="14.5" cy="14.5" r="1" fill="currentColor" stroke="none" />
        </svg>
    );
}

export function RefundIcon() {
    return (
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={1.8}>
            <path strokeLinecap="round" strokeLinejoin="round" d="M8 7 4 11l4 4" />
            <path strokeLinecap="round" strokeLinejoin="round" d="M4 11h11a5 5 0 0 1 0 10h-2" />
        </svg>
    );
}

export function DeclineIcon() {
    return (
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={1.8}>
            <circle cx="12" cy="12" r="9" />
            <path strokeLinecap="round" d="M9.5 9.5l5 5M14.5 9.5l-5 5" />
        </svg>
    );
}

export function ClockIcon() {
    return (
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={1.8}>
            <circle cx="12" cy="12" r="9" />
            <path strokeLinecap="round" strokeLinejoin="round" d="M12 7v5l3.5 2" />
        </svg>
    );
}
