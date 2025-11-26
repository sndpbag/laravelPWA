<?php

namespace Sndpbag\Sndppwa\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Sndpbag\Sndppwa\PwaManager;

class AuditCommand extends Command
{
    protected $signature = 'pwa:audit';
    protected $description = 'Audit PWA setup and check for issues';

    protected $pwa;
    protected $issues = [];
    protected $warnings = [];
    protected $passed = [];

    public function __construct(PwaManager $pwa)
    {
        parent::__construct();
        $this->pwa = $pwa;
    }

    public function handle()
    {
        $this->info('🔍 Auditing PWA Configuration...');
        $this->newLine();

        // Run all checks
        $this->checkHttps();
        $this->checkManifest();
        $this->checkServiceWorker();
        $this->checkIcons();
        $this->checkOfflinePage();
        $this->checkCacheStrategy();
        $this->checkMetaTags();
        $this->checkSecurityHeaders();

        // Display results
        $this->displayResults();

        return count($this->issues) > 0 ? 1 : 0;
    }

    protected function checkHttps()
    {
        if (request()->secure() || app()->environment('local')) {
            $this->passed[] = 'HTTPS enabled or running locally';
        } else {
            $this->issues[] = 'HTTPS is required for PWA in production';
        }
    }

    protected function checkManifest()
    {
        $manifestPath = public_path('manifest.json');
        
        if (File::exists($manifestPath)) {
            $this->passed[] = 'manifest.json exists';
            
            $content = json_decode(File::get($manifestPath), true);
            
            // Check required fields
            $requiredFields = ['name', 'short_name', 'start_url', 'display', 'icons'];
            foreach ($requiredFields as $field) {
                if (!isset($content[$field])) {
                    $this->issues[] = "manifest.json missing required field: {$field}";
                }
            }
        } else {
            $this->issues[] = 'manifest.json not found in public directory';
        }
    }

    protected function checkServiceWorker()
    {
        $swPath = public_path('sw.js');
        
        if (File::exists($swPath)) {
            $this->passed[] = 'Service Worker file exists';
            
            $content = File::get($swPath);
            
            // Check for essential features
            if (strpos($content, 'install') !== false) {
                $this->passed[] = 'Service Worker has install event';
            } else {
                $this->warnings[] = 'Service Worker missing install event';
            }
            
            if (strpos($content, 'fetch') !== false) {
                $this->passed[] = 'Service Worker has fetch event';
            } else {
                $this->warnings[] = 'Service Worker missing fetch event';
            }
        } else {
            $this->issues[] = 'Service Worker (sw.js) not found in public directory';
        }
    }

    protected function checkIcons()
    {
        $config = config('pwa.icons', []);
        $missingIcons = [];
        
        foreach ($config as $size => $path) {
            $fullPath = public_path(ltrim($path, '/'));
            if (!File::exists($fullPath)) {
                $missingIcons[] = "{$size} ({$path})";
            }
        }
        
        if (empty($missingIcons)) {
            $this->passed[] = 'All configured icons exist';
        } else {
            $this->warnings[] = 'Missing icons: ' . implode(', ', $missingIcons);
        }
        
        // Check for minimum required sizes
        $requiredSizes = ['192x192', '512x512'];
        foreach ($requiredSizes as $size) {
            if (!isset($config[$size])) {
                $this->issues[] = "Missing required icon size: {$size}";
            }
        }
    }

    protected function checkOfflinePage()
    {
        $offlinePath = config('pwa.offline.fallback_page', '/offline');
        
        try {
            $response = $this->laravel->make('router')->dispatch(
                \Illuminate\Http\Request::create($offlinePath)
            );
            
            if ($response->getStatusCode() === 200) {
                $this->passed[] = 'Offline fallback page accessible';
            } else {
                $this->warnings[] = 'Offline page returned status: ' . $response->getStatusCode();
            }
        } catch (\Exception $e) {
            $this->warnings[] = 'Offline page not accessible: ' . $offlinePath;
        }
    }

    protected function checkCacheStrategy()
    {
        $strategy = config('pwa.serviceworker.cache_strategy');
        
        $validStrategies = ['CacheFirst', 'NetworkFirst', 'StaleWhileRevalidate', 'NetworkOnly', 'CacheOnly'];
        
        if (in_array($strategy, $validStrategies)) {
            $this->passed[] = "Valid cache strategy: {$strategy}";
        } else {
            $this->warnings[] = "Unknown cache strategy: {$strategy}";
        }
    }

    protected function checkMetaTags()
    {
        $viewPath = resource_path('views/vendor/pwa/meta.blade.php');
        
        if (File::exists($viewPath)) {
            $this->passed[] = 'PWA meta tags view exists';
        } else {
            $this->warnings[] = 'PWA meta tags view not found. Run: php artisan vendor:publish --tag=pwa-views';
        }
    }

    protected function checkSecurityHeaders()
    {
        if (config('pwa.csp.enabled')) {
            $this->passed[] = 'Content Security Policy enabled';
        } else {
            $this->warnings[] = 'Content Security Policy disabled';
        }
    }

    protected function displayResults()
    {
        $this->newLine();
        
        // Passed checks
        if (!empty($this->passed)) {
            $this->info('✅ Passed Checks:');
            foreach ($this->passed as $item) {
                $this->line('  • ' . $item);
            }
            $this->newLine();
        }
        
        // Warnings
        if (!empty($this->warnings)) {
            $this->warn('⚠️  Warnings:');
            foreach ($this->warnings as $item) {
                $this->line('  • ' . $item);
            }
            $this->newLine();
        }
        
        // Issues
        if (!empty($this->issues)) {
            $this->error('❌ Issues:');
            foreach ($this->issues as $item) {
                $this->line('  • ' . $item);
            }
            $this->newLine();
        }
        
        // Summary
        $total = count($this->passed) + count($this->warnings) + count($this->issues);
        $score = $total > 0 ? round((count($this->passed) / $total) * 100) : 0;
        
        $this->newLine();
        $this->info("📊 PWA Health Score: {$score}%");
        
        if ($score >= 90) {
            $this->info('🎉 Excellent! Your PWA is well configured.');
        } elseif ($score >= 70) {
            $this->comment('👍 Good! Consider addressing warnings.');
        } else {
            $this->warn('⚠️  Needs improvement. Please fix the issues above.');
        }
    }
}