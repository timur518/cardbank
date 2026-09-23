import { apiClient } from './client';
import type { Card, CardDetail, CardOtpCode, CardRequisites } from './types';

export async function fetchCards(): Promise<Card[]> {
    const { data } = await apiClient.get<{ data: Card[] }>('/cards');
    return data.data;
}

// Карточка одной карты с реквизитами и платёжным адресом.
export async function fetchCard(cardId: number | string): Promise<CardDetail> {
    const { data } = await apiClient.get<{ data: CardDetail }>(`/cards/${cardId}`);
    return data.data;
}

// Полные данные карты.
export async function fetchCardRequisites(cardId: number | string): Promise<CardRequisites> {
    const { data } = await apiClient.get<{ data: CardRequisites }>(`/cards/${cardId}/requisites`);
    return data.data;
}

// OTP(3DS)-коды карты (вкладка «3DS коды») — живой запрос к провайдеру на каждый вызов.
export async function fetchCardOtpCodes(cardId: number | string): Promise<CardOtpCode[]> {
    const { data } = await apiClient.get<{ data: CardOtpCode[] }>(`/cards/${cardId}/otp-codes`);
    return data.data;
}
