import { apiClient } from './client';

export interface KycSession {
    url: string;
    session_id: string;
    status: string;
}

// Запускает/переиспользует сессию верификации Didit — блок «Верификация личности»
// на странице профиля открывает попап с iframe на возвращённый url.
export async function startKycVerification(): Promise<KycSession> {
    const { data } = await apiClient.post<{ data: KycSession }>('/kyc/start');
    return data.data;
}
