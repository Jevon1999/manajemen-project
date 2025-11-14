<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Project;
use App\Models\ProjectMember;
use Illuminate\Support\Facades\Auth;

class ProjectAccessMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission = 'view'): Response
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        // Get project ID from route parameter
        $projectId = $request->route('project') ?? $request->route('id') ?? $request->input('project_id');
        
        if (!$projectId) {
            abort(404, 'Project not found');
        }

        // System Admin has full access to everything
        if ($user->role === 'admin') {
            return $next($request);
        }

        $project = Project::find($projectId);
        if (!$project) {
            abort(404, 'Project not found');
        }

        // Check if user has access to this project
        $hasAccess = $this->checkProjectAccess($user, $project, $permission);
        
        if (!$hasAccess) {
            abort(403, 'You do not have permission to access this project');
        }

        // Add project to request for easy access in controllers
        $request->merge(['_project' => $project]);

        return $next($request);
    }

    /**
     * Check if user has specific permission for the project
     */
    private function checkProjectAccess($user, $project, $permission): bool
    {
        // Check if user is a member of this project
        $membership = ProjectMember::where('project_id', $project->project_id)
            ->where('user_id', $user->user_id)
            ->first();

        if (!$membership) {
            return false;
        }

        // Permission check based on role and membership
        switch ($permission) {
            case 'view':
                // All project members can view
                return true;

            case 'manage_members':
                // Only project managers (leaders assigned to project) can manage members
                return $membership->role === 'project_manager';

            case 'edit_project':
                // Only project managers can edit project details (but not change leader)
                return $membership->role === 'project_manager';

            case 'manage_tasks':
                // Project managers and developers can manage tasks
                return in_array($membership->role, ['project_manager', 'developer', 'designer']);

            case 'delete_project':
                // Only system admin can delete projects (handled above)
                return false;

            case 'change_leader':
                // Only system admin can change project leader (handled above)
                return false;

            default:
                return false;
        }
    }
}