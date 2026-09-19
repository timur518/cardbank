import { apiClient } from './client';
import type { IssueOrderPayload, IssueOrderResult, TopupOrderPayload, TopupOrderResult } from './types';

// Оформление заказа на выпуск новой карты с первым пополнением (OrderController::issue()).
// При успехе бэкенд уже создал Card (status = waiting) и Income (payment_status =
// pending) и инициировал платёж — дальше пользователя нужно отправить на payment_url.
export async function issueOrder(payload: IssueOrderPayload): Promise<IssueOrderResult> {
    const { data } = await apiClient.post<{ data: IssueOrderResult }>('/orders/issue', payload);
    return data.data;
}

// Пополнение баланса уже выпущенной активной карты (OrderController::topup()) — кнопка
// «+ Пополнить карту» на странице карты.
export async function topupOrder(payload: TopupOrderPayload): Promise<TopupOrderResult> {
    const { data } = await apiClient.post<{ data: TopupOrderResult }>('/orders/topup', payload);
    return data.data;
}
