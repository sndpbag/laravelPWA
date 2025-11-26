# SNDP PWA - Installation Guide

## বিস্তারিত Installation গাইড

### Requirements

- PHP 8.1 or higher
- Laravel 10.x or 11.x
- Composer
- GD Library or Imagick (for icon generation)
- HTTPS enabled (production এর জন্য)

---

## Step-by-Step Installation

### ধাপ ১: Package Install করুন

```bash
composer require sndpbag/sndppwa
```

### ধাপ ২: Package Setup করুন

```bash
php artisan pwa:install
```

এই command run করলে:
- ✅ `config/pwa.php` তৈরি হবে
- ✅ Views publish হবে `resources/views/vendor/pwa/`
- ✅ Assets publish হবে `public/pwa/`
- ✅ Service Worker তৈরি হবে `public/sw.js`
- ✅ Required directories তৈরি হবে

### ধাপ ৩: Layout File এ Meta Tags Add করুন

আপনার main layout file খুলুন (সাধারণত `resources/views/layouts/app.blade.php`)

`<head>` section এর মধ্যে add করুন:

```blade
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>{{ config('app.name') }}</title>
    
    {{-- PWA Meta Tags --}}
    @include('pwa::meta')
    
    {{-- Your CSS --}}
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    @yield('content')
    
    {{-- Your JS --}}
    <script src="{{ asset('js/app.js') }}"></script>
    
    {{-- PWA Helper JS (Optional) --}}
    <script src="{{ asset('pwa/js/pwa.js') }}"></script>
</body>
</html>
```

### ধাপ ৪: Icons Generate করুন

আপনার logo image থেকে সব PWA icons automatically generate করুন:

```bash
php artisan pwa:generate-icons public/logo.png
```

**Note:** Logo image minimum 512x512 pixels হওয়া উচিত।

এই command automatically generate করবে:
- Regular icons: 72x72, 96x96, 128x128, 144x144, 152x152, 192x192, 384x384, 512x512
- Maskable icons: 192x192, 512x512
- Splash screens: বিভিন্ন iOS device এর জন্য
- Favicons: 16x16, 32x32, apple-touch-icon

### ধাপ ৫: Configuration Setup

`config/pwa.php` file open করে আপনার প্রয়োজন অনুযায়ী configure করুন:

```php
return [
    // App Basic Info
    'name' => env('APP_NAME', 'My Laravel PWA'),
    'short_name' => env('PWA_SHORT_NAME', 'PWA'),
    'description' => 'A Progressive Web Application built with Laravel',
    
    // Colors
    'theme_color' => '#4f46e5',
    'background_color' => '#ffffff',
    
    // Display Mode
    'display' => 'standalone', // fullscreen, standalone, minimal-ui, browser
    
    // Service Worker Settings
    'serviceworker' => [
        'enabled' => true,
        'cache_strategy' => 'NetworkFirst', // CacheFirst, NetworkFirst, StaleWhileRevalidate
        'cache_version' => 'v1.0.0',
        'auto_update' => true,
        'navigation_preload' => true,
    ],
    
    // Assets to Pre-cache
    'offline' => [
        'enabled' => true,
        'precache' => [
            '/',
            '/offline',
            '/css/app.css',
            '/js/app.js',
        ],
    ],
];
```

### ধাপ ৬: Test করুন

Local development server run করুন:

```bash
php artisan serve
```

Browser এ গিয়ে test করুন:
1. DevTools খুলুন (F12)
2. Application tab এ যান
3. Manifest check করুন: সব info সঠিক আছে কিনা
4. Service Workers check করুন: registered আছে কিনা
5. Lighthouse audit run করুন PWA score দেখার জন্য

### ধাপ ৭: PWA Audit করুন

```bash
php artisan pwa:audit
```

এটি আপনার PWA configuration check করে report দেবে।

---

## Advanced Setup

### 1. App Shortcuts Configure করুন

`config/pwa.php` তে shortcuts add করুন:

```php
'shortcuts' => [
    [
        'name' => 'Dashboard',
        'short_name' => 'Dashboard',
        'description' => 'Go to Dashboard',
        'url' => '/dashboard',
        'icons' => [
            [
                'src' => '/pwa/icons/home.png',
                'sizes' => '96x96',
            ]
        ],
    ],
    [
        'name' => 'New Post',
        'short_name' => 'New Post',
        'description' => 'Create New Post',
        'url' => '/posts/create',
        'icons' => [
            [
                'src' => '/pwa/icons/edit.png',
                'sizes' => '96x96',
            ]
        ],
    ],
],
```

### 2. Biometric Authentication Setup

WebAuthn enable করতে, আপনার User model এ একটি field add করুন:

```php
// Migration
Schema::table('users', function (Blueprint $table) {
    $table->json('webauthn_credentials')->nullable();
});
```

Frontend এ biometric registration:

```javascript
// Register biometric
document.getElementById('register-biometric').addEventListener('click', async () => {
    const success = await sndpPwa.registerBiometric();
    if (success) {
        alert('Biometric authentication registered!');
    }
});
```

### 3. Push Notifications Setup

VAPID keys generate করুন:

```bash
npx web-push generate-vapid-keys
```

`.env` তে add করুন:

```env
VAPID_PUBLIC_KEY=your-public-key
VAPID_PRIVATE_KEY=your-private-key
```

Frontend এ push subscription:

```javascript
const vapidPublicKey = 'your-public-key';
const subscription = await sndpPwa.subscribePushNotifications(vapidPublicKey);

// Send subscription to server
await fetch('/api/push-subscribe', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify(subscription)
});
```

### 4. Trusted Web Activity (TWA) - Play Store Setup

Play Store এ publish করতে TWA setup করুন:

```bash
php artisan pwa:setup-twa
```

এটি জিজ্ঞেস করবে:
1. Android package name (e.g., `com.example.myapp`)
2. SHA-256 certificate fingerprint

Certificate fingerprint পেতে:

```bash
keytool -list -v -keystore your-release-keystore.jks
```

### 5. Content Security Policy (CSP)

CSP middleware routes এ add করুন:

```php
// routes/web.php
Route::middleware(['pwa.csp'])->group(function () {
    Route::get('/', [HomeController::class, 'index']);
    // ... other routes
});
```

---

## Production Deployment

### 1. HTTPS Enable করুন

PWA production এ শুধুমাত্র HTTPS এ কাজ করে। আপনার server এ SSL certificate setup করুন।

### 2. Service Worker Cache Version Update করুন

যখন নতুন features deploy করবেন, cache version update করুন:

```php
// config/pwa.php
'serviceworker' => [
    'cache_version' => 'v1.0.1', // Update this
],
```

### 3. Assets Optimize করুন

```bash
npm run build
php artisan optimize
```

### 4. Test করুন

- Chrome DevTools > Lighthouse > Run PWA audit
- Score 90+ হওয়া উচিত

---

## Troubleshooting

### Service Worker Register হচ্ে না?

1. Check করুন HTTPS enabled আছে কিনা (local এ localhost ব্যবহার করুন)
2. Browser console check করুন error এর জন্য
3. Dev mode disable করুন: `APP_DEBUG=false`

### Icons Show হচ্ছে না?

1. Run: `php artisan pwa:generate-icons public/logo.png`
2. Check: `public/pwa/icons/` directory তে icons আছে কিনা
3. Browser cache clear করুন

### Offline Page কাজ করছে না?

1. Check: `/offline` route accessible কিনা
2. Service Worker console check করুন
3. Precache list এ offline page আছে কিনা

### Install Prompt Show হচ্ছে না?

1. Check: Manifest valid কিনা
2. Check: Service Worker registered কিনা
3. Check: HTTPS enabled কিনা
4. Note: iPhone Safari তে auto-prompt নেই, manually add করতে হয়

---

## Next Steps

✅ আপনার PWA এখন ready!

পরবর্তী পদক্ষেপ:
1. আরও features explore করুন README.md তে
2. Advanced features implement করুন
3. Performance optimize করুন
4. Play Store এ publish করুন (TWA দিয়ে)

---

## Support

সমস্যা হলে GitHub এ issue তৈরি করুন:
https://github.com/sndpbag/sndppwa/issues