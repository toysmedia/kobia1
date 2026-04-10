/**
 * OxNet Service Worker
 * Caches static assets (CSS, JS, fonts, images) for offline support.
 *
 * Strategy:
 *   - Cache-first for static assets (CSS, JS, fonts, images)
 *   - Network-first for HTML/API requests
 */

const CACHE_VERSION = 'oxnet-v1';
const STATIC_CACHE  = `${CACHE_VERSION}-static`;
const FONT_CACHE    = `${CACHE_VERSION}-fonts`;

const STATIC_ASSETS = [
    '/',
];

const CACHE_PATTERNS = {
    fonts:  /\.(woff2?|eot|ttf|otf)(\?.*)?$/i,
    static: /\.(css|js|png|jpg|jpeg|webp|svg|ico|gif)(\?.*)?$/i,
};

// ── Install ──────────────────────────────────────────────────────
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(STATIC_CACHE)
            .then((cache) => cache.addAll(STATIC_ASSETS))
            .then(() => self.skipWaiting())
    );
});

// ── Activate ─────────────────────────────────────────────────────
self.addEventListener('activate', (event) => {
    const validCaches = [STATIC_CACHE, FONT_CACHE];
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(
                keys
                    .filter((key) => !validCaches.includes(key))
                    .map((key) => caches.delete(key))
            ))
            .then(() => self.clients.claim())
    );
});

// ── Fetch ─────────────────────────────────────────────────────────
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // Skip non-GET requests and cross-origin requests except fonts/CDN
    if (request.method !== 'GET') return;

    // Font assets: cache-first with long TTL
    if (CACHE_PATTERNS.fonts.test(url.pathname)) {
        event.respondWith(cacheFirst(request, FONT_CACHE));
        return;
    }

    // Static assets from same origin: cache-first
    if (url.origin === self.location.origin && CACHE_PATTERNS.static.test(url.pathname)) {
        event.respondWith(cacheFirst(request, STATIC_CACHE));
        return;
    }

    // CDN assets (Bootstrap, Font Awesome, etc.): stale-while-revalidate
    // Use exact hostname matching to prevent URL-based bypass attacks
    const ALLOWED_CDN_HOSTS = new Set([
        'cdn.jsdelivr.net',
        'cdnjs.cloudflare.com',
        'fonts.googleapis.com',
        'fonts.gstatic.com',
        'unpkg.com',
    ]);

    if (ALLOWED_CDN_HOSTS.has(url.hostname)) {
        event.respondWith(staleWhileRevalidate(request, STATIC_CACHE));
        return;
    }

    // HTML/API: network-first with cache fallback
    if (request.headers.get('Accept') && request.headers.get('Accept').includes('text/html')) {
        event.respondWith(networkFirst(request, STATIC_CACHE));
        return;
    }
});

// ── Strategies ────────────────────────────────────────────────────

async function cacheFirst(request, cacheName) {
    const cached = await caches.match(request);
    if (cached) return cached;

    try {
        const response = await fetch(request);
        if (response.ok) {
            const cache = await caches.open(cacheName);
            cache.put(request, response.clone());
        }
        return response;
    } catch {
        return new Response('Offline', { status: 503 });
    }
}

async function networkFirst(request, cacheName) {
    try {
        const response = await fetch(request);
        if (response.ok) {
            const cache = await caches.open(cacheName);
            cache.put(request, response.clone());
        }
        return response;
    } catch {
        const cached = await caches.match(request);
        return cached || new Response('Offline', { status: 503, headers: { 'Content-Type': 'text/plain' } });
    }
}

async function staleWhileRevalidate(request, cacheName) {
    const cache = await caches.open(cacheName);
    const cached = await cache.match(request);

    const fetchPromise = fetch(request).then((response) => {
        if (response.ok) cache.put(request, response.clone());
        return response;
    }).catch(() => null);

    return cached || await fetchPromise || new Response('Offline', { status: 503 });
}
