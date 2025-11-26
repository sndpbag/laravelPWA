<?php

namespace Sndpbag\Sndppwa\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class OfflineMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Add offline-related headers
        if (config('pwa.offline.enabled')) {
            $response->headers->set('X-PWA-Offline', 'enabled');
        }

        return $response;
    }
}