// Соответствует UserResource — см. CABINET_API_SPEC.md, раздел 1, п. 3.
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
