<?php

namespace Sndpbag\Sndppwa\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array generateManifest()
 * @method static string getMetaTags()
 * @method static bool isInstallable()
 * @method static string getCacheVersion()
 * @method static array generateAssetLinks()
 * 
 * @see \Sndpbag\Sndppwa\PwaManager
 */
class Pwa extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'pwa';
    }
}