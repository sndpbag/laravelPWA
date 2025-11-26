<?php

namespace Sndpbag\Sndppwa\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallCommand extends Command
{
    protected $signature = 'pwa:install';
    protected $description = 'Install SNDP PWA package and publish all necessary files';

    public function handle()
    {
        $this->info('🚀 Installing SNDP PWA Package...');
        $this->newLine();

        // Publish config
        $this->call('vendor:publish', [
            '--tag' => 'pwa-config',
            '--force' => true,
        ]);
        $this->info('✅ Configuration published');

        // Publish views
        $this->call('vendor:publish', [
            '--tag' => 'pwa-views',
            '--force' => true,
        ]);
        $this->info('✅ Views published');

        // Publish assets
        $this->call('vendor:publish', [
            '--tag' => 'pwa-assets',
            '--force' => true,
        ]);
        $this->info('✅ Assets published');

        // Publish service worker
        $this->call('vendor:publish', [
            '--tag' => 'pwa-sw',
            '--force' => true,
        ]);
        $this->info('✅ Service Worker published');

        // Create necessary directories
        $this->createDirectories();

        // Create .well-known directory for TWA
        $wellKnownPath = public_path('.well-known');
        if (!File::exists($wellKnownPath)) {
            File::makeDirectory($wellKnownPath, 0755, true);
            $this->info('✅ Created .well-known directory');
        }

        $this->newLine();
        $this->info('🎉 SNDP PWA Package installed successfully!');
        $this->newLine();
        
        $this->comment('Next steps:');
        $this->line('1. Add PWA meta tags to your layout: @include(\'pwa::meta\')');
        $this->line('2. Configure config/pwa.php as needed');
        $this->line('3. Generate icons: php artisan pwa:generate-icons your-logo.png');
        $this->line('4. Check PWA health: php artisan pwa:audit');
        $this->newLine();
    }

    protected function createDirectories()
    {
        $directories = [
            public_path('pwa/icons'),
            public_path('pwa/splash'),
            public_path('pwa/images'),
            public_path('pwa/js'),
        ];

        foreach ($directories as $dir) {
            if (!File::exists($dir)) {
                File::makeDirectory($dir, 0755, true);
            }
        }

        $this->info('✅ Created PWA directories');
    }
}