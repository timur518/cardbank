/**
 * Единая точка входа для иконок нижнего меню/главного экрана/истории операций —
 * все иконки взяты из пакета heroicons (24/outline), просто реэкспортированы
 * под именами по смыслу использования в приложении, чтобы код-потребители
 * (MobileTabBar, DashboardPage, TransactionsTable через labels.ts) не менялись.
 */
export {
    HomeIcon,
    CreditCardIcon as CardsIcon,
    ClockIcon as HistoryIcon,
    UserIcon as ProfileIcon,
    PlusIcon,
    // Новая карта — иконка банковской карты (в heroicons нет варианта с двумя картами,
    // только одна CreditCardIcon — та же, что и у «Мои карты»/реквизитах, это нормально).
    CreditCardIcon as NewCardIcon,
    // Иконки строк истории операций (TransactionsTable) — по одной на CardTransactionType.
    ShoppingBagIcon as PurchaseIcon,
    ArrowDownIcon as TopupTxIcon,
    ReceiptPercentIcon as FeeIcon,
    ReceiptRefundIcon as RefundIcon,
    XCircleIcon as DeclineIcon,
    // Пометка операций в статусе pending («в обработке на стороне эмитента»).
    ClockIcon,
} from '@heroicons/react/24/outline';
