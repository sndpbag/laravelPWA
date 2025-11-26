# SNDP PWA - Professional Laravel PWA Package

<p align="center">
    <img src="https://img.shields.io/badge/Laravel-10%2B%20%7C%2011%2B-red?style=for-the-badge&logo=laravel" alt="Laravel">
    <img src="https://img.shields.io/badge/PHP-8.1%2B-blue?style=for-the-badge&logo=php" alt="PHP">
    <img src="https://img.shields.io/badge/PWA-Ready-green?style=for-the-badge" alt="PWA">
    <img src="https://img.shields.io/badge/License-MIT-yellow?style=for-the-badge" alt="License">
</p>

**SNDP PWA** হলো Laravel এর জন্য একটি সম্পূর্ণ Professional Progressive Web App (PWA) Package যা আপনার Laravel website কে একটি Native App এর মতো powerful করে তুলবে।

## ✨ Features

### 🟦 Core PWA Features
- ✅ **Manifest.json Generator** - Automatic manifest generation
- ✅ **Service Worker** - Auto-register with multiple cache strategies
- ✅ **Full Offline Support** - Works without internet
- ✅ **Custom Cache Strategies** - CacheFirst, NetworkFirst, StaleWhileRevalidate, etc.
- ✅ **App Icons** - Auto-generate all sizes
- ✅ **Theme Colors** - Customizable theme and background colors
- ✅ **Standalone Mode** - Fullscreen app experience
- ✅ **Add to Home Screen (A2HS)** - Install prompt
- ✅ **Offline Fallback Page** - Beautiful offline page
- ✅ **Auto-versioning** - Service worker version management
- ✅ **Auto-update Popup** - "New version available" notification

### 🟪 Advanced App-Like Features
- 🔹 **App Shortcuts** - Quick actions from home screen
- 🔹 **Biometric Authentication (WebAuthn)** - Fingerprint/FaceID login
- 🔹 **Periodic Background Sync** - Auto-fetch data in background
- 🔹 **Wake Lock API** - Keep screen on
- 🔹 **Contact Picker API** - Access device contacts
- 🔹 **App Badging API** - Show unread count on app icon
- 🔹 **Push Notifications** - Real-time notifications
- 🔹 **Web Share API** - Native sharing

### 🔧 Developer Experience Features
- 🛠️ **Smart Icon Generator** - CLI command to generate all icon sizes
- 🛠️ **TWA / Asset Links** - Play Store ready setup
- 🛠️ **Audit Tool** - Health check for PWA configuration
- 🛠️ **Dev Mode Hot Reload** - Auto cache clear in development
- 🛠️ **Navigation Preload** - Ultra-fast first load

### 🔐 Security Features
- 🔒 **Maskable Icons** - Android adaptive shapes
- 🔒 **Content Security Policy (CSP)** - Auto-secure headers
- 🔒 **XSS Protection** - Built-in security headers

## 📦 Installation

### Step 1: Install via Composer

```bash
composer require sndpbag/sndppwa
```

### Step 2: Install Package

```bash
php artisan pwa:install
```

এই command টি automatically:
- Config file publish করবে
- Views publish করবে
- Assets publish করবে
- Service Worker publish করবে
- Necessary directories তৈরি করবে

### Step 3: Add Meta Tags

আপনার main layout file (`resources/views/layouts/app.blade.php`) এর `<head>` section এ add করুন:

```blade
@include('pwa::meta')
```

### Step 4: Generate Icons

আপনার logo থেকে সব PWA icons generate করুন:

```bash
php artisan pwa:generate-icons public/logo.png
```

এটি automatically generate করবে:
- Regular icons (72x72 থেকে 512x512)
- Maskable icons (192x192, 512x512)
- Splash screens (iOS এর জন্য)
- Favicons (16x16, 32x32, apple-touch-icon)

## ⚙️ Configuration

`config/pwa.php` file এ আপনার PWA configuration করুন:

```php
return [
    'name' => env('APP_NAME', 'My PWA'),
    'short_name' => 'PWA',
    'description' => 'A Progressive Web Application',
    'theme_color' => '#4f46e5',
    'background_color' => '#ffffff',
    'display' => 'standalone',
    
    // Cache Strategy
    'serviceworker' => [
        'cache_strategy' => 'NetworkFirst', // CacheFirst, NetworkFirst, StaleWhileRevalidate
        'cache_version' => 'v1.0.0',
        'auto_update' => true,
        'navigation_preload' => true,
    ],
    
    // Offline Support
    'offline' => [
        'enabled' => true,
        'precache' => [
            '/',
            '/css/app.css',
            '/js/app.js',
        ],
    ],
    
    // Advanced Features
    'background_sync' => ['enabled' => true],
    'app_badge' => ['enabled' => true],
    'wake_lock' => ['enabled' => true],
    'contact_picker' => ['enabled' => true],
    'webauthn' => ['enabled' => true],
];
```

## 🚀 Usage

### Basic Usage

একবার install করলে, আপনার website automatically PWA হয়ে যাবে! Users install prompt পাবে এবং home screen এ add করতে পারবে।

### Advanced Features Usage

#### 1. App Shortcuts (Quick Actions)

`config/pwa.php` তে shortcuts add করুন:

```php
'shortcuts' => [
    [
        'name' => 'Dashboard',
        'short_name' => 'Dashboard',
        'description' => 'Go to Dashboard',
        'url' => '/dashboard',
        'icons' => [['src' => '/pwa/icons/home.png', 'sizes' => '96x96']],
    ],
    [
        'name' => 'Profile',
        'short_name' => 'Profile',
        'description' => 'View Profile',
        'url' => '/profile',
        'icons' => [['src' => '/pwa/icons/profile.png', 'sizes' => '96x96']],
    ],
],
```

#### 2. Biometric Authentication (WebAuthn)

Frontend এ biometric authentication enable করুন:

```javascript
// Register biometric
await sndpPwa.registerBiometric();

// Login with biometric
const success = await sndpPwa.loginWithBiometric();
if (success) {
    window.location.href = '/dashboard';
}
```

#### 3. Background Sync

Offline এ data submit করুন, online হলে auto-sync হবে:

```javascript
// Register background sync
await sndpPwa.registerSync('sync-data');

// Periodic sync (daily update)
await sndpPwa.registerPeriodicSync('periodic-sync', 24 * 60 * 60 * 1000);
```

#### 4. App Badge (Unread Count)

App icon এ unread count show করুন:

```javascript
// Set badge count
await sndpPwa.setBadge(5);

// Clear badge
await sndpPwa.clearBadge();
```

#### 5. Wake Lock (Keep Screen On)

Video বা reading app এর জন্য screen on রাখুন:

```javascript
// Request wake lock
await sndpPwa.requestWakeLock();

// Release wake lock
await sndpPwa.releaseWakeLock();
```

#### 6. Contact Picker

User এর contacts access করুন:

```javascript
const contacts = await sndpPwa.pickContact(['name', 'tel'], false);
console.log(contacts);
```

#### 7. Push Notifications

```javascript
// Request permission
await sndpPwa.requestNotificationPermission();

// Show notification
await sndpPwa.showNotification('New Message', {
    body: 'You have a new message',
    icon: '/pwa/icons/icon-192x192.png',
});

// Subscribe to push
const subscription = await sndpPwa.subscribePushNotifications(vapidPublicKey);
```

#### 8. Install Prompt

Custom install button তৈরি করুন:

```html
<button id="pwa-install-btn" onclick="sndpPwa.install()" style="display: none;">
    📥 Install App
</button>
```

## 🎨 Customization

### Custom Offline Page

`resources/views/vendor/pwa/offline.blade.php` edit করে custom offline page তৈরি করুন।

### Custom Service Worker

`public/sw.js` file edit করে custom caching logic add করুন।

### Custom Cache Strategy

আলাদা routes এর জন্য আলাদা cache strategy:

```javascript
// In your JavaScript
if (request.url.includes('/api/')) {
    // Use NetworkFirst for API
    return networkFirst(request);
} else if (request.url.includes('/images/')) {
    // Use CacheFirst for images
    return cacheFirst(request);
}
```

## 🔍 Audit & Testing

PWA configuration check করুন:

```bash
php artisan pwa:audit
```

Output:
```
✅ Passed Checks:
  • HTTPS enabled
  • manifest.json exists
  • Service Worker file exists
  • All configured icons exist

⚠️ Warnings:
  • Offline page not optimized

📊 PWA Health Score: 95%
🎉 Excellent! Your PWA is well configured.
```

## 📱 Trusted Web Activity (TWA) - Play Store

Play Store এ publish করার জন্য TWA setup করুন:

```bash
php artisan pwa:setup-twa
```

এটি automatically:
- `assetlinks.json` generate করবে
- Android package name configure করবে
- Certificate fingerprints setup করবে

## 🛠️ Available Commands

| Command | Description |
|---------|-------------|
| `php artisan pwa:install` | Install package and publish files |
| `php artisan pwa:generate-icons {source}` | Generate all PWA icons from source image |
| `php artisan pwa:audit` | Check PWA configuration health |
| `php artisan pwa:publish` | Publish package files |
| `php artisan pwa:setup-twa` | Setup Trusted Web Activity for Play Store |

## 🌐 Browser Support

| Feature | Chrome | Firefox | Safari | Edge |
|---------|--------|---------|--------|------|
| Service Worker | ✅ | ✅ | ✅ | ✅ |
| Manifest | ✅ | ✅ | ✅ | ✅ |
| WebAuthn | ✅ | ✅ | ✅ | ✅ |
| Background Sync | ✅ | ❌ | ❌ | ✅ |
| Periodic Sync | ✅ | ❌ | ❌ | ✅ |
| App Badge | ✅ | ❌ | ❌ | ✅ |
| Wake Lock | ✅ | ❌ | ❌ | ✅ |
| Contact Picker | ✅ | ❌ | ❌ | ✅ |

## 📝 License

MIT License

## 👨‍💻 Author

**sndpbag**
- GitHub: [@sndpbag](https://github.com/sndpbag)

## 🤝 Contributing

Contributions, issues এবং feature requests স্বাগত!

## ⭐ Show your support

এই project helpful লাগলে একটা ⭐ দিন!

## 📚 Resources

- [Progressive Web Apps - MDN](https://developer.mozilla.org/en-US/docs/Web/Progressive_web_apps)
- [Web.dev PWA Guide](https://web.dev/progressive-web-apps/)
- [WebAuthn Guide](https://webauthn.guide/)
- [Service Worker API](https://developer.mozilla.org/en-US/docs/Web/API/Service_Worker_API)