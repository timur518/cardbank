import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import tailwindcss from '@tailwindcss/vite';

// Личный кабинет — самостоятельное SPA-приложение (не часть сборки Laravel/Vite
// основного проекта, см. корневой vite.config.js для лендинга). Собирается и
// раздаётся отдельно, в перспективе — со своего поддомена (app.<domain>), ходит в
// общий Laravel-бэкенд по REST API (см. CABINET_API_SPEC.md) через CORS + Sanctum SPA.
export default defineConfig({
    plugins: [react(), tailwindcss()],
    server: {
        port: 5173,
    },
});
