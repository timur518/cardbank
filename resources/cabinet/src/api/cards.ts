import { apiClient } from './client';
import type { Card } from './types';

export async function fetchCards(): Promise<Card[]> {
    const { data } = await apiClient.get<{ data: Card[] }>('/cards');
    return data.data;
}
