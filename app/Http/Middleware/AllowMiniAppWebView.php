<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AllowMiniAppWebView
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // ✅ Allow iframe / WebView embedding
        $response->headers->set('X-Frame-Options', 'ALLOWALL');

        // ✅ Safer CSP for mini-app embedding
        $response->headers->set(
            'Content-Security-Policy',
            "frame-ancestors *"
        );

        // Optional: prevent caching issues in mobile shells
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');

        return $response;
    }
}