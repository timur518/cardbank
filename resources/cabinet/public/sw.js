// Минимальный service worker личного кабинета — нужен только для того, чтобы браузер
// считал приложение устанавливаемым (PWA installability requirement) и чтобы при потере
// сети открывался хотя бы каркас приложения (index.html) вместо ошибки браузера "нет
// соединения". Полноценный офлайн-режим (кеш API-ответов и т.п.) не реализован осознанно:
// ЛК — банковское приложение поверх живых данных (баланс, транзакции), показывать
// устаревшие закешированные данные о деньгах пользователю было бы неправильно.
const SHELL_CACHE = 'mojno-shell-v1';

self.addEventListener('install', (event) => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(keys.filter((key) => key !== SHELL_CACHE).map((key) => caches.delete(key)))
        )
    );
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    // Только навигационные запросы (переход/обновление страницы) — API (`/api/...`) и
    // статические ассеты (JS/CSS с хешем в имени) всегда идут в сеть как есть, без
    // перехвата, чтобы не мешать обычному HTTP-кешу браузера и не отдавать устаревший код
    // после деплоя.
    if (event.request.mode !== 'navigate') {
        return;
    }

    event.respondWith(
        fetch(event.request)
            .then((response) => {
                const copy = response.clone();
                caches.open(SHELL_CACHE).then((cache) => cache.put('/', copy));
                return response;
            })
            .catch(() => caches.match('/').then((cached) => cached || Response.error()))
    );
});
