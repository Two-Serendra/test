<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AllowMiniAppWebView
{
    public function handle(Request $request, Closure $next)
    {
        Log::info('===== MINIAPP.WEBVIEW =====');

        Log::info([
            'url' => $request->fullUrl(),
            'auth' => auth()->check(),
            'user' => auth()->user()?->email,
            'headers' => $request->headers->all(),
        ]);

        $response = $next($request);

        Log::info('===== RESPONSE =====');

        Log::info([
            'status' => $response->getStatusCode(),
        ]);

        $trustedOrigin = config(
            'app.miniapp_origin',
            'https://dev.serendra.ity.ph'
        );

        $response->headers->set(
            'Content-Security-Policy',
            "frame-ancestors 'self' {$trustedOrigin}"
        );

        $response->headers->remove('X-Frame-Options');

        $response->headers->set(
            'Cache-Control',
            'no-store, no-cache, must-revalidate, max-age=0'
        );

        return $response;
    }

}