import { apiClient } from './client';
import type { IssueOrderPayload, IssueOrderResult, TopupOrderPayload, TopupOrderResult } from './types';

// Оформление заказа на выпуск новой карты с первым пополнением.
export async function issueOrder(payload: IssueOrderPayload): Promise<IssueOrderResult> {
    const { data } = await apiClient.post<{ data: IssueOrderResult }>('/orders/issue', payload);
    return data.data;
}

// Пополнение баланса уже выпущенной активной карты
export async function topupOrder(payload: TopupOrderPayload): Promise<TopupOrderResult> {
    const { data } = await apiClient.post<{ data: TopupOrderResult }>('/orders/topup', payload);
    return data.data;
}
