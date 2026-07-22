<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use App\Models\User;

class AllowMiniAppWebView
{
    public function handle(Request $request, Closure $next)
    {


        Log::info('===== MINIAPP.WEBVIEW =====');

        Log::info([
            'url' => $request->fullUrl(),
            'auth_before' => auth()->check(),
            'user_before' => auth()->user()?->email,
            'headers' => $request->headers->all(),
        ]);

        $proxySecret = '6657eac22b10df48a1511240d2ac24c45b738638d22b54a50ed610a1b5df9995';
        $forwardedHost = $request->header('x-forwarded-host');

        if (
            $proxySecret &&
            $forwardedHost &&
            hash_equals($proxySecret, (string) $request->header('x-miniapp-proxy-secret'))
        ) {
            // Trust the proxy's X-Forwarded-Proto rather than assuming https
            // — hardcoding https broke every asset()/route() link with
            // ERR_SSL_PROTOCOL_ERROR when the proxy itself was being hit over
            // plain http (local dev on localhost:3000 has no TLS). Only "http"
            // downgrades; anything else (missing header, "https", garbage)
            // stays https, since that's the correct/only scheme in production.
            $forwardedProto = $request->header('x-forwarded-proto') === 'http' ? 'http' : 'https';

            URL::forceRootUrl($forwardedProto . '://' . $forwardedHost);
            URL::forceScheme($forwardedProto);

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