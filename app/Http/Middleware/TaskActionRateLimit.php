<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiter;
use Illuminate\Support\Facades\Auth;

class TaskActionRateLimit
{
    protected $limiter;

    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    /**
     * Handle an incoming request to prevent task action abuse
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        $key = 'task_actions:' . $user->user_id;
        
        // Allow 30 task actions per minute per user
        if ($this->limiter->tooManyAttempts($key, 30)) {
            $seconds = $this->limiter->availableIn($key);
            
            return response()->json([
                'message' => 'Too many task actions. Please try again in ' . $seconds . ' seconds.',
                'retry_after' => $seconds
            ], 429);
        }

        $this->limiter->hit($key, 60); // 60 seconds window

        return $next($request);
    }
}