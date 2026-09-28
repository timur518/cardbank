// UTM-метки и реферальный код (?ref= / ?referral_code= в ссылках-приглашениях,
// см. PartnershipPage.tsx) сохраняются в cookie на 60 дней при заходе в ЛК с
// ними в URL — чтобы дожить до момента регистрации, даже если она произойдёт
// позже или на другой странице без этих параметров в адресе.
//
// Cookie ставится с Domain=mojno.cc (без самого верхнего уровня на localhost/IP,
// см. cookieDomain()), поэтому видна и лендингу на mojno.cc: та же логика с теми
// же именами cookie продублирована в resources/js/app.js (отдельный рантайм,
// общий код между Vite-сборкой ЛК и обычным JS-ассетом лендинга не используется).
const TTL_DAYS = 60;
const COOKIE_PREFIX = 'mojno_';
const TRACKED_PARAMS: Record<string, string[]> = {
    utm_source: ['utm_source'],
    utm_medium: ['utm_medium'],
    utm_campaign: ['utm_campaign'],
    utm_content: ['utm_content'],
    ref: ['ref', 'referral_code'],
};

function cookieDomain(): string | null {
    const host = window.location.hostname;

    if (host === 'localhost' || /^\d+\.\d+\.\d+\.\d+$/.test(host)) {
        return null;
    }

    const parts = host.split('.');

    return parts.length > 2 ? parts.slice(-2).join('.') : host;
}

function setCookie(name: string, value: string): void {
    const domain = cookieDomain();
    const expires = new Date(Date.now() + TTL_DAYS * 24 * 60 * 60 * 1000).toUTCString();
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
 * UTM-метки или реферальный код, обновляет cookie (60 дней от текущего
 * момента); если параметра нет в URL, ранее сохранённое значение не трогает.
 */
export function captureTrackingParams(): void {
    const params = new URLSearchParams(window.location.search);

    for (const [cookieKey, queryKeys] of Object.entries(TRACKED_PARAMS)) {
        const value = queryKeys.map((key) => params.get(key)).find((v) => v);

        if (value) {
            setCookie(cookieKey, value);
        }
    }
}
