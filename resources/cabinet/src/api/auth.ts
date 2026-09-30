import { apiClient, ensureCsrfCookie } from './client';
import type { DeviceStatus, LoginPayload, Profile, RegisterPayload, SetPinPayload, UpdatePasswordPayload, UpdateProfilePayload } from './types';

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

// Есть ли на этом устройстве действующий доверенный токен (кука mojno_pin_device) — вызывается в
// бутстрапе AuthContext, когда GET /profile вернул 401 (обычная сессия истекла/отсутствует).
export async function deviceStatus(): Promise<DeviceStatus> {
    const { data } = await apiClient.get<DeviceStatus>('/auth/device-status');
    return data;
}

// Разблокировка ЛК по 4-значному ПИН-коду на доверенном устройстве (PinUnlockPage.tsx) — вызывается только
// когда deviceStatus() вернул trusted:true; при успехе создаёт обычную полноценную Sanctum-сессию (как login()).
// Не требует ensureCsrfCookie() — вызывается до наличия сессии/CSRF-токена, как и login().
export async function unlockPin(pin: string): Promise<Profile> {
    await ensureCsrfCookie();
    const { data } = await apiClient.post<{ data: Profile }>('/auth/unlock-pin', { pin });
    return data.data;
}

// «Это не я» / «Войти по паролю» на экране ввода ПИН-кода — отзывает доверие этому устройству,
// чтобы выбраться на обычный /login (иначе GuestRoute вернёт обратно на /unlock, пока кука жива).
export async function forgetDevice(): Promise<void> {
    await ensureCsrfCookie();
    await apiClient.post('/auth/forget-device');
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
