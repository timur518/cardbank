import { apiClient } from './client';
import type { CardProduct, PaymentMethod } from './types';

// Каталог карточных продуктов для выбора при оформлении заявки на выпуск карты.
export async function fetchCardProducts(): Promise<CardProduct[]> {
    const { data } = await apiClient.get<{ data: CardProduct[] }>('/card-products');
    return data.data;
}

// Способы оплаты для шага оплаты при выпуске карты и при пополнении баланса.
export async function fetchPaymentMethods(): Promise<PaymentMethod[]> {
    const { data } = await apiClient.get<{ data: PaymentMethod[] }>('/payment-methods');
    return data.data;
}
