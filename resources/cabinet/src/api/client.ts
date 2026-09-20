import axios from 'axios';

export const API_ROOT = import.meta.env.VITE_API_BASE_URL ?? 'http://localhost';

export const apiClient = axios.create({
    baseURL: `${API_ROOT}/api/v1`,
    withCredentials: true,
    withXSRFToken: true,
    xsrfCookieName: 'XSRF-TOKEN',
    xsrfHeaderName: 'X-XSRF-TOKEN',
    headers: {
        Accept: 'application/json',
    },
    adapter: ['fetch', 'xhr', 'http'],
});

/**
 * Взять CSRF-куку перед первым небезопасным запросом (логин/регистрация/логаут и
 * т.д.) — обязательный первый шаг SPA-аутентификации Sanctum: без этой
 * куки сервер отвергает запрос как 419 CSRF token mismatch.
 */
export function ensureCsrfCookie(): Promise<unknown> {
    return axios.get(`${API_ROOT}/sanctum/csrf-cookie`, { withCredentials: true });
}

export interface ApiValidationError {
    message: string;
    errors?: Record<string, string[]>;
}

/**
 * Достать читаемое сообщение об ошибке из ответа axios (422/401/403/...) —
 * единообразно для всех форм ЛК.
 */
export function extractErrorMessage(error: unknown, fallback = 'Что-то пошло не так. Попробуйте ещё раз.'): string {
    if (axios.isAxiosError<ApiValidationError>(error) && error.response?.data) {
        const { message, errors } = error.response.data;

        if (errors) {
            const firstField = Object.values(errors)[0];
            if (firstField?.[0]) {
                return firstField[0];
            }
        }

        if (message) {
            return message;
        }
    }

    return fallback;
}
