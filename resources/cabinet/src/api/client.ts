import axios from 'axios';

// Базовый URL Laravel-бэкенда (без /api) — см. .env.example. По умолчанию считаем,
// что ЛК крутится на том же хосте, что и API (см. dev-конфиг SANCTUM_STATEFUL_DOMAINS/
// CORS_ALLOWED_ORIGINS в .env бэкенда — localhost:5173 уже разрешён).
export const API_ROOT = import.meta.env.VITE_API_BASE_URL ?? 'http://localhost';

export const apiClient = axios.create({
    baseURL: `${API_ROOT}/api/v1`,
    withCredentials: true,
    // Sanctum SPA-аутентификация: сессионная кука + CSRF-кука (XSRF-TOKEN).
    // withXSRFToken нужен явно, т.к. ЛК в проде живёт на отдельном поддомене
    // (app.<domain>), т.е. запрос к API кросс-origin — по умолчанию (с axios 1.6+)
    // XSRF-заголовок не проставляется для кросс-origin запросов без этого флага.
    withXSRFToken: true,
    xsrfCookieName: 'XSRF-TOKEN',
    xsrfHeaderName: 'X-XSRF-TOKEN',
    headers: {
        Accept: 'application/json',
    },
    // Некоторые расширения браузера патчат window.XMLHttpRequest (менеджеры паролей,
    // блокировщики и т.п.) и ломают обработку CORS-ответа на стороне страницы —
    // снаружи это выглядит как "No 'Access-Control-Allow-Origin' header", хотя сервер
    // его отдаёт (см. CORS_ALLOWED_ORIGINS). Fetch API — отдельная реализация, которую
    // такие расширения обычно не трогают — предпочитаем её, с откатом на xhr/http.
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
