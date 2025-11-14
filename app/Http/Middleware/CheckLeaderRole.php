<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckLeaderRole
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        if (!in_array(Auth::user()->role, ['admin', 'leader'])) {
            abort(403, 'Akses ditolak. Hanya admin dan leader yang dapat mengakses halaman ini.');
        }

        return $next($request);
    }
}
