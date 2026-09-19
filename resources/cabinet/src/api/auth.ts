import { apiClient, ensureCsrfCookie } from './client';
import type { LoginPayload, Profile, RegisterPayload } from './types';

// Аутентификация и управление текущей сессией личного кабинета (Sanctum SPA).

export async function login(payload: LoginPayload): Promise<Profile> {
    await ensureCsrfCookie();
    const { data } = await apiClient.post<{ data: Profile }>('/auth/login', payload);
    return data.data;
}

export async function register(payload: RegisterPayload): Promise<Profile> {
    await ensureCsrfCookie();
    const { data } = await apiClient.post<{ data: Profile }>('/auth/register', payload);
    return data.data;
}

export async function forgotPassword(login: string): Promise<string> {
    await ensureCsrfCookie();
    const { data } = await apiClient.post<{ message: string }>('/auth/password/forgot', { login });
    return data.message;
}

export async function logout(): Promise<void> {
    await apiClient.post('/auth/logout');
}

export async function fetchProfile(): Promise<Profile> {
    const { data } = await apiClient.get<{ data: Profile }>('/profile');
    return data.data;
}
