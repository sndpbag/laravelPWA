<?php

namespace Sndpbag\Sndppwa\Helpers;

use Sndpbag\Sndppwa\PwaManager;

class PwaHelper
{
    protected $pwa;

    public function __construct(PwaManager $pwa)
    {
        $this->pwa = $pwa;
    }

    /**
     * Get PWA meta tags HTML
     */
    public function metaTags(): string
    {
        return $this->pwa->getMetaTags();
    }

    /**
     * Check if PWA is installable
     */
    public function isInstallable(): bool
    {
        return $this->pwa->isInstallable();
    }

    /**
     * Get manifest URL
     */
    public function manifestUrl(): string
    {
        return route('pwa.manifest');
    }

    /**
     * Get service worker URL
     */
    public function serviceWorkerUrl(): string
    {
        return route('pwa.serviceworker');
    }

    /**
     * Get offline page URL
     */
    public function offlineUrl(): string
    {
        return route('pwa.offline');
    }

    /**
     * Get cache version
     */
    public function cacheVersion(): string
    {
        return $this->pwa->getCacheVersion();
    }

    /**
     * Check if running in standalone mode
     */
    public function isStandalone(): bool
    {
        return request()->header('X-Requested-With') === 'PWA' ||
               isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'PWA';
    }

    /**
     * Get app name
     */
    public function appName(): string
    {
        return config('pwa.name', config('app.name'));
    }

    /**
     * Get short name
     */
    public function shortName(): string
    {
        return config('pwa.short_name', 'PWA');
    }

    /**
     * Get theme color
     */
    public function themeColor(): string
    {
        return config('pwa.theme_color', '#4f46e5');
    }

    /**
     * Get background color
     */
    public function backgroundColor(): string
    {
        return config('pwa.background_color', '#ffffff');
    }

    /**
     * Check if feature is enabled
     */
    public function isFeatureEnabled(string $feature): bool
    {
        return config("pwa.{$feature}.enabled", false);
    }

    /**
     * Get install button HTML
     */
    public function installButton(string $text = 'Install App', string $class = 'pwa-install-btn'): string
    {
        return <<<HTML
        <button id="pwa-install-btn" class="{$class}" onclick="sndpPwa.install()" style="display: none;">
            {$text}
        </button>
        HTML;
    }
}