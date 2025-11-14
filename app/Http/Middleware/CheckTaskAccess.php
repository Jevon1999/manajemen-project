<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Card;

class CheckTaskAccess
{
    /**
     * Handle an incoming request to ensure user has access to the task
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        $taskId = $request->route('task');
        
        if (!$taskId) {
            return $next($request);
        }

        $task = Card::find($taskId);
        
        if (!$task) {
            abort(404, 'Task not found');
        }

        // Check if user has access to this task
        $hasAccess = false;
        
        // Admin has access to all tasks
        if ($user->role === 'admin') {
            $hasAccess = true;
        }
        // Check if user is assigned to the task
        elseif ($task->assignments()->where('user_id', $user->user_id)->exists()) {
            $hasAccess = true;
        }
        // Check if user is project manager of the task's project
        elseif (DB::table('project_members')
                ->where('user_id', $user->user_id)
                ->where('project_id', $task->board->project_id)
                ->where('role', 'project_manager')
                ->exists()) {
            $hasAccess = true;
        }
        // Check if user is team leader and the project has this user as leader
        elseif ($user->role === 'leader' && 
                DB::table('project_members')
                ->where('user_id', $user->user_id)
                ->where('project_id', $task->board->project_id)
                ->exists()) {
            $hasAccess = true;
        }

        if (!$hasAccess) {
            abort(403, 'You do not have permission to access this task');
        }

        return $next($request);
    }
}