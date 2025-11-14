<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSessionIsValid
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Regenerate CSRF token if session is invalid or expired
        if (!$request->session()->has('_token')) {
            $request->session()->regenerateToken();
        }

        // Ensure session is properly started
        if (!$request->session()->isStarted()) {
            $request->session()->start();
        }

        return $next($request);
    }
}