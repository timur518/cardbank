import { apiClient } from './client';
import type { Card, CardDetail, CardRequisites } from './types';

export async function fetchCards(): Promise<Card[]> {
    const { data } = await apiClient.get<{ data: Card[] }>('/cards');
    return data.data;
}

// Карточка одной карты с реквизитами и платёжным адресом (CardController::show()).
export async function fetchCard(cardId: number | string): Promise<CardDetail> {
    const { data } = await apiClient.get<{ data: CardDetail }>(`/cards/${cardId}`);
    return data.data;
}

// Полный номер и CVV карты (CardController::requisites()). Номер запрашивается автоматически
// на странице карты, CVV — только по кнопке «Показать CVV».
export async function fetchCardRequisites(cardId: number | string): Promise<CardRequisites> {
    const { data } = await apiClient.get<{ data: CardRequisites }>(`/cards/${cardId}/requisites`);
    return data.data;
}
