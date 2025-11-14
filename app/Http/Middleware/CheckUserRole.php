<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Cek apakah user sudah login
        if (!Auth::check()) {
            return redirect('/login')->with('message', 'Login dulu bro');
        }

        // Cek apakah role user adalah user
        if (Auth::user()->role !== 'user') {
            abort(403, 'Akses ditolak. Hanya user yang dapat mengakses halaman ini.');
        }

        return $next($request);
    }
}