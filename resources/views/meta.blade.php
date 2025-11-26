{{-- PWA Meta Tags --}}
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="{{ config('pwa.status_bar', 'default') }}">
<meta name="apple-mobile-web-app-title" content="{{ config('pwa.short_name') }}">
<meta name="theme-color" content="{{ config('pwa.theme_color') }}">
<meta name="msapplication-TileColor" content="{{ config('pwa.theme_color') }}">

{{-- Manifest --}}
<link rel="manifest" href="{{ route('pwa.manifest') }}">

{{-- Icons --}}
@foreach(config('pwa.icons', []) as $size => $src)
<link rel="apple-touch-icon" sizes="{{ $size }}" href="{{ $src }}">
@endforeach

{{-- Favicons --}}
<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">

{{-- iOS Splash Screens --}}
@if(config('pwa.splash'))
    @foreach(config('pwa.splash') as $size => $src)
        @php
            [$width, $height] = explode('x', $size);
        @endphp
        <link rel="apple-touch-startup-image" href="{{ $src }}" media="(device-width: {{ $width }}px) and (device-height: {{ $height }}px) and (-webkit-device-pixel-ratio: 2)">
    @endforeach
@endif

{{-- PWA JavaScript --}}
@if(config('pwa.serviceworker.enabled'))
<script>
    if ('serviceWorker' in navigator) {
        @if(config('pwa.dev_mode'))
        // Development mode - unregister service worker
        navigator.serviceWorker.getRegistrations().then(function(registrations) {
            for(let registration of registrations) {
                registration.unregister();
            }
        });
        console.log('🔧 PWA: Development mode - Service Worker disabled');
        @else
        // Production mode - register service worker
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js', { scope: '/' })
                .then(registration => {
                    console.log('✅ PWA: Service Worker registered');
                    
                    // Check for updates
                    @if(config('pwa.serviceworker.auto_update'))
                    setInterval(() => {
                        registration.update();
                    }, {{ config('pwa.serviceworker.update_interval', 3600) }} * 1000);
                    @endif
                    
                    // Handle updates
                    registration.addEventListener('updatefound', () => {
                        const newWorker = registration.installing;
                        newWorker.addEventListener('statechange', () => {
                            if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                // New service worker available
                                if (confirm('🆕 New version available! Reload to update?')) {
                                    window.location.reload();
                                }
                            }
                        });
                    });
                })
                .catch(error => {
                    console.error('❌ PWA: Service Worker registration failed:', error);
                });
        });
        @endif
    }
</script>
@endif

{{-- App Badge API --}}
@if(config('pwa.app_badge.enabled'))
<script>
    // App Badge API
    window.pwaSetBadge = function(count) {
        if ('setAppBadge' in navigator) {
            navigator.setAppBadge(count);
        }
    };
    
    window.pwaClearBadge = function() {
        if ('clearAppBadge' in navigator) {
            navigator.clearAppBadge();
        }
    };
</script>
@endif

{{-- Wake Lock API --}}
@if(config('pwa.wake_lock.enabled'))
<script>
    // Wake Lock API
    let wakeLock = null;
    
    window.pwaRequestWakeLock = async function() {
        if ('wakeLock' in navigator) {
            try {
                wakeLock = await navigator.wakeLock.request('screen');
                console.log('🔒 Wake Lock activated');
            } catch (err) {
                console.error('Wake Lock error:', err);
            }
        }
    };
    
    window.pwaReleaseWakeLock = function() {
        if (wakeLock !== null) {
            wakeLock.release();
            wakeLock = null;
            console.log('🔓 Wake Lock released');
        }
    };
</script>
@endif

{{-- Contact Picker API --}}
@if(config('pwa.contact_picker.enabled'))
<script>
    // Contact Picker API
    window.pwaPickContact = async function() {
        if ('contacts' in navigator && 'ContactsManager' in window) {
            try {
                const props = ['name', 'email', 'tel'];
                const contacts = await navigator.contacts.select(props, { multiple: false });
                return contacts[0];
            } catch (err) {
                console.error('Contact Picker error:', err);
                return null;
            }
        }
        return null;
    };
</script>
@endif

{{-- Install Prompt --}}
<script>
    let deferredPrompt;
    
    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt = e;
        
        // Show install button
        const installBtn = document.getElementById('pwa-install-btn');
        if (installBtn) {
            installBtn.style.display = 'block';
        }
        
        console.log('💾 PWA: Install prompt ready');
    });
    
    window.pwaInstall = async function() {
        if (deferredPrompt) {
            deferredPrompt.prompt();
            const { outcome } = await deferredPrompt.userChoice;
            
            if (outcome === 'accepted') {
                console.log('✅ PWA: User accepted installation');
            } else {
                console.log('❌ PWA: User dismissed installation');
            }
            
            deferredPrompt = null;
        }
    };
    
    window.addEventListener('appinstalled', () => {
        console.log('🎉 PWA: App installed successfully');
        deferredPrompt = null;
    });
</script>