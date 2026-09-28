import { fetchAnalyticsSettings } from '../api/settings';

let injected = false;

/**
 * Загружает код счётчика (например, Яндекс.Метрика) и код пикселей/прочих
 * сторонних скриптов, заданные в админке (Настройки -> Аналитика и внешние
 * сервисы), и вставляет их в <head> ЛК — тот же код, что подставляется в
 * <head> лендинга (см. routes/web.php + welcome.blade.php), так как оба
 * фронтенда читают одни и те же ключи settings (AnalyticsSettings::KEYS).
 * Вызывается один раз при старте SPA (main.tsx).
 */
export async function injectAnalyticsCodes(): Promise<void> {
    if (injected) {
        return;
    }

    injected = true;

    try {
        const codes = await fetchAnalyticsSettings();

        [codes.analytics_counter_id, codes.analytics_pixel_ids]
            .filter((code): code is string => Boolean(code && code.trim()))
            .forEach(appendCodeToHead);
    } catch {
        // Аналитика не критична для работы ЛК — молча игнорируем сбой загрузки.
    }
}

// document.head.innerHTML не выполняет вложенные <script> — их нужно
// пересоздать через createElement, чтобы браузер их реально исполнил.
function appendCodeToHead(code: string): void {
    const template = document.createElement('template');
    template.innerHTML = code;

    Array.from(template.content.childNodes).forEach((node) => {
        if (node instanceof HTMLScriptElement) {
            const script = document.createElement('script');

            Array.from(node.attributes).forEach((attr) => script.setAttribute(attr.name, attr.value));
            script.textContent = node.textContent;

            document.head.appendChild(script);
        } else {
            document.head.appendChild(node.cloneNode(true));
        }
    });
}
