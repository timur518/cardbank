/// <reference types="vite/client" />

interface ImportMetaEnv {
    readonly VITE_API_BASE_URL: string;
    /** Адрес встраиваемого виджета чата (public/mojno-help) в ChatWidget.tsx — опциональный, без него кнопка чата в ЛК не показывается. */
    readonly VITE_CHAT_WIDGET_URL?: string;
}

interface ImportMeta {
    readonly env: ImportMetaEnv;
}
