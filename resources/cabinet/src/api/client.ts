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

// Вызывается при любом 401 от защищённого эндпоинта, кроме самих /auth/* (они сами
// обрабатывают свои ожидаемые 401 в формах логина/разблокировки) — покрывает случай, когда
// обычная Sanctum-сессия истекает, пока вкладка ЛК остаётся открытой (не только при
// холодной загрузке, которую обрабатывает бутстрап в AuthContext) — см. AuthContext.tsx.
type UnauthorizedHandler = () => void;
let onUnauthorized: UnauthorizedHandler | null = null;

export function setUnauthorizedHandler(handler: UnauthorizedHandler | null): void {
    onUnauthorized = handler;
}

apiClient.interceptors.response.use(
    (response) => response,
    (error) => {
        const url = axios.isAxiosError(error) ? error.config?.url : undefined;
        if (axios.isAxiosError(error) && error.response?.status === 401 && !url?.startsWith('/auth/')) {
            onUnauthorized?.();
        }
        return Promise.reject(error);
    },
);

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
