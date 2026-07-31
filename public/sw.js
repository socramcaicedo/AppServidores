/**
 * Service Worker - App Servidores IPUC
 * Estrategia: stale-while-revalidate para assets, network-first para HTML.
 * No intercepta POST/PUT/DELETE ni requests cross-origin.
 *
 * IMPORTANTE: al deployar cambios en CSS/JS hay que subir CACHE_VERSION
 * para que se limpie la cache vieja en los dispositivos.
 */
const CACHE_VERSION = 'ipuc-shell-v1-20260730';
const CACHE_NAME = CACHE_VERSION;

const PRECACHE_URLS = [
    '/',
    '/manifest.json',
    '/images/LOGO3.jpeg',
    '/images/icon-192.png',
    '/images/icon-512.png',
    '/css/responsive.css',
    '/js/funciones-calendario.js',
];

// INSTALL: pre-cachear el app shell
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => cache.addAll(PRECACHE_URLS))
            .then(() => self.skipWaiting())
    );
});

// ACTIVATE: limpiar caches de versiones anteriores
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(
                keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key))
            ))
            .then(() => self.clients.claim())
    );
});

// FETCH: estrategia segun tipo de recurso
self.addEventListener('fetch', (event) => {
    const req = event.request;

    // Solo GET. POST/PUT/DELETE pasan directo al servidor.
    if (req.method !== 'GET') return;

    const url = new URL(req.url);

    // Solo mismo origen. No interferir con CDN/analytics externos.
    if (url.origin !== self.location.origin) return;

    if (req.destination === 'style' || req.destination === 'script' || req.destination === 'image') {
        // Assets estaticos: stale-while-revalidate
        event.respondWith(staleWhileRevalidate(req));
    } else {
        // HTML y demas: network-first con fallback a cache
        event.respondWith(networkFirst(req));
    }
});

async function staleWhileRevalidate(req) {
    const cache = await caches.open(CACHE_NAME);
    const cached = await cache.match(req);
    const network = fetch(req)
        .then((res) => {
            if (res && res.ok && res.type === 'basic') {
                cache.put(req, res.clone());
            }
            return res;
        })
        .catch(() => cached);
    return cached || network;
}

async function networkFirst(req) {
    const cache = await caches.open(CACHE_NAME);
    try {
        const res = await fetch(req);
        if (res && res.ok && res.type === 'basic') {
            cache.put(req, res.clone());
        }
        return res;
    } catch (err) {
        const cached = await cache.match(req);
        return cached || caches.match('/');
    }
}
