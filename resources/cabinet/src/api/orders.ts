import { apiClient } from './client';
import type { IssueOrderPayload, IssueOrderResult } from './types';

// Оформление заказа на выпуск новой карты с первым пополнением (OrderController::issue()).
// При успехе бэкенд уже создал Card (status = waiting) и Income (payment_status =
// pending) и инициировал платёж — дальше пользователя нужно отправить на payment_url.
export async function issueOrder(payload: IssueOrderPayload): Promise<IssueOrderResult> {
    const { data } = await apiClient.post<{ data: IssueOrderResult }>('/orders/issue', payload);
    return data.data;
}
