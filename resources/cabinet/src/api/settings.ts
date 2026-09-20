import { apiClient } from './client';
import type { BrandSettings, CurrencyRates, ReferralSettings } from './types';

export async function fetchBrandSettings(): Promise<BrandSettings> {
    const { data } = await apiClient.get<{ data: BrandSettings }>('/settings/brand');
    return data.data;
}

export async function fetchReferralSettings(): Promise<ReferralSettings> {
    const { data } = await apiClient.get<{ data: ReferralSettings }>('/settings/referral');
    return data.data;
}

// Курс продажи валют с наценкой — используется для
// пересчёта суммы в $ в ₽ на шаге оформления заказа (серверный пересчёт
// всегда авторитетен, здесь только для отображения «К оплате» до отправки).
export async function fetchCurrencyRates(): Promise<CurrencyRates> {
    const { data } = await apiClient.get<{ data: CurrencyRates }>('/settings/currency-rates');
    return data.data;
}
