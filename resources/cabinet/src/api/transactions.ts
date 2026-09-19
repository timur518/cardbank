import { apiClient } from './client';
import type { CardTransaction, Paginated } from './types';

export interface TransactionsQuery {
    page?: number;
    per_page?: number;
    type?: string;
    card_id?: number;
}

export async function fetchTransactions(query: TransactionsQuery = {}): Promise<Paginated<CardTransaction>> {
    const { data } = await apiClient.get<Paginated<CardTransaction>>('/transactions', { params: query });
    return data;
}
