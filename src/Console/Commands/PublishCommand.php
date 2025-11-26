<?php

namespace Sndpbag\Sndppwa\Console\Commands;

use Illuminate\Console\Command;

class PublishCommand extends Command
{
    protected $signature = 'pwa:publish {--force : Overwrite existing files}';
    protected $description = 'Publish PWA assets, views and config';

    public function handle()
    {
        $force = $this->option('force');

        $this->info('📦 Publishing PWA files...');
        $this->newLine();

        $tags = ['pwa-config', 'pwa-views', 'pwa-assets', 'pwa-sw'];

        foreach ($tags as $tag) {
            $this->call('vendor:publish', [
                '--tag' => $tag,
                '--force' => $force,
            ]);
        }

        $this->newLine();
        $this->info('✅ All PWA files published successfully!');
    }
}