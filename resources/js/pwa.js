/**
 * SNDP PWA Helper Library
 * Advanced PWA Features Integration
 */

class SndpPwa {
    constructor() {
        this.deferredPrompt = null;
        this.wakeLock = null;
        this.init();
    }

    /**
     * Initialize PWA features
     */
    init() {
        this.setupInstallPrompt();
        this.setupOnlineOfflineDetection();
        this.setupServiceWorkerCommunication();
        
        if (this.isStandalone()) {
            console.log('📱 Running in standalone mode');
        }
    }

    /**
     * Check if app is running in standalone mode
     */
    isStandalone() {
        return window.matchMedia('(display-mode: standalone)').matches ||
               window.navigator.standalone === true;
    }

    /**
     * Setup install prompt
     */
    setupInstallPrompt() {
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            this.deferredPrompt = e;
            this.showInstallButton();
        });

        window.addEventListener('appinstalled', () => {
            console.log('✅ PWA installed');
            this.hideInstallButton();
            this.deferredPrompt = null;
        });
    }

    /**
     * Prompt user to install PWA
     */
    async install() {
        if (!this.deferredPrompt) {
            console.warn('Install prompt not available');
            return false;
        }

        this.deferredPrompt.prompt();
        const { outcome } = await this.deferredPrompt.userChoice;
        
        console.log(`User ${outcome} the install prompt`);
        this.deferredPrompt = null;
        
        return outcome === 'accepted';
    }

    /**
     * Show install button
     */
    showInstallButton() {
        const installBtn = document.getElementById('pwa-install-btn');
        if (installBtn) {
            installBtn.style.display = 'block';
        }
        
        // Dispatch custom event
        window.dispatchEvent(new CustomEvent('pwa-installable'));
    }

    /**
     * Hide install button
     */
    hideInstallButton() {
        const installBtn = document.getElementById('pwa-install-btn');
        if (installBtn) {
            installBtn.style.display = 'none';
        }
    }

    /**
     * Setup online/offline detection
     */
    setupOnlineOfflineDetection() {
        window.addEventListener('online', () => {
            console.log('📶 Back online');
            window.dispatchEvent(new CustomEvent('pwa-online'));
        });

        window.addEventListener('offline', () => {
            console.log('📵 Gone offline');
            window.dispatchEvent(new CustomEvent('pwa-offline'));
        });
    }

    /**
     * Check if device is online
     */
    isOnline() {
        return navigator.onLine;
    }

    /**
     * Setup service worker communication
     */
    setupServiceWorkerCommunication() {
        if ('serviceWorker' in navigator && navigator.serviceWorker.controller) {
            navigator.serviceWorker.addEventListener('message', (event) => {
                this.handleServiceWorkerMessage(event.data);
            });
        }
    }

    /**
     * Handle messages from service worker
     */
    handleServiceWorkerMessage(data) {
        if (data.type === 'UPDATE_AVAILABLE') {
            this.showUpdateNotification();
        }
    }

    /**
     * Show update notification
     */
    showUpdateNotification() {
        if (confirm('🆕 New version available! Reload to update?')) {
            window.location.reload();
        }
    }

    /**
     * Send message to service worker
     */
    sendMessageToSW(message) {
        if (navigator.serviceWorker.controller) {
            navigator.serviceWorker.controller.postMessage(message);
        }
    }

    /**
     * Clear all caches
     */
    async clearCache() {
        this.sendMessageToSW({ type: 'CLEAR_CACHE' });
        console.log('🗑️ Cache cleared');
    }

    /**
     * Cache specific URLs
     */
    async cacheUrls(urls) {
        this.sendMessageToSW({ type: 'CACHE_URLS', urls });
        console.log('💾 URLs cached:', urls);
    }

    // ===========================================
    // BACKGROUND SYNC
    // ===========================================

    /**
     * Register background sync
     */
    async registerSync(tag = 'sync-data') {
        if ('serviceWorker' in navigator && 'sync' in self.registration) {
            try {
                await navigator.serviceWorker.ready;
                await self.registration.sync.register(tag);
                console.log('🔄 Background sync registered:', tag);
                return true;
            } catch (error) {
                console.error('Background sync registration failed:', error);
                return false;
            }
        }
        return false;
    }

    /**
     * Register periodic background sync
     */
    async registerPeriodicSync(tag = 'periodic-sync', minInterval = 24 * 60 * 60 * 1000) {
        if ('serviceWorker' in navigator && 'periodicSync' in self.registration) {
            try {
                await navigator.serviceWorker.ready;
                await self.registration.periodicSync.register(tag, {
                    minInterval: minInterval
                });
                console.log('🔄 Periodic sync registered:', tag);
                return true;
            } catch (error) {
                console.error('Periodic sync registration failed:', error);
                return false;
            }
        }
        return false;
    }

    // ===========================================
    // APP BADGE API
    // ===========================================

    /**
     * Set app badge count
     */
    async setBadge(count) {
        if ('setAppBadge' in navigator) {
            try {
                await navigator.setAppBadge(count);
                console.log('🔔 Badge set:', count);
                return true;
            } catch (error) {
                console.error('Badge API error:', error);
                return false;
            }
        }
        return false;
    }

    /**
     * Clear app badge
     */
    async clearBadge() {
        if ('clearAppBadge' in navigator) {
            try {
                await navigator.clearAppBadge();
                console.log('🔕 Badge cleared');
                return true;
            } catch (error) {
                console.error('Badge API error:', error);
                return false;
            }
        }
        return false;
    }

    // ===========================================
    // WAKE LOCK API
    // ===========================================

    /**
     * Request wake lock to keep screen on
     */
    async requestWakeLock() {
        if ('wakeLock' in navigator) {
            try {
                this.wakeLock = await navigator.wakeLock.request('screen');
                console.log('🔒 Wake lock activated');
                
                this.wakeLock.addEventListener('release', () => {
                    console.log('🔓 Wake lock released');
                });
                
                return true;
            } catch (error) {
                console.error('Wake lock error:', error);
                return false;
            }
        }
        return false;
    }

    /**
     * Release wake lock
     */
    async releaseWakeLock() {
        if (this.wakeLock !== null) {
            await this.wakeLock.release();
            this.wakeLock = null;
            return true;
        }
        return false;
    }

    // ===========================================
    // CONTACT PICKER API
    // ===========================================

    /**
     * Pick contact from device
     */
    async pickContact(props = ['name', 'email', 'tel'], multiple = false) {
        if ('contacts' in navigator && 'ContactsManager' in window) {
            try {
                const contacts = await navigator.contacts.select(props, { multiple });
                console.log('📇 Contacts selected:', contacts);
                return contacts;
            } catch (error) {
                console.error('Contact picker error:', error);
                return null;
            }
        }
        console.warn('Contact Picker API not supported');
        return null;
    }

    // ===========================================
    // WEB SHARE API
    // ===========================================

    /**
     * Share content using Web Share API
     */
    async share(data) {
        if (navigator.share) {
            try {
                await navigator.share(data);
                console.log('✅ Content shared');
                return true;
            } catch (error) {
                console.error('Share error:', error);
                return false;
            }
        }
        console.warn('Web Share API not supported');
        return false;
    }

    // ===========================================
    // PUSH NOTIFICATIONS
    // ===========================================

    /**
     * Request notification permission
     */
    async requestNotificationPermission() {
        if ('Notification' in window) {
            const permission = await Notification.requestPermission();
            console.log('🔔 Notification permission:', permission);
            return permission === 'granted';
        }
        return false;
    }

    /**
     * Show local notification
     */
    async showNotification(title, options = {}) {
        if ('Notification' in window && Notification.permission === 'granted') {
            const registration = await navigator.serviceWorker.ready;
            await registration.showNotification(title, {
                icon: '/pwa/icons/icon-192x192.png',
                badge: '/pwa/icons/icon-72x72.png',
                ...options
            });
            return true;
        }
        return false;
    }

    /**
     * Subscribe to push notifications
     */
    async subscribePushNotifications(vapidPublicKey) {
        try {
            const registration = await navigator.serviceWorker.ready;
            const subscription = await registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: this.urlBase64ToUint8Array(vapidPublicKey)
            });
            
            console.log('✅ Push subscription:', subscription);
            return subscription;
        } catch (error) {
            console.error('Push subscription error:', error);
            return null;
        }
    }

    /**
     * Convert VAPID key
     */
    urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - base64String.length % 4) % 4);
        const base64 = (base64String + padding).replace(/\-/g, '+').replace(/_/g, '/');
        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);
        
        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    }

    // ===========================================
    // WEBAUTHN (BIOMETRIC AUTH)
    // ===========================================

    /**
     * Register biometric authentication
     */
    async registerBiometric() {
        try {
            const response = await fetch('/pwa/webauthn/register/options', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            
            const options = await response.json();
            
            // Prepare options for WebAuthn
            options.challenge = this.base64ToBuffer(options.challenge);
            options.user.id = this.base64ToBuffer(options.user.id);
            
            const credential = await navigator.credentials.create({
                publicKey: options
            });
            
            // Send credential to server
            await fetch('/pwa/webauthn/register/verify', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ credential })
            });
            
            console.log('✅ Biometric authentication registered');
            return true;
        } catch (error) {
            console.error('Biometric registration error:', error);
            return false;
        }
    }

    /**
     * Login with biometric authentication
     */
    async loginWithBiometric() {
        try {
            const response = await fetch('/pwa/webauthn/login/options', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            
            const options = await response.json();
            options.challenge = this.base64ToBuffer(options.challenge);
            
            const credential = await navigator.credentials.get({
                publicKey: options
            });
            
            // Verify credential
            const verifyResponse = await fetch('/pwa/webauthn/login/verify', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ credential })
            });
            
            const result = await verifyResponse.json();
            console.log('✅ Biometric login successful');
            return result.success;
        } catch (error) {
            console.error('Biometric login error:', error);
            return false;
        }
    }

    /**
     * Base64 to ArrayBuffer
     */
    base64ToBuffer(base64) {
        const binary = window.atob(base64);
        const bytes = new Uint8Array(binary.length);
        for (let i = 0; i < binary.length; i++) {
            bytes[i] = binary.charCodeAt(i);
        }
        return bytes.buffer;
    }
}

// Initialize PWA helper
const sndpPwa = new SndpPwa();

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = SndpPwa;
}