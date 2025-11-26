<?php

namespace Sndpbag\Sndppwa;

use Illuminate\Support\ServiceProvider;
use Sndpbag\Sndppwa\Console\Commands\AuditCommand;
use Sndpbag\Sndppwa\Console\Commands\GenerateIconsCommand;
use Sndpbag\Sndppwa\Console\Commands\InstallCommand;
use Sndpbag\Sndppwa\Console\Commands\PublishCommand;
use Sndpbag\Sndppwa\Console\Commands\SetupTwaCommand;

class SndppwaServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/pwa.php', 'pwa'
        );

        $this->app->singleton('pwa', function ($app) {
            return new PwaManager($app);
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load routes
        $this->loadRoutesFrom(__DIR__.'/../routes/pwa.php');

        // Load views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'pwa');

        // Publish config
        $this->publishes([
            __DIR__.'/../config/pwa.php' => config_path('pwa.php'),
        ], 'pwa-config');

        // Publish views
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/pwa'),
        ], 'pwa-views');

        // Publish assets
        $this->publishes([
            __DIR__.'/../resources/js' => public_path('pwa/js'),
            __DIR__.'/../resources/images' => public_path('pwa/images'),
        ], 'pwa-assets');

        // Publish service worker
        $this->publishes([
            __DIR__.'/../stubs/service-worker.js' => public_path('sw.js'),
        ], 'pwa-sw');

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
                GenerateIconsCommand::class,
                AuditCommand::class,
                PublishCommand::class,
                SetupTwaCommand::class,
            ]);
        }

        // Register middleware
        $router = $this->app['router'];
        $router->aliasMiddleware('pwa.csp', \Sndpbag\Sndppwa\Http\Middleware\ContentSecurityPolicy::class);
        $router->aliasMiddleware('pwa.offline', \Sndpbag\Sndppwa\Http\Middleware\OfflineMiddleware::class);
    }
}