<?php

namespace Sndpbag\Sndppwa\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ContentSecurityPolicy
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (!config('pwa.csp.enabled')) {
            return $response;
        }

        $directives = config('pwa.csp.directives', []);
        $cspHeader = $this->buildCspHeader($directives);

        $headerName = config('pwa.csp.report_only', false) 
            ? 'Content-Security-Policy-Report-Only' 
            : 'Content-Security-Policy';

        $response->headers->set($headerName, $cspHeader);

        // Add additional security headers
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');

        return $response;
    }

    /**
     * Build CSP header string
     */
    protected function buildCspHeader(array $directives): string
    {
        $csp = [];

        foreach ($directives as $directive => $sources) {
            if (is_array($sources)) {
                $csp[] = $directive . ' ' . implode(' ', $sources);
            } else {
                $csp[] = $directive . ' ' . $sources;
            }
        }

        return implode('; ', $csp);
    }
}