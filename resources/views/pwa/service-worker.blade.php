const CACHE_VERSION = @json($cacheVersion);
const STATIC_CACHE = `pc-static-${CACHE_VERSION}`;
const RUNTIME_CACHE = `pc-runtime-${CACHE_VERSION}`;
const OFFLINE_URL = @json($offlineUrl);
const PROTECTED_PREFIXES = @json($protectedPrefixes);
const OFFLINE_FALLBACK_ROUTES = @json($offlineFallbackRoutes);
const PRECACHE_URLS = @json($precacheUrls);

const STATIC_EXTENSIONS = [
    '.css', '.js', '.mjs', '.woff', '.woff2', '.ttf', '.otf', '.eot',
    '.png', '.jpg', '.jpeg', '.gif', '.webp', '.svg', '.ico', '.json', '.txt',
];

self.addEventListener('install', (event) => {
    event.waitUntil((async () => {
        const cache = await caches.open(STATIC_CACHE);

        await cache.addAll(PRECACHE_URLS.map((url) => new Request(url, { cache: 'reload' })));
        await self.skipWaiting();
    })());
});

self.addEventListener('activate', (event) => {
    event.waitUntil((async () => {
        const keys = await caches.keys();

        await Promise.all(
            keys
                .filter((key) => key.startsWith('pc-') && key !== STATIC_CACHE && key !== RUNTIME_CACHE)
                .map((key) => caches.delete(key)),
        );

        await self.clients.claim();
    })());
});

self.addEventListener('fetch', (event) => {
    const request = event.request;

    if (request.method !== 'GET') {
        return;
    }

    const url = new URL(request.url);

    if (url.origin !== self.location.origin) {
        return;
    }

    if (url.pathname === '/sw.js' || url.pathname === '/manifest.webmanifest') {
        return;
    }

    if (isProtectedPath(url.pathname)) {
        if (request.mode === 'navigate') {
            event.respondWith(networkOnlyNavigateWithOfflineFallback(request));
        }

        return;
    }

    if (isStaticAsset(url.pathname)) {
        event.respondWith(cacheFirst(request));

        return;
    }

    if (request.mode === 'navigate') {
        event.respondWith(networkNavigateWithOfflineFallback(request, url.pathname));
    }
});

function isProtectedPath(pathname) {
    return PROTECTED_PREFIXES.some((prefix) => pathname === prefix || pathname.startsWith(`${prefix}/`));
}

function isStaticAsset(pathname) {
    if (pathname.startsWith('/build/')) {
        return true;
    }

    return STATIC_EXTENSIONS.some((extension) => pathname.endsWith(extension));
}

async function cacheFirst(request) {
    const cached = await caches.match(request);

    if (cached) {
        return cached;
    }

    try {
        const response = await fetch(request);

        if (isCacheableResponse(response)) {
            const cache = await caches.open(STATIC_CACHE);
            await cache.put(request, response.clone());
        }

        return response;
    } catch {
        const fallback = await caches.match('/favicon.ico');

        return fallback || Response.error();
    }
}

async function networkNavigateWithOfflineFallback(request, pathname) {
    try {
        const response = await fetch(request);

        if (OFFLINE_FALLBACK_ROUTES.includes(pathname) && isCacheableResponse(response)) {
            const cache = await caches.open(RUNTIME_CACHE);
            await cache.put(request, response.clone());
        }

        return response;
    } catch {
        if (OFFLINE_FALLBACK_ROUTES.includes(pathname)) {
            const cached = await caches.match(request);

            if (cached) {
                return cached;
            }
        }

        const offline = await caches.match(OFFLINE_URL);

        return offline || Response.error();
    }
}

async function networkOnlyNavigateWithOfflineFallback(request) {
    try {
        return await fetch(request);
    } catch {
        const offline = await caches.match(OFFLINE_URL);

        return offline || Response.error();
    }
}

function isCacheableResponse(response) {
    if (!response || response.status !== 200 || response.type === 'opaque') {
        return false;
    }

    const cacheControl = (response.headers.get('Cache-Control') || '').toLowerCase();

    if (cacheControl.includes('no-store') || cacheControl.includes('private')) {
        return false;
    }

    if (response.headers.has('Set-Cookie')) {
        return false;
    }

    return true;
}
