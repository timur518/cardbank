import { apiClient } from './client';
import type { CardTransaction, Paginated } from './types';

export interface TransactionsQuery {
    page?: number;
    per_page?: number;
    // Строка — один тип, массив — несколько через axios ?type[]=a&type[]=b (см. TransactionController::paginate()).
    type?: string | string[];
    card_id?: string;
    date_from?: string;
    date_to?: string;
}

export async function fetchTransactions(query: TransactionsQuery = {}): Promise<Paginated<CardTransaction>> {
    const { data } = await apiClient.get<Paginated<CardTransaction>>('/transactions', { params: query });
    return data;
}

// История операций по одной карте — блок «История транзакций» на странице карты.
export async function fetchCardTransactions(
    cardId: number | string,
    query: Omit<TransactionsQuery, 'card_id'> = {},
): Promise<Paginated<CardTransaction>> {
    const { data } = await apiClient.get<Paginated<CardTransaction>>(`/cards/${cardId}/transactions`, { params: query });
    return data;
}
