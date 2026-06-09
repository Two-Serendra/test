<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AllowMiniAppWebView
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $trustedOrigin = config(
            'app.miniapp_origin',
            'https://dev.serendra.ity.ph'
        );

        // Allow embedding only from your app and your own site
        $response->headers->set(
            'Content-Security-Policy',
            "frame-ancestors 'self' {$trustedOrigin}"
        );

        // Remove invalid header
        $response->headers->remove('X-Frame-Options');

        $response->headers->set(
            'Cache-Control',
            'no-store, no-cache, must-revalidate, max-age=0'
        );

        return $response;
    }
}