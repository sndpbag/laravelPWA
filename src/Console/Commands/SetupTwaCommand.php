<?php

namespace Sndpbag\Sndppwa\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SetupTwaCommand extends Command
{
    protected $signature = 'pwa:setup-twa';
    protected $description = 'Setup Trusted Web Activity (TWA) for Play Store';

    public function handle()
    {
        $this->info('🔧 Setting up Trusted Web Activity (TWA)...');
        $this->newLine();

        // Get package name
        $packageName = $this->ask('Enter your Android package name (e.g., com.example.app)', config('pwa.twa.package_name'));
        
        // Get certificate fingerprints
        $this->info('Generate SHA-256 fingerprint using:');
        $this->line('keytool -list -v -keystore your-keystore.jks');
        $this->newLine();
        
        $fingerprint = $this->ask('Enter SHA-256 certificate fingerprint');

        // Update config
        $configPath = config_path('pwa.php');
        if (File::exists($configPath)) {
            $config = File::get($configPath);
            
            $config = preg_replace(
                "/'package_name' => '.*?'/",
                "'package_name' => '{$packageName}'",
                $config
            );
            
            $config = preg_replace(
                "/'sha256_cert_fingerprints' => \[\]/",
                "'sha256_cert_fingerprints' => ['{$fingerprint}']",
                $config
            );
            
            $config = preg_replace(
                "/'enabled' => false,/",
                "'enabled' => true,",
                $config,
                1
            );
            
            File::put($configPath, $config);
            
            $this->info('✅ Config updated');
        }

        // Generate assetlinks.json
        $this->generateAssetLinks($packageName, $fingerprint);

        $this->newLine();
        $this->info('✅ TWA setup completed!');
        $this->newLine();
        
        $this->comment('Next steps:');
        $this->line('1. Verify assetlinks.json at: https://yourdomain.com/.well-known/assetlinks.json');
        $this->line('2. Test with: https://developers.google.com/digital-asset-links/tools/generator');
        $this->line('3. Build your Android app with TWA support');
        $this->line('4. Submit to Play Store');
    }

    protected function generateAssetLinks($packageName, $fingerprint)
    {
        $wellKnownPath = public_path('.well-known');
        
        if (!File::exists($wellKnownPath)) {
            File::makeDirectory($wellKnownPath, 0755, true);
        }

        $assetLinks = [
            [
                'relation' => ['delegate_permission/common.handle_all_urls'],
                'target' => [
                    'namespace' => 'android_app',
                    'package_name' => $packageName,
                    'sha256_cert_fingerprints' => [$fingerprint],
                ],
            ],
        ];

        File::put(
            $wellKnownPath . '/assetlinks.json',
            json_encode($assetLinks, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );

        $this->info('✅ Generated assetlinks.json');
    }
}