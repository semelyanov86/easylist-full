// Easylist Service Worker — Network First стратегия
const CACHE_VERSION = 'easylist-v1';
const STATIC_ASSETS = [
    '/android-chrome-192x192.png',
    '/android-chrome-512x512.png',
    '/apple-touch-icon.png',
    '/favicon.ico',
    '/favicon.svg',
    '/site.webmanifest',
];

// Установка: кэшируем статические ресурсы
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_VERSION).then((cache) => cache.addAll(STATIC_ASSETS))
    );
    self.skipWaiting();
});

// Активация: удаляем старые кэши
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(
                keys
                    .filter((key) => key !== CACHE_VERSION)
                    .map((key) => caches.delete(key))
            )
        )
    );
    self.clients.claim();
});

// Запросы: Network First с fallback на кэш
self.addEventListener('fetch', (event) => {
    const { request } = event;

    // Пропускаем non-GET запросы
    if (request.method !== 'GET') {
        return;
    }

    // Пропускаем запросы к внешним ресурсам
    if (!request.url.startsWith(self.location.origin)) {
        return;
    }

    // Пропускаем API-запросы — они не кэшируются
    const url = new URL(request.url);
    if (url.pathname.startsWith('/api/')) {
        return;
    }

    event.respondWith(
        fetch(request)
            .then((response) => {
                // Кэшируем успешные ответы
                if (response.ok) {
                    const responseClone = response.clone();
                    caches.open(CACHE_VERSION).then((cache) => {
                        cache.put(request, responseClone);
                    });
                }
                return response;
            })
            .catch(() => {
                // При ошибке сети — отдаём из кэша
                return caches.match(request);
            })
    );
});
