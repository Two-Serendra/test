<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use App\Models\User;
use Illuminate\Support\Facades\URL;

class AllowMiniAppWebView
{
    public function handle(Request $request, Closure $next)
    {
        $proxySecret = config('app.miniapp_proxy_secret');
        $forwardedHost = $request->header('x-forwarded-host');

        if (
            $proxySecret &&
            $forwardedHost &&
            hash_equals($proxySecret, (string) $request->header('x-miniapp-proxy-secret'))
        ) {
            URL::forceRootUrl('https://' . $forwardedHost);
            URL::forceScheme('https');

            config(['session.domain' => null]);
        }

        Log::info('===== MINIAPP.WEBVIEW =====');

        Log::info([
            'url' => $request->fullUrl(),
            'auth_before' => auth()->check(),
            'user_before' => auth()->user()?->email,
            'headers' => $request->headers->all(),
        ]);

        $proxySecret = config('app.miniapp_proxy_secret');
        $forwardedHost = $request->header('x-forwarded-host');

        if (
            $proxySecret &&
            $forwardedHost &&
            hash_equals($proxySecret, (string) $request->header('x-miniapp-proxy-secret'))
        ) {
            URL::forceRootUrl('https://' . $forwardedHost);
            URL::forceScheme('https');
            
            config(['session.domain' => null]);
        }

        /**
         * ✅ AUTH FIRST (before request executes)
         */
        if (!auth()->check() && $request->header('x-miniapp-token')) {

            $user = User::where('api_token', $request->header('x-miniapp-token'))->first();

            if ($user) {
                Auth::login($user);
                $request->setUserResolver(fn() => $user);
            }
        }

        /**
         * Now request runs WITH correct auth context
         */
        $response = $next($request);

        Log::info('===== RESPONSE =====');

        Log::info([
            'status' => $response->getStatusCode(),
            'auth_after' => auth()->check(),
            'user_after' => auth()->user()?->email,
        ]);

        $trustedOrigin = config('app.miniapp_origin', 'https://serendra.dev.ity.ph');

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