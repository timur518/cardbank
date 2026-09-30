import { apiClient, ensureCsrfCookie } from './client';
import type { LoginPayload, Profile, RegisterPayload, SetPinPayload, UpdatePasswordPayload, UpdateProfilePayload } from './types';

// Аутентификация и управление текущей сессией личного кабинета
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

// Блок «Мои данные» на странице профиля — ФИО, телефон, дата рождения.
export async function updateProfile(payload: UpdateProfilePayload): Promise<Profile> {
    const { data } = await apiClient.patch<{ data: Profile }>('/profile', payload);
    return data.data;
}

// Блок «Безопасность» на странице профиля — смена пароля с подтверждением текущим.
export async function updatePassword(payload: UpdatePasswordPayload): Promise<string> {
    const { data } = await apiClient.post<{ message: string }>('/profile/password', payload);
    return data.message;
}

// Отмечает в профиле, что клиент установил ЛК как PWA — вызывается из PwaInstallPrompt.tsx.
export async function markPwaInstalled(): Promise<Profile> {
    const { data } = await apiClient.post<{ data: Profile }>('/profile/pwa-installed');
    return data.data;
}

// Установка/смена ПИН-кода — PinSetupModal.tsx (нудж из PinSetupPrompt.tsx и кнопка в профиле).
export async function setPin(payload: SetPinPayload): Promise<Profile> {
    const { data } = await apiClient.post<{ data: Profile }>('/profile/pin', payload);
    return data.data;
}

// Проверка текущего ПИН-кода на первом шаге попапа смены (PinSetupModal.tsx, mode="change") —
// бросает ValidationError, если ПИН неверный, иначе просто resolve.
export async function verifyPin(pin: string): Promise<void> {
    await apiClient.post('/profile/pin/verify', { pin });
}
