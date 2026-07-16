<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        //
    ];


    protected function tokensMatch($request)
    {
        \Log::info('CSRF DEBUG', [
            'session_id' => session()->getId(),
            'session_token' => $request->session()->token(),
            'request_token' => $this->getTokenFromRequest($request),
            'cookies' => $request->cookies->all(),
            'cookie_header' => $request->header('Cookie'),
            'origin' => $request->header('Origin'),
            'referer' => $request->header('Referer'),
        ]);

        return parent::tokensMatch($request);
    }
}
