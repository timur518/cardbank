// Соответствует полям, которые отдаёт UserResource на бэкенде.
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

// Соответствует CardProductResource (только поля, нужные для отображения карты
// клиента, без себестоимостных полей продукта).
export interface CardProductSummary {
    key: string;
    name: string;
    skin: string | null;
}

export type CardStatus = 'waiting' | 'pending' | 'active' | 'frozen' | 'closed' | 'cancelled' | 'failed';

// Соответствует CardResource.
export interface Card {
    id: number;
    card_product: CardProductSummary;
    status: CardStatus;
    currency: string;
    balance: string;
    card_last4: string;
    expiry: string | null;
    issued_at: string | null;
}

export type CardTransactionType = 'purchase' | 'topup' | 'fee' | 'refund' | 'decline';
export type CardTransactionStatus = 'pending' | 'success' | 'declined' | 'reversed';

// Соответствует CardTransactionResource.
export interface CardTransaction {
    id: number;
    card_id: number;
    type: CardTransactionType;
    amount: string;
    currency: string;
    merchant: string | null;
    status: CardTransactionStatus;
    occurred_at: string;
}

export interface PaginationMeta {
    current_page: number;
    last_page: number;
    total: number;
}

export interface Paginated<T> {
    data: T[];
    meta: PaginationMeta;
}

// Соответствует ответу SettingsController::brand().
export interface BrandSettings {
    brand_domain: string | null;
    brand_site_name: string | null;
    brand_support_email: string | null;
    brand_logo: string | null;
    brand_theme_color: string | null;
}

// Соответствует ответу SettingsController::referral(). Поля могут быть null, если
// админ ещё не заполнил ReferralSettings в Filament.
export interface ReferralSettings {
    referral_issue_rate: string | null;
    referral_topup_rate: string | null;
    referral_hold_days: string | null;
    referral_min_wallet_rub: string | null;
    referral_min_bank_rub: string | null;
}
