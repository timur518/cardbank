import { apiClient } from './client';
import type { BitbankerStatus } from './types';

// Текущее состояние подключения BitBanker для пользователя (оферта/статус проверки) —
// используется PaymentMethodsList, чтобы выбрать нужную плитку (см.
// BITBANKER_INTEGRATION_PLAN.md раздел 10.2).
export async function fetchBitbankerStatus(): Promise<BitbankerStatus> {
    const { data } = await apiClient.get<{ data: BitbankerStatus }>('/bitbanker/status');
    return data.data;
}

// Принятие оферты BitBanker + попытка регистрации клиента (POST /v1/bitbanker/accept).
// offer_accepted фиксируется на бэкенде ещё до самой регистрации, поэтому в ответе его нет —
// вызывающий код (BitbankerOfferModal/PaymentMethodsList) считает оферту принятой по самому
// факту успешного ответа.
export async function acceptBitbankerOffer(): Promise<Pick<BitbankerStatus, 'is_verified_for_sbp' | 'check_status'>> {
    const { data } = await apiClient.post<{ data: Pick<BitbankerStatus, 'is_verified_for_sbp' | 'check_status'> }>(
        '/bitbanker/accept',
    );
    return data.data;
}
