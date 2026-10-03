// UTM-метки и реферальный код приглашающего (?pid= в ссылках-приглашениях вида
// https://mojno.cc/?pid=X71KJN, где X71KJN — собственный код приглашения другого
// пользователя, см. User::invite_code) сохраняются в cookie при заходе в ЛК с ними в URL —
// чтобы дожить до момента регистрации (записывается в User::referral_code, см.
// RegisterPage.tsx), даже если она произойдёт позже или на другой странице без этого
// параметра в адресе. При повторном визите с ?pid= cookie перезаписывается (новое значение +
// срок жизни снова отсчитывается от текущего момента).
//
// Cookie ставится с Domain=mojno.cc (без самого верхнего уровня на localhost/IP,
// см. cookieDomain()), поэтому видна и лендингу на mojno.cc: та же логика с теми
// же именами cookie продублирована в resources/js/app.js (отдельный рантайм,
// общий код между Vite-сборкой ЛК и обычным JS-ассетом лендинга не используется).
const TTL_DAYS = 60;
const PID_TTL_DAYS = 90;
const COOKIE_PREFIX = 'mojno_';
const TRACKED_PARAMS: Record<string, { queryKeys: string[]; ttlDays: number }> = {
    utm_source: { queryKeys: ['utm_source'], ttlDays: TTL_DAYS },
    utm_medium: { queryKeys: ['utm_medium'], ttlDays: TTL_DAYS },
    utm_campaign: { queryKeys: ['utm_campaign'], ttlDays: TTL_DAYS },
    utm_content: { queryKeys: ['utm_content'], ttlDays: TTL_DAYS },
    pid: { queryKeys: ['pid'], ttlDays: PID_TTL_DAYS },
};

function cookieDomain(): string | null {
    const host = window.location.hostname;

    if (host === 'localhost' || /^\d+\.\d+\.\d+\.\d+$/.test(host)) {
        return null;
    }

    const parts = host.split('.');

    return parts.length > 2 ? parts.slice(-2).join('.') : host;
}

function setCookie(name: string, value: string, ttlDays: number): void {
    const domain = cookieDomain();
    const expires = new Date(Date.now() + ttlDays * 24 * 60 * 60 * 1000).toUTCString();
    let cookie = `${COOKIE_PREFIX}${name}=${encodeURIComponent(value)}; expires=${expires}; path=/; SameSite=Lax`;

    if (domain) {
        cookie += `; domain=${domain}`;
    }

    if (window.location.protocol === 'https:') {
        cookie += '; secure';
    }

    document.cookie = cookie;
}

/** Читает ранее сохранённую метку/реферальный код (без префикса mojno_ в имени). */
export function getTrackingCookie(name: string): string | undefined {
    const match = document.cookie.match(new RegExp(`(?:^|; )${COOKIE_PREFIX}${name}=([^;]*)`));

    return match ? decodeURIComponent(match[1]) : undefined;
}

/**
 * Вызывается один раз при старте SPA (main.tsx). Если в текущем URL есть
 * UTM-метки или pid, обновляет cookie (срок — от текущего момента); если
 * параметра нет в URL, ранее сохранённое значение не трогает.
 */
export function captureTrackingParams(): void {
    const params = new URLSearchParams(window.location.search);

    for (const [cookieKey, { queryKeys, ttlDays }] of Object.entries(TRACKED_PARAMS)) {
        const value = queryKeys.map((key) => params.get(key)).find((v) => v);

        if (value) {
            setCookie(cookieKey, value, ttlDays);
        }
    }
}
