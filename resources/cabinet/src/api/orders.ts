import { apiClient } from './client';
import type {
    IssueOrderPayload,
    IssueOrderResult,
    TopupOrderPayload,
    TopupOrderResult,
    TopupQuotePayload,
    TopupQuoteResult,
} from './types';

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

// Живой предрасчёт суммы к оплате (с комиссией провайдера) — без создания заказа,
// для кнопки «Оплатить • {сумма} ₽» в TopupModal и NewCardOrderPage.
export async function quoteTopup(payload: TopupQuotePayload): Promise<TopupQuoteResult> {
    const { data } = await apiClient.post<{ data: TopupQuoteResult }>('/orders/topup/quote', payload);
    return data.data;
}
