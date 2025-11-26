<?php

namespace Sndpbag\Sndppwa;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;

class PwaManager
{
    protected $app;

    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Generate manifest.json
     */
    public function generateManifest(): array
    {
        $config = config('pwa');

        $manifest = [
            'name' => $config['name'],
            'short_name' => $config['short_name'],
            'description' => $config['description'],
            'start_url' => $config['start_url'],
            'scope' => $config['scope'],
            'display' => $config['display'],
            'orientation' => $config['orientation'],
            'theme_color' => $config['theme_color'],
            'background_color' => $config['background_color'],
            'icons' => $this->formatIcons(),
            'shortcuts' => $config['shortcuts'] ?? [],
        ];

        // Add categories
        $manifest['categories'] = ['productivity', 'utilities'];

        // Add prefer_related_applications
        $manifest['prefer_related_applications'] = false;

        return $manifest;
    }

    /**
     * Format icons for manifest
     */
    protected function formatIcons(): array
    {
        $icons = [];
        $config = config('pwa');

        // Regular icons
        foreach ($config['icons'] as $size => $src) {
            $icons[] = [
                'src' => $src,
                'sizes' => $size,
                'type' => 'image/png',
                'purpose' => 'any',
            ];
        }

        // Maskable icons
        if (isset($config['maskable_icons'])) {
            foreach ($config['maskable_icons'] as $size => $src) {
                $icons[] = [
                    'src' => $src,
                    'sizes' => $size,
                    'type' => 'image/png',
                    'purpose' => 'maskable',
                ];
            }
        }

        return $icons;
    }

    /**
     * Get meta tags for PWA
     */
    public function getMetaTags(): string
    {
        $config = config('pwa');
        
        $tags = [];
        
        // Basic meta tags
        $tags[] = '<meta name="mobile-web-app-capable" content="yes">';
        $tags[] = '<meta name="apple-mobile-web-app-capable" content="yes">';
        $tags[] = '<meta name="apple-mobile-web-app-status-bar-style" content="' . $config['status_bar'] . '">';
        $tags[] = '<meta name="apple-mobile-web-app-title" content="' . $config['short_name'] . '">';
        $tags[] = '<meta name="theme-color" content="' . $config['theme_color'] . '">';
        $tags[] = '<meta name="msapplication-TileColor" content="' . $config['theme_color'] . '">';
        
        // Manifest link
        $tags[] = '<link rel="manifest" href="/manifest.json">';
        
        // Apple touch icons
        foreach ($config['icons'] as $size => $src) {
            $tags[] = '<link rel="apple-touch-icon" sizes="' . $size . '" href="' . $src . '">';
        }
        
        // Splash screens for iOS
        if (isset($config['splash'])) {
            foreach ($config['splash'] as $size => $src) {
                list($width, $height) = explode('x', $size);
                $tags[] = '<link rel="apple-touch-startup-image" href="' . $src . '" media="(device-width: ' . $width . 'px) and (device-height: ' . $height . 'px)">';
            }
        }
        
        return implode("\n    ", $tags);
    }

    /**
     * Check if PWA is installable
     */
    public function isInstallable(): bool
    {
        $manifestExists = File::exists(public_path('manifest.json'));
        $swExists = File::exists(public_path('sw.js'));
        $httpsEnabled = request()->secure() || app()->environment('local');
        
        return $manifestExists && $swExists && $httpsEnabled;
    }

    /**
     * Get cache version
     */
    public function getCacheVersion(): string
    {
        return config('pwa.serviceworker.cache_version', 'v1.0.0');
    }

    /**
     * Generate asset links for TWA
     */
    public function generateAssetLinks(): array
    {
        $config = config('pwa.twa');
        
        return [
            [
                'relation' => ['delegate_permission/common.handle_all_urls'],
                'target' => [
                    'namespace' => 'android_app',
                    'package_name' => $config['package_name'],
                    'sha256_cert_fingerprints' => $config['sha256_cert_fingerprints'],
                ],
            ],
        ];
    }
}