// SNDP PWA Service Worker
// Version: {{CACHE_VERSION}}

const CACHE_VERSION = '{{CACHE_VERSION}}';
const CACHE_NAME = `pwa-cache-${CACHE_VERSION}`;
const OFFLINE_CACHE = 'pwa-offline-cache';
const DYNAMIC_CACHE = 'pwa-dynamic-cache';

// Assets to precache
const PRECACHE_ASSETS = [
    '/',
    '/offline',
    '/css/app.css',
    '/js/app.js',
];

// Cache strategies enum
const CacheStrategy = {
    CACHE_FIRST: 'CacheFirst',
    NETWORK_FIRST: 'NetworkFirst',
    STALE_WHILE_REVALIDATE: 'StaleWhileRevalidate',
    NETWORK_ONLY: 'NetworkOnly',
    CACHE_ONLY: 'CacheOnly',
};

// Current strategy (can be configured)
const CURRENT_STRATEGY = CacheStrategy.NETWORK_FIRST;

// ===========================================
// INSTALL EVENT - Pre-caching
// ===========================================
self.addEventListener('install', (event) => {
    console.log('[SW] Installing Service Worker...', CACHE_VERSION);
    
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => {
                console.log('[SW] Precaching assets');
                return cache.addAll(PRECACHE_ASSETS);
            })
            .then(() => {
                // Skip waiting to activate immediately
                return self.skipWaiting();
            })
    );
});

// ===========================================
// ACTIVATE EVENT - Cleanup old caches
// ===========================================
self.addEventListener('activate', (event) => {
    console.log('[SW] Activating Service Worker...', CACHE_VERSION);
    
    event.waitUntil(
        caches.keys()
            .then((cacheNames) => {
                return Promise.all(
                    cacheNames.map((cacheName) => {
                        if (cacheName !== CACHE_NAME && 
                            cacheName !== OFFLINE_CACHE && 
                            cacheName !== DYNAMIC_CACHE) {
                            console.log('[SW] Deleting old cache:', cacheName);
                            return caches.delete(cacheName);
                        }
                    })
                );
            })
            .then(() => {
                // Take control of all pages immediately
                return self.clients.claim();
            })
    );
});

// ===========================================
// FETCH EVENT - Network request handling
// ===========================================
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);
    
    // Skip non-GET requests
    if (request.method !== 'GET') {
        return;
    }
    
    // Skip chrome extensions and other origins
    if (url.origin !== self.location.origin) {
        return;
    }
    
    // Choose strategy based on request type
    if (isNavigationRequest(request)) {
        event.respondWith(handleNavigationRequest(request));
    } else if (isImageRequest(request)) {
        event.respondWith(handleImageRequest(request));
    } else if (isStaticAsset(request)) {
        event.respondWith(handleStaticAsset(request));
    } else {
        event.respondWith(handleRequest(request, CURRENT_STRATEGY));
    }
});

// ===========================================
// MESSAGE EVENT - Communication with pages
// ===========================================
self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
    
    if (event.data && event.data.type === 'CACHE_URLS') {
        event.waitUntil(
            caches.open(DYNAMIC_CACHE)
                .then((cache) => cache.addAll(event.data.urls))
        );
    }
    
    if (event.data && event.data.type === 'CLEAR_CACHE') {
        event.waitUntil(
            caches.keys().then((cacheNames) => {
                return Promise.all(
                    cacheNames.map((cacheName) => caches.delete(cacheName))
                );
            })
        );
    }
});

// ===========================================
// SYNC EVENT - Background Sync
// ===========================================
self.addEventListener('sync', (event) => {
    console.log('[SW] Background sync:', event.tag);
    
    if (event.tag === 'sync-data') {
        event.waitUntil(syncData());
    }
});

// ===========================================
// PERIODIC SYNC EVENT
// ===========================================
self.addEventListener('periodicsync', (event) => {
    console.log('[SW] Periodic sync:', event.tag);
    
    if (event.tag === 'periodic-sync') {
        event.waitUntil(periodicSync());
    }
});

// ===========================================
// PUSH EVENT - Push Notifications
// ===========================================
self.addEventListener('push', (event) => {
    console.log('[SW] Push notification received');
    
    const options = {
        body: event.data ? event.data.text() : 'New notification',
        icon: '/pwa/icons/icon-192x192.png',
        badge: '/pwa/icons/icon-72x72.png',
        vibrate: [200, 100, 200],
        data: {
            dateOfArrival: Date.now(),
            primaryKey: 1
        },
        actions: [
            {
                action: 'open',
                title: 'Open App',
            },
            {
                action: 'close',
                title: 'Close',
            },
        ],
    };
    
    event.waitUntil(
        self.registration.showNotification('SNDP PWA', options)
    );
});

// ===========================================
// NOTIFICATION CLICK EVENT
// ===========================================
self.addEventListener('notificationclick', (event) => {
    console.log('[SW] Notification clicked:', event.action);
    
    event.notification.close();
    
    if (event.action === 'open') {
        event.waitUntil(
            clients.openWindow('/')
        );
    }
});

// ===========================================
// CACHE STRATEGIES
// ===========================================

// Cache First Strategy
async function cacheFirst(request) {
    const cached = await caches.match(request);
    if (cached) {
        return cached;
    }
    
    try {
        const response = await fetch(request);
        const cache = await caches.open(DYNAMIC_CACHE);
        cache.put(request, response.clone());
        return response;
    } catch (error) {
        return getOfflineFallback(request);
    }
}

// Network First Strategy
async function networkFirst(request) {
    try {
        const response = await fetch(request);
        const cache = await caches.open(DYNAMIC_CACHE);
        cache.put(request, response.clone());
        return response;
    } catch (error) {
        const cached = await caches.match(request);
        if (cached) {
            return cached;
        }
        return getOfflineFallback(request);
    }
}

// Stale While Revalidate Strategy
async function staleWhileRevalidate(request) {
    const cached = await caches.match(request);
    
    const fetchPromise = fetch(request).then((response) => {
        const cache = caches.open(DYNAMIC_CACHE);
        cache.then((c) => c.put(request, response.clone()));
        return response;
    });
    
    return cached || fetchPromise;
}

// Network Only Strategy
async function networkOnly(request) {
    return fetch(request);
}

// Cache Only Strategy
async function cacheOnly(request) {
    return caches.match(request);
}

// ===========================================
// HELPER FUNCTIONS
// ===========================================

function handleRequest(request, strategy) {
    switch (strategy) {
        case CacheStrategy.CACHE_FIRST:
            return cacheFirst(request);
        case CacheStrategy.NETWORK_FIRST:
            return networkFirst(request);
        case CacheStrategy.STALE_WHILE_REVALIDATE:
            return staleWhileRevalidate(request);
        case CacheStrategy.NETWORK_ONLY:
            return networkOnly(request);
        case CacheStrategy.CACHE_ONLY:
            return cacheOnly(request);
        default:
            return networkFirst(request);
    }
}

function handleNavigationRequest(request) {
    return networkFirst(request).catch(() => {
        return caches.match('/offline');
    });
}

function handleImageRequest(request) {
    return cacheFirst(request).catch(() => {
        return caches.match('/pwa/images/offline.png');
    });
}

function handleStaticAsset(request) {
    return cacheFirst(request);
}

function isNavigationRequest(request) {
    return request.mode === 'navigate';
}

function isImageRequest(request) {
    return request.destination === 'image';
}

function isStaticAsset(request) {
    const url = new URL(request.url);
    return url.pathname.match(/\.(css|js|woff|woff2|ttf|eot)$/);
}

async function getOfflineFallback(request) {
    if (isNavigationRequest(request)) {
        return caches.match('/offline');
    }
    
    if (isImageRequest(request)) {
        return caches.match('/pwa/images/offline.png');
    }
    
    return new Response('Offline', {
        status: 503,
        statusText: 'Service Unavailable',
    });
}

async function syncData() {
    // Implement your data sync logic here
    console.log('[SW] Syncing data...');
    
    try {
        // Example: sync pending requests from IndexedDB
        // const db = await openDB();
        // const pending = await db.getAllFromIndex('requests', 'pending');
        // await Promise.all(pending.map(req => fetch(req)));
        
        return Promise.resolve();
    } catch (error) {
        console.error('[SW] Sync failed:', error);
        return Promise.reject(error);
    }
}

async function periodicSync() {
    // Implement periodic sync logic
    console.log('[SW] Running periodic sync...');
    
    try {
        // Example: fetch latest data
        // const response = await fetch('/api/latest-data');
        // const data = await response.json();
        // Update cache or IndexedDB
        
        return Promise.resolve();
    } catch (error) {
        console.error('[SW] Periodic sync failed:', error);
        return Promise.reject(error);
    }
}

// ===========================================
// NAVIGATION PRELOAD
// ===========================================
if (self.registration.navigationPreload) {
    self.addEventListener('activate', (event) => {
        event.waitUntil(
            self.registration.navigationPreload.enable()
        );
    });
}

console.log('[SW] Service Worker loaded successfully!', CACHE_VERSION);