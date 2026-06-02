<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MiniappTrust
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
      
        if ($request->header('X-SHELL') !== 'twoserendra-shell') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if ($request->has('user_id')) {
            auth()->loginUsingId($request->user_id);
        }

        return $next($request);
    }
}
