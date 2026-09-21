// Поля профиля.
export interface Profile {
    id: string;
    first_name: string;
    last_name: string;
    middle_name: string | null;
    phone: string;
    email: string;
    date_of_birth: string | null;
    kyc_status: string;
    two_factor_enabled: boolean;
    referral_code: string | null;
    created_at: string;
}

export interface LoginPayload {
    login: string;
    password: string;
}

// Обновить данные в профиле.
export interface UpdateProfilePayload {
    first_name: string;
    last_name: string;
    middle_name?: string;
    phone: string;
    date_of_birth: string;
}

// Смена пароля в личном кабинете, требует подтверждения текущим паролем.
export interface UpdatePasswordPayload {
    current_password: string;
    password: string;
    password_confirmation: string;
}

// Регистрация пользователя
export interface RegisterPayload {
    first_name: string;
    last_name: string;
    middle_name?: string;
    phone: string;
    email: string;
    date_of_birth: string;
    password: string;
    password_confirmation: string;
    personal_data_consent: boolean;
    referral_code?: string;
    utm_source?: string;
    utm_medium?: string;
    utm_campaign?: string;
    utm_content?: string;
}

// Карты клиента
export interface CardProductSummary {
    key: string;
    name: string;
    skin: string | null;
    topup_min_amount?: string;
    topup_max_amount?: string;
}

export type CardStatus = 'waiting' | 'pending' | 'active' | 'frozen' | 'closed' | 'cancelled' | 'failed';

// Информация о карте которая еще не выпущена.
export interface Card {
    // uuid, а не сквозной id в базе — чтобы номер карты не светился в URL ЛК.
    id: string;
    card_product: CardProductSummary;
    status: CardStatus;
    currency: string;
    balance: string;
    card_last4: string | null;
    expiry: string | null;
    issued_at: string | null;
    billing_address: BillingAddress;
}

// Платёжный адрес карты
export interface BillingAddress {
    country: string | null;
    city: string | null;
    region: string | null;
    address: string | null;
    post_code: string | null;
}

// Информация о карте
export interface CardDetail extends Card {
    price_rub: string;
}

// Реквизиты карты
export interface CardRequisites {
    card_number: string;
    expiry: string | null;
    cvv: string;
}

export type CardTransactionType = 'purchase' | 'topup' | 'fee' | 'refund' | 'decline';
export type CardTransactionStatus = 'pending' | 'success' | 'declined' | 'reversed';

// Мерчант из справочника админки, определённый автоматически по описанию операции
// (см. Merchant::matchByDescription() на бэкенде) — null, если совпадение не найдено.
export interface TransactionMerchantInfo {
    id: number;
    name: string;
    category: string;
    logo_svg: string | null;
    color: string | null;
}

// Транзакции по карте
export interface CardTransaction {
    // uuid, а не сквозные id в базе — аналогично Card.id.
    id: string;
    card_id: string;
    type: CardTransactionType;
    amount: string;
    currency: string;
    merchant: string | null;
    merchant_info: TransactionMerchantInfo | null;
    status: CardTransactionStatus;
    occurred_at: string;
}

export interface PaginationMeta {
    current_page: number;
    last_page: number;
    total: number;
    // Только в ответе GET /notifications — счётчик непрочитанных по всем записям, а не только по текущей странице.
    unread_count?: number;
}

export interface Paginated<T> {
    data: T[];
    meta: PaginationMeta;
}

// Настройки сайта
export interface BrandSettings {
    brand_domain: string | null;
    brand_site_name: string | null;
    brand_support_email: string | null;
    brand_logo: string | null;
    brand_theme_color: string | null;
}

// Настройки реферальной
export interface ReferralSettings {
    referral_issue_rate: string | null;
    referral_topup_rate: string | null;
    referral_hold_days: string | null;
    referral_min_wallet_rub: string | null;
    referral_min_bank_rub: string | null;
}

// Текщие ставки курсов валют
export type CurrencyCode = 'usd' | 'eur' | 'gbp';
export type CurrencyRates = Record<CurrencyCode, string>;

// Информация о карточном продукте
export interface CardProduct {
    id: number;
    key: string;
    name: string;
    description: string | null;
    skin: string | null;
    currency: string;
    price_rub: string;
    provider_kyc_required: boolean;
    apple_pay_enabled: boolean;
    google_pay_enabled: boolean;
    issue_min_amount: string | null;
    issue_max_amount: string | null;
    topup_min_amount: string | null;
    topup_max_amount: string | null;
    coming_soon: boolean;
}

export type PaymentMethodType = 'gateway' | 'crypto' | 'card' | 'wallet';

// Платежные методы
export interface PaymentMethod {
    id: number;
    name: string;
    type: PaymentMethodType;
    currency: string;
    min_amount: string;
    max_amount: string;
}

export interface IssueOrderPayload {
    card_product_id: number;
    topup_amount: number;
    topup_currency: 'USD' | 'RUB';
    payment_method_id: number;
    idempotency_key: string;
}

// Результат выпуска карты
export interface IssueOrderResult {
    card_id: number;
    status: CardStatus;
    price_rub: string;
    topup_usd: string;
    topup_rub: string;
    total_rub: string;
    payment_transaction_id: string | null;
    payment_url: string | null;
    idempotency_key: string;
}

export interface TopupOrderPayload {
    card_id: string;
    amount: number;
    currency: 'USD' | 'RUB';
    payment_method_id: number;
    idempotency_key: string;
}

// Результат пополнения карты
export interface TopupOrderResult {
    card_id: number;
    topup_usd: string;
    topup_rub: string;
    total_rub: string;
    payment_transaction_id: string | null;
    payment_url: string | null;
    idempotency_key: string;
}

// Категория уведомления — зеркалит App\Enums\NotificationType на бэкенде; определяет
// иконку/цвет кружочка слева от уведомления в попапе (NotificationsPanel).
export type AppNotificationType = 'system' | 'card' | 'payment' | 'security' | 'promo';

// Уведомление в ленте ЛК (попап из шапки) — заводится в админке,
// отдаётся GET /notifications. Назван не `Notification`, чтобы не пересекаться
// с глобальным DOM-типом Notification (browser push API).
export interface AppNotification {
    // uuid, а не сквозной id в базе — аналогично Card.id/CardTransaction.id.
    id: string;
    type: AppNotificationType;
    title: string;
    body: string | null;
    // Маршрут SPA (например /cards/{uuid}) или внешняя ссылка — null, если уведомление не кликабельно.
    action_url: string | null;
    is_read: boolean;
    created_at: string;
}
