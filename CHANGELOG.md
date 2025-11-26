# Changelog

All notable changes to `sndpbag/sndppwa` will be documented in this file.

## [1.0.0] - 2024-11-26

### Added
- 🎉 Initial release
- ✅ Core PWA features (Manifest, Service Worker, Offline support)
- ✅ Multiple cache strategies (CacheFirst, NetworkFirst, StaleWhileRevalidate)
- ✅ Automatic icon generator command
- ✅ App Shortcuts (Quick Actions)
- ✅ Biometric Authentication (WebAuthn)
- ✅ Periodic Background Sync
- ✅ Wake Lock API integration
- ✅ Contact Picker API integration
- ✅ App Badging API
- ✅ Push Notifications support
- ✅ TWA (Trusted Web Activity) setup for Play Store
- ✅ PWA Audit command for health check
- ✅ Content Security Policy (CSP) middleware
- ✅ Navigation Preload
- ✅ Maskable Icons support
- ✅ Beautiful offline fallback page
- ✅ Auto-update notification
- ✅ Dev mode with hot reload
- ✅ Comprehensive documentation
- ✅ JavaScript helper library (sndpPwa)

### Features
- Full Laravel 10 and 11 support
- PHP 8.1+ support
- Intervention Image integration for icon generation
- Multiple splash screen sizes for iOS
- Customizable theme and background colors
- Automatic service worker versioning
- Install prompt handling
- Online/offline detection
- Web Share API integration

### Commands
- `php artisan pwa:install` - Install and setup PWA
- `php artisan pwa:generate-icons` - Generate all icon sizes
- `php artisan pwa:audit` - Health check
- `php artisan pwa:publish` - Publish files
- `php artisan pwa:setup-twa` - Setup Trusted Web Activity

### Security
- Content Security Policy headers
- XSS protection headers
- Safe service worker sandboxing
- WebAuthn biometric authentication

## [Unreleased]

### Planned Features
- IndexedDB helper utilities
- Offline form submission queue
- Advanced analytics integration
- A/B testing support
- Performance monitoring
- More cache strategy options
- PWA dashboard UI