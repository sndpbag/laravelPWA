<?php

namespace Sndpbag\Sndppwa\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\View;
use Sndpbag\Sndppwa\PwaManager;

class PwaController extends Controller
{
    protected $pwa;

    public function __construct(PwaManager $pwa)
    {
        $this->pwa = $pwa;
    }

    /**
     * Serve manifest.json
     */
    public function manifest(): JsonResponse
    {
        $manifest = $this->pwa->generateManifest();
        
        return response()->json($manifest)
            ->header('Content-Type', 'application/manifest+json')
            ->header('Cache-Control', 'public, max-age=3600');
    }

    /**
     * Serve service worker
     */
    public function serviceWorker(): Response
    {
        $swPath = public_path('sw.js');
        
        if (!file_exists($swPath)) {
            abort(404, 'Service Worker not found');
        }

        $content = file_get_contents($swPath);
        
        // Replace cache version dynamically
        $content = str_replace(
            '{{CACHE_VERSION}}',
            $this->pwa->getCacheVersion(),
            $content
        );

        return response($content)
            ->header('Content-Type', 'application/javascript')
            ->header('Service-Worker-Allowed', '/')
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate');
    }

    /**
     * Show offline page
     */
    public function offline()
    {
        return view('pwa::offline');
    }

    /**
     * Asset links for TWA
     */
    public function assetLinks(): JsonResponse
    {
        if (!config('pwa.twa.enabled')) {
            abort(404);
        }

        $assetLinks = $this->pwa->generateAssetLinks();
        
        return response()->json($assetLinks)
            ->header('Content-Type', 'application/json')
            ->header('Cache-Control', 'public, max-age=86400');
    }
}