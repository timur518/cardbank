import { apiClient } from './client';
import type { BrandSettings, ReferralSettings } from './types';

export async function fetchBrandSettings(): Promise<BrandSettings> {
    const { data } = await apiClient.get<{ data: BrandSettings }>('/settings/brand');
    return data.data;
}

export async function fetchReferralSettings(): Promise<ReferralSettings> {
    const { data } = await apiClient.get<{ data: ReferralSettings }>('/settings/referral');
    return data.data;
}
