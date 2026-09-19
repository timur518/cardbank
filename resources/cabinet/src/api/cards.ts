import { apiClient } from './client';
import type { Card, CardDetail } from './types';

export async function fetchCards(): Promise<Card[]> {
    const { data } = await apiClient.get<{ data: Card[] }>('/cards');
    return data.data;
}

// Карточка одной карты с реквизитами и платёжным адресом (CardController::show()).
export async function fetchCard(cardId: number | string): Promise<CardDetail> {
    const { data } = await apiClient.get<{ data: CardDetail }>(`/cards/${cardId}`);
    return data.data;
}
