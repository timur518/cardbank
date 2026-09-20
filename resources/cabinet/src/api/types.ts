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

// Поля, редактируемые клиентом на странице профиля (PATCH /profile) — соответствует
// UpdateProfileRequest::rules(). email через этот эндпоинт не меняется.
export interface UpdateProfilePayload {
    first_name: string;
    last_name: string;
    middle_name?: string;
    phone: string;
    date_of_birth: string;
}

// Соответствует UpdatePasswordRequest::rules() (POST /profile/password) — смена пароля
// в личном кабинете, требует подтверждения текущим паролем.
export interface UpdatePasswordPayload {
    current_password: string;
    password: string;
    password_confirmation: string;
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
// клиента, без себестоимостных полей продукта). `topup_min_amount`/`topup_max_amount`
// заполнены только в CardDetailResource (вкладка «Лимиты» на странице карты), в списке карт
// (CardResource) их нет.
export interface CardProductSummary {
    key: string;
    name: string;
    skin: string | null;
    topup_min_amount?: string;
    topup_max_amount?: string;
}

export type CardStatus = 'waiting' | 'pending' | 'active' | 'frozen' | 'closed' | 'cancelled' | 'failed';

// Соответствует CardResource. `card_last4` — null, пока карте ещё не присвоен
// номер провайдером (статусы waiting/pending).
export interface Card {
    id: number;
    card_product: CardProductSummary;
    status: CardStatus;
    currency: string;
    balance: string;
    card_last4: string | null;
    expiry: string | null;
    issued_at: string | null;
    billing_address: BillingAddress;
}

// Соответствует блоку `billing_address` в CardResource — платёжный адрес карты
// (AVS), скопированный с CardProduct при выпуске. Любое поле может быть null.
export interface BillingAddress {
    country: string | null;
    city: string | null;
    region: string | null;
    address: string | null;
    post_code: string | null;
}

// Соответствует CardDetailResource (ответ GET /cards/{card}) — всё, что есть в Card, плюс
// стоимость выпуска — нужна только на странице одной карты.
export interface CardDetail extends Card {
    price_rub: string;
}

// Соответствует CardRequisitesResource (ответ GET /cards/{card}/requisites) — полный номер
// и CVV. Номер подгружается автоматически при открытии страницы карты, CVV — только по
// кнопке «Показать CVV».
export interface CardRequisites {
    card_number: string;
    expiry: string | null;
    cvv: string;
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

// Соответствует ответу SettingsController::currencyRates() — курс продажи с
// наценкой к курсу ЦБ РФ (CurrencyRateService::sellRates()).
export type CurrencyCode = 'usd' | 'eur' | 'gbp';
export type CurrencyRates = Record<CurrencyCode, string>;

// Соответствует CardProductResource (полная версия для выбора продукта при
// оформлении заявки, без себестоимостных полей продукта).
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

// Соответствует PaymentMethodResource.
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

// Соответствует ответу OrderController::issue().
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
    card_id: number;
    amount: number;
    currency: 'USD' | 'RUB';
    payment_method_id: number;
    idempotency_key: string;
}

// Соответствует ответу OrderController::topup().
export interface TopupOrderResult {
    card_id: number;
    topup_usd: string;
    topup_rub: string;
    total_rub: string;
    payment_transaction_id: string | null;
    payment_url: string | null;
    idempotency_key: string;
}
