<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleSessionExpiredRequests
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if this is a POST request with potentially expired session
        if ($request->isMethod('POST') && !$request->session()->has('_token')) {
            // If it's an AJAX request, return JSON error
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Your session has expired. Please refresh the page and try again.',
                    'expired' => true
                ], 419);
            }
            
            // For regular requests, redirect back with error
            return back()->withErrors([
                'session' => 'Your session has expired. Please try again.'
            ])->with('warning', 'Session expired. Please try again.');
        }

        return $next($request);
    }
}