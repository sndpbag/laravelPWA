# SNDP PWA - Package Structure

## 📁 Complete Folder Structure

```
sndppwa/
├── 📄 composer.json                          # Package dependencies and autoload
├── 📄 LICENSE                                 # MIT License
├── 📄 README.md                               # Main documentation
├── 📄 CHANGELOG.md                            # Version history
├── 📄 INSTALLATION.md                         # Detailed installation guide
├── 📄 .gitignore                              # Git ignore rules
│
├── 📂 config/
│   └── 📄 pwa.php                             # Main PWA configuration file
│
├── 📂 src/
│   ├── 📄 SndppwaServiceProvider.php          # Laravel service provider
│   ├── 📄 PwaManager.php                      # Core PWA manager class
│   │
│   ├── 📂 Console/
│   │   └── 📂 Commands/
│   │       ├── 📄 InstallCommand.php          # php artisan pwa:install
│   │       ├── 📄 GenerateIconsCommand.php    # php artisan pwa:generate-icons
│   │       ├── 📄 AuditCommand.php            # php artisan pwa:audit
│   │       ├── 📄 PublishCommand.php          # php artisan pwa:publish
│   │       └── 📄 SetupTwaCommand.php         # php artisan pwa:setup-twa
│   │
│   ├── 📂 Http/
│   │   ├── 📂 Controllers/
│   │   │   ├── 📄 PwaController.php           # Manifest, SW, Offline routes
│   │   │   └── 📄 WebAuthnController.php      # Biometric authentication
│   │   │
│   │   └── 📂 Middleware/
│   │       ├── 📄 ContentSecurityPolicy.php   # CSP headers
│   │       └── 📄 OfflineMiddleware.php       # Offline detection
│   │
│   ├── 📂 Helpers/
│   │   └── 📄 PwaHelper.php                   # Blade helper functions
│   │
│   └── 📂 Facades/
│       └── 📄 Pwa.php                         # Laravel Facade
│
├── 📂 routes/
│   └── 📄 pwa.php                             # Package routes (manifest, sw, etc.)
│
├── 📂 resources/
│   ├── 📂 views/
│   │   ├── 📄 meta.blade.php                  # PWA meta tags include
│   │   └── 📄 offline.blade.php               # Offline fallback page
│   │
│   ├── 📂 js/
│   │   └── 📄 pwa.js                          # PWA helper JavaScript library
│   │
│   └── 📂 images/
│       └── 📄 offline.svg                     # Offline placeholder image
│
└── 📂 stubs/
    └── 📄 service-worker.js                   # Service worker template
```

---

## 📋 File Descriptions

### Root Files

| File | Description |
|------|-------------|
| `composer.json` | Package metadata, dependencies (Laravel 10/11, Intervention Image), autoload PSR-4 |
| `LICENSE` | MIT License |
| `README.md` | Complete documentation with examples |
| `CHANGELOG.md` | Version history and updates |
| `INSTALLATION.md` | Step-by-step installation guide |
| `.gitignore` | Git ignore patterns |

### Configuration

| File | Description |
|------|-------------|
| `config/pwa.php` | Main configuration - app name, colors, icons, cache strategy, features enable/disable |

### Source Code (`src/`)

#### Core Classes

| File | Description |
|------|-------------|
| `SndppwaServiceProvider.php` | Laravel service provider - registers routes, views, commands, middleware |
| `PwaManager.php` | Core PWA logic - manifest generation, meta tags, cache version |

#### Console Commands

| File | Command | Description |
|------|---------|-------------|
| `InstallCommand.php` | `pwa:install` | Install package, publish files, create directories |
| `GenerateIconsCommand.php` | `pwa:generate-icons` | Generate all icon sizes from source image |
| `AuditCommand.php` | `pwa:audit` | Health check - HTTPS, manifest, SW, icons validation |
| `PublishCommand.php` | `pwa:publish` | Publish config, views, assets |
| `SetupTwaCommand.php` | `pwa:setup-twa` | Setup Trusted Web Activity for Play Store |

#### HTTP Layer

**Controllers:**

| File | Routes | Description |
|------|--------|-------------|
| `PwaController.php` | `/manifest.json`, `/sw.js`, `/offline`, `/.well-known/assetlinks.json` | Serves manifest, service worker, offline page, TWA asset links |
| `WebAuthnController.php` | `/pwa/webauthn/*` | Biometric authentication - register/login with fingerprint/FaceID |

**Middleware:**

| File | Alias | Description |
|------|-------|-------------|
| `ContentSecurityPolicy.php` | `pwa.csp` | Adds CSP headers, XSS protection, frame options |
| `OfflineMiddleware.php` | `pwa.offline` | Adds offline-related headers |

#### Helpers & Facades

| File | Description |
|------|-------------|
| `PwaHelper.php` | Helper methods for Blade templates |
| `Pwa.php` | Laravel Facade for PWA methods |

### Routes

| File | Description |
|------|-------------|
| `routes/pwa.php` | Package routes - manifest, service worker, offline, WebAuthn endpoints |

### Resources

#### Views

| File | Include Syntax | Description |
|------|----------------|-------------|
| `meta.blade.php` | `@include('pwa::meta')` | PWA meta tags, service worker registration, install prompt, badge API, wake lock, contact picker |
| `offline.blade.php` | Auto-served at `/offline` | Beautiful offline fallback page with retry button |

#### JavaScript

| File | Usage | Description |
|------|-------|-------------|
| `pwa.js` | `<script src="/pwa/js/pwa.js"></script>` | PWA helper library - `sndpPwa` object with all advanced features |

#### Images

| File | Description |
|------|-------------|
| `offline.svg` | Offline placeholder image |

### Stubs

| File | Published To | Description |
|------|--------------|-------------|
| `service-worker.js` | `public/sw.js` | Advanced service worker - cache strategies, background sync, push notifications, navigation preload |

---

## 🔧 How Components Work Together

### 1. Installation Flow

```
php artisan pwa:install
    ↓
InstallCommand.php
    ↓
Publishes: config, views, assets, service worker
    ↓
Creates: directories (public/pwa/icons, etc.)
```

### 2. Icon Generation Flow

```
php artisan pwa:generate-icons logo.png
    ↓
GenerateIconsCommand.php
    ↓
Uses: Intervention Image
    ↓
Generates: Regular icons (72-512px)
          Maskable icons (192, 512px)
          Splash screens (iOS sizes)
          Favicons (16, 32, apple-touch-icon)
    ↓
Saves to: public/pwa/icons/, public/pwa/splash/
```

### 3. Runtime Flow

```
User visits website
    ↓
@include('pwa::meta') loads
    ↓
Registers service worker (sw.js)
    ↓
Service worker caches assets
    ↓
User goes offline
    ↓
Service worker serves cached content
    ↓
Navigation request → /offline page
```

### 4. Manifest Generation

```
User requests /manifest.json
    ↓
PwaController@manifest
    ↓
PwaManager->generateManifest()
    ↓
Reads config/pwa.php
    ↓
Returns JSON with app info, icons, shortcuts
```

---

## 🎯 Key Features Implementation

### Service Worker (sw.js)

**Features Implemented:**
- ✅ Install event - Pre-caching
- ✅ Activate event - Cleanup old caches
- ✅ Fetch event - Request interception
- ✅ Cache strategies (CacheFirst, NetworkFirst, StaleWhileRevalidate)
- ✅ Background sync event
- ✅ Periodic sync event
- ✅ Push notification event
- ✅ Notification click event
- ✅ Navigation preload
- ✅ Message event (communication with pages)

### PWA Helper (pwa.js)

**Class: `SndpPwa`**

**Methods Available:**
- `install()` - Trigger install prompt
- `isOnline()` - Check connection status
- `registerSync(tag)` - Background sync
- `registerPeriodicSync(tag, interval)` - Periodic sync
- `setBadge(count)` - Set app badge
- `clearBadge()` - Clear app badge
- `requestWakeLock()` - Keep screen on
- `releaseWakeLock()` - Release wake lock
- `pickContact(props, multiple)` - Pick device contacts
- `share(data)` - Web Share API
- `requestNotificationPermission()` - Ask for notification permission
- `showNotification(title, options)` - Show notification
- `subscribePushNotifications(vapidKey)` - Subscribe to push
- `registerBiometric()` - Register fingerprint/FaceID
- `loginWithBiometric()` - Login with biometrics

---

## 🔌 Integration Points

### Laravel Integration

1. **Service Provider** (`SndppwaServiceProvider.php`)
   - Registers in `composer.json` > `extra.laravel.providers`
   - Auto-discovered by Laravel

2. **Configuration**
   - Published to `config/pwa.php`
   - Accessible via `config('pwa.key')`

3. **Routes**
   - Loaded from `routes/pwa.php`
   - Automatically included by service provider

4. **Views**
   - Namespace: `pwa::`
   - Include: `@include('pwa::meta')`

5. **Middleware**
   - Registered aliases: `pwa.csp`, `pwa.offline`
   - Usage: `Route::middleware(['pwa.csp'])`

6. **Facades**
   - `Pwa::generateManifest()`
   - `Pwa::getMetaTags()`

### Frontend Integration

1. **Include Meta Tags**
   ```blade
   @include('pwa::meta')
   ```

2. **Use PWA Helper**
   ```html
   <script src="{{ asset('pwa/js/pwa.js') }}"></script>
   <script>
       sndpPwa.install();
   </script>
   ```

3. **Install Button**
   ```html
   <button id="pwa-install-btn" onclick="sndpPwa.install()">Install</button>
   ```

---

## 📊 Data Flow Diagrams

### Install Prompt Flow

```
Page Load
    ↓
Service Worker Registers
    ↓
Browser Checks PWA Criteria
    ↓
`beforeinstallprompt` Event Fires
    ↓
Event Captured by pwa.js
    ↓
Install Button Shows
    ↓
User Clicks Install
    ↓
`sndpPwa.install()` Called
    ↓
Browser Shows Install Dialog
    ↓
User Accepts/Declines
    ↓
`appinstalled` Event (if accepted)
```

### Offline Handling Flow

```
User Goes Offline
    ↓
Service Worker Intercepts Fetch
    ↓
Network Request Fails
    ↓
Check Cache
    ↓
If Navigation: Serve /offline
If Image: Serve placeholder
If Asset: Serve from cache
    ↓
User Sees Offline Content
```

### Background Sync Flow

```
User Submits Form Offline
    ↓
Store Request in IndexedDB (user code)
    ↓
Register Background Sync
    ↓
User Comes Online
    ↓
`sync` Event Fires
    ↓
Service Worker Processes Queue
    ↓
Sends Pending Requests
    ↓
Success!
```

---

## 🚀 Extension Points

Package টি easily extend করা যায়:

1. **Custom Service Worker Logic**
   - Edit `public/sw.js` after publishing

2. **Custom Offline Page**
   - Edit `resources/views/vendor/pwa/offline.blade.php`

3. **Custom Cache Strategies**
   - Modify service worker fetch event

4. **Additional Commands**
   - Create new command extending `Command` class

5. **Custom Routes**
   - Add routes in your app's `routes/web.php`

---

## 📦 Published Files Location

After running `php artisan pwa:install`:

```
Laravel Project/
├── config/
│   └── pwa.php                          # ✅ Published
│
├── public/
│   ├── sw.js                            # ✅ Published
│   └── pwa/
│       ├── icons/                       # ✅ Created
│       ├── splash/                      # ✅ Created
│       ├── images/                      # ✅ Created
│       └── js/
│           └── pwa.js                   # ✅ Published
│
└── resources/
    └── views/
        └── vendor/
            └── pwa/
                ├── meta.blade.php       # ✅ Published
                └── offline.blade.php    # ✅ Published
```

---

এই structure অনুসরণ করে package টি সম্পূর্ণ modular এবং maintainable!