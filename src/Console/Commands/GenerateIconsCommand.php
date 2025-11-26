<?php

namespace Sndpbag\Sndppwa\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;

class GenerateIconsCommand extends Command
{
    protected $signature = 'pwa:generate-icons {source : Path to source image (min 512x512)}';
    protected $description = 'Generate all PWA icons from a source image';

    protected $sizes = [
        'icons' => [72, 96, 128, 144, 152, 192, 384, 512],
        'maskable' => [192, 512],
        'splash' => [
            '640x1136',
            '750x1334',
            '828x1792',
            '1125x2436',
            '1242x2208',
            '1242x2688',
            '1536x2048',
            '1668x2224',
            '1668x2388',
            '2048x2732',
        ],
    ];

    public function handle()
    {
        $sourcePath = $this->argument('source');

        if (!File::exists($sourcePath)) {
            $this->error('Source image not found: ' . $sourcePath);
            return 1;
        }

        $this->info('🎨 Generating PWA icons from: ' . $sourcePath);
        $this->newLine();

        try {
            // Generate regular icons
            $this->generateRegularIcons($sourcePath);
            
            // Generate maskable icons
            $this->generateMaskableIcons($sourcePath);
            
            // Generate splash screens
            $this->generateSplashScreens($sourcePath);
            
            // Generate favicon
            $this->generateFavicon($sourcePath);

            $this->newLine();
            $this->info('✅ All icons generated successfully!');
            $this->newLine();
            
            $this->comment('Generated files:');
            $this->line('• Regular icons: ' . count($this->sizes['icons']) . ' sizes');
            $this->line('• Maskable icons: ' . count($this->sizes['maskable']) . ' sizes');
            $this->line('• Splash screens: ' . count($this->sizes['splash']) . ' sizes');
            $this->line('• Favicon: 16x16, 32x32, apple-touch-icon');
            
            return 0;
        } catch (\Exception $e) {
            $this->error('Error generating icons: ' . $e->getMessage());
            return 1;
        }
    }

    protected function generateRegularIcons($sourcePath)
    {
        $iconPath = public_path('pwa/icons');
        
        if (!File::exists($iconPath)) {
            File::makeDirectory($iconPath, 0755, true);
        }

        $bar = $this->output->createProgressBar(count($this->sizes['icons']));
        $bar->setFormat('Regular Icons: %current%/%max% [%bar%] %percent:3s%%');

        foreach ($this->sizes['icons'] as $size) {
            $img = Image::make($sourcePath);
            $img->resize($size, $size, function ($constraint) {
                $constraint->aspectRatio();
            });
            
            $img->save($iconPath . "/icon-{$size}x{$size}.png");
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
    }

    protected function generateMaskableIcons($sourcePath)
    {
        $iconPath = public_path('pwa/icons');

        $bar = $this->output->createProgressBar(count($this->sizes['maskable']));
        $bar->setFormat('Maskable Icons: %current%/%max% [%bar%] %percent:3s%%');

        foreach ($this->sizes['maskable'] as $size) {
            // Create canvas with safe zone (80% of icon)
            $canvas = Image::canvas($size, $size, config('pwa.background_color', '#ffffff'));
            
            $img = Image::make($sourcePath);
            $iconSize = (int)($size * 0.8);
            $img->resize($iconSize, $iconSize);
            
            // Center the icon
            $offset = (int)(($size - $iconSize) / 2);
            $canvas->insert($img, 'top-left', $offset, $offset);
            
            $canvas->save($iconPath . "/maskable-icon-{$size}x{$size}.png");
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
    }

    protected function generateSplashScreens($sourcePath)
    {
        $splashPath = public_path('pwa/splash');
        
        if (!File::exists($splashPath)) {
            File::makeDirectory($splashPath, 0755, true);
        }

        $bar = $this->output->createProgressBar(count($this->sizes['splash']));
        $bar->setFormat('Splash Screens: %current%/%max% [%bar%] %percent:3s%%');

        $backgroundColor = config('pwa.background_color', '#ffffff');

        foreach ($this->sizes['splash'] as $size) {
            list($width, $height) = explode('x', $size);
            
            $canvas = Image::canvas($width, $height, $backgroundColor);
            
            $img = Image::make($sourcePath);
            
            // Resize to fit within canvas (80% of smallest dimension)
            $maxSize = (int)(min($width, $height) * 0.4);
            $img->resize($maxSize, $maxSize, function ($constraint) {
                $constraint->aspectRatio();
            });
            
            // Center the icon
            $canvas->insert($img, 'center');
            
            $canvas->save($splashPath . "/splash-{$size}.png");
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
    }

    protected function generateFavicon($sourcePath)
    {
        $publicPath = public_path();
        
        $this->info('Generating Favicons...');
        
        // 16x16 favicon
        $img = Image::make($sourcePath);
        $img->resize(16, 16);
        $img->save($publicPath . '/favicon-16x16.png');
        
        // 32x32 favicon
        $img = Image::make($sourcePath);
        $img->resize(32, 32);
        $img->save($publicPath . '/favicon-32x32.png');
        
        // Apple touch icon
        $img = Image::make($sourcePath);
        $img->resize(180, 180);
        $img->save($publicPath . '/apple-touch-icon.png');
    }
}