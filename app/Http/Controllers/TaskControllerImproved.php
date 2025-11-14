<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\CardAssignment;
use App\Models\Project;
use App\Models\Board;
use App\Models\User;
use App\Http\Requests\TaskProgressRequest;
use App\Http\Requests\TaskStatusRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TaskController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // Apply these middlewares to specific methods
        // $this->middleware('task.access')->only(['show', 'updateStatus', 'addProgress']);
        // $this->middleware('task.rate-limit')->only(['updateStatus', 'addProgress']);
    }

    /**
     * Display user's tasks based on their role with optimized queries
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $filter = $request->get('filter', 'all');
            $role = $request->get('role_filter');
            
            // Cache key for user's project roles
            $cacheKey = "user_project_roles_{$user->user_id}";
            $userProjectRoles = Cache::remember($cacheKey, 300, function () use ($user) {
                return DB::table('project_members')
                    ->where('user_id', $user->user_id)
                    ->pluck('role')
                    ->unique()
                    ->toArray();
            });
            
            // Optimized query with proper joins
            $query = Card::whereHas('assignments', function($q) use ($user) {
                $q->where('user_id', $user->user_id);
            })->with([
                'board:board_id,name,project_id',
                'board.project:project_id,name',
                'assignments:card_id,user_id',
                'assignments.user:user_id,name,email'
            ])->withCount([
                'subtasks',
                'subtasks as completed_subtasks_count' => function($query) {
                    $query->where('is_completed', true);
                }
            ]);

            // Apply filters
            $this->applyTaskFilters($query, $filter, $role, $user, $userProjectRoles);

            $tasks = $query->orderByRaw('FIELD(priority, "high", "medium", "low")')
                          ->orderBy('due_date')
                          ->paginate(20);

            // Get task statistics
            $stats = $this->getTaskStatistics($user, $userProjectRoles, $role);

            return view('tasks.index', compact('tasks', 'stats', 'filter', 'role', 'userProjectRoles'));
            
        } catch (\Exception $e) {
            Log::error('Error loading tasks: ' . $e->getMessage());
            return back()->with('error', 'Failed to load tasks. Please try again.');
        }
    }

    /**
     * Apply task filters to query
     */
    private function applyTaskFilters($query, $filter, $role, $user, $userProjectRoles)
    {
        // Apply status filter
        switch ($filter) {
            case 'todo':
                $query->where('status', 'todo');
                break;
            case 'in_progress':
                $query->where('status', 'in_progress');
                break;
            case 'review':
                $query->where('status', 'review');
                break;
            case 'done':
                $query->where('status', 'done');
                break;
            case 'overdue':
                $query->where('due_date', '<', now())
                      ->where('status', '!=', 'done');
                break;
        }

        // Apply role-specific filter if user has multiple roles
        if ($role && in_array($role, $userProjectRoles)) {
            $projectIds = DB::table('project_members')
                ->where('user_id', $user->user_id)
                ->where('role', $role)
                ->pluck('project_id');
                
            $query->whereHas('board', function($q) use ($projectIds) {
                $q->whereIn('project_id', $projectIds);
            });
        }
    }

    /**
     * Get task statistics with caching
     */
    private function getTaskStatistics($user, $userProjectRoles, $role)
    {
        $cacheKey = "task_stats_{$user->user_id}_{$role}";
        
        return Cache::remember($cacheKey, 120, function () use ($user, $userProjectRoles, $role) {
            $baseQuery = Card::whereHas('assignments', function($q) use ($user) {
                $q->where('user_id', $user->user_id);
            });

            // Apply role filter if specified
            if ($role && in_array($role, $userProjectRoles)) {
                $projectIds = DB::table('project_members')
                    ->where('user_id', $user->user_id)
                    ->where('role', $role)
                    ->pluck('project_id');
                    
                $baseQuery->whereHas('board', function($q) use ($projectIds) {
                    $q->whereIn('project_id', $projectIds);
                });
            }

            return [
                'total' => (clone $baseQuery)->count(),
                'todo' => (clone $baseQuery)->where('status', 'todo')->count(),
                'in_progress' => (clone $baseQuery)->where('status', 'in_progress')->count(),
                'review' => (clone $baseQuery)->where('status', 'review')->count(),
                'done' => (clone $baseQuery)->where('status', 'done')->count(),
                'overdue' => (clone $baseQuery)->where('due_date', '<', now())
                    ->where('status', '!=', 'done')->count(),
            ];
        });
    }

    /**
     * Show task details with enhanced security
     */
    public function show($id)
    {
        try {
            $user = Auth::user();
            
            $task = Card::with([
                'board:board_id,name,project_id',
                'board.project:project_id,name',
                'assignments:card_id,user_id,assigned_at',
                'assignments.user:user_id,name,email',
                'subtasks:subtask_id,card_id,title,description,is_completed',
                'comments:comment_id,card_id,user_id,comment_text,commented_at',
                'comments.user:user_id,name'
            ])->findOrFail($id);

            // Check access permissions
            $this->authorizeTaskAccess($user, $task);

            // Check user's role in the project
            $projectRole = DB::table('project_members')
                ->where('user_id', $user->user_id)
                ->where('project_id', $task->board->project_id)
                ->value('role');

            $isAssigned = $task->assignments()->where('user_id', $user->user_id)->exists();
            
            // Determine permissions
            $permissions = $this->getTaskPermissions($user, $task, $projectRole, $isAssigned);

            return view('tasks.show', compact('task', 'isAssigned', 'projectRole') + $permissions);
            
        } catch (\Exception $e) {
            Log::error('Error loading task details: ' . $e->getMessage());
            return back()->with('error', 'Failed to load task details. Please try again.');
        }
    }

    /**
     * Update task status with validation and logging
     */
    public function updateStatus(TaskStatusRequest $request, $id)
    {
        try {
            $user = Auth::user();
            $task = Card::findOrFail($id);
            
            // Check permissions
            $this->authorizeTaskStatusChange($user, $task);

            $oldStatus = $task->status;
            $newStatus = $request->validated()['status'];

            // Validate status transition
            if (!$this->isValidStatusTransition($oldStatus, $newStatus)) {
                return back()->with('error', 'Invalid status transition.');
            }

            $task->update([
                'status' => $newStatus,
                'updated_at' => now()
            ]);

            // Log the status change
            Log::info("Task status updated", [
                'task_id' => $task->card_id,
                'user_id' => $user->user_id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus
            ]);

            // Clear cache
            $this->clearTaskCaches($user->user_id);

            return back()->with('success', 'Task status updated successfully!');
            
        } catch (\Exception $e) {
            Log::error('Error updating task status: ' . $e->getMessage());
            return back()->with('error', 'Failed to update task status. Please try again.');
        }
    }

    /**
     * Add progress update with enhanced validation
     */
    public function addProgress(TaskProgressRequest $request, $id)
    {
        try {
            $user = Auth::user();
            $task = Card::findOrFail($id);
            
            // Check permissions
            $this->authorizeTaskComment($user, $task);

            // Create comment with sanitized content
            $task->comments()->create([
                'user_id' => $user->user_id,
                'comment_text' => $request->validated()['comment'],
                'commented_at' => now()
            ]);

            // Log the progress update
            Log::info("Progress update added", [
                'task_id' => $task->card_id,
                'user_id' => $user->user_id
            ]);

            return back()->with('success', 'Progress update added successfully!');
            
        } catch (\Exception $e) {
            Log::error('Error adding progress update: ' . $e->getMessage());
            return back()->with('error', 'Failed to add progress update. Please try again.');
        }
    }

    /**
     * Role-specific task dashboard
     */
    public function roleDashboard($role)
    {
        $user = Auth::user();
        
        // Validate role access
        $userProjectRoles = DB::table('project_members')
            ->where('user_id', $user->user_id)
            ->pluck('role')
            ->toArray();
        
        if (!in_array($role, $userProjectRoles)) {
            abort(404, 'Role not found for this user.');
        }

        // Get data with caching
        $data = $this->getRoleDashboardData($user, $role);

        return view("tasks.{$role}-dashboard", $data);
    }

    // Helper methods for authorization and permissions

    private function authorizeTaskAccess($user, $task)
    {
        if ($user->role === 'admin') return;
        
        $isAssigned = $task->assignments()->where('user_id', $user->user_id)->exists();
        $isProjectMember = DB::table('project_members')
            ->where('user_id', $user->user_id)
            ->where('project_id', $task->board->project_id)
            ->exists();
        
        if (!$isAssigned && !$isProjectMember) {
            abort(403, 'You do not have permission to view this task.');
        }
    }

    private function authorizeTaskStatusChange($user, $task)
    {
        if ($user->role === 'admin') return;
        
        $isAssigned = $task->assignments()->where('user_id', $user->user_id)->exists();
        $isProjectManager = DB::table('project_members')
            ->where('user_id', $user->user_id)
            ->where('project_id', $task->board->project_id)
            ->where('role', 'project_manager')
            ->exists();
        
        if (!$isAssigned && !$isProjectManager) {
            abort(403, 'You do not have permission to update this task status.');
        }
    }

    private function authorizeTaskComment($user, $task)
    {
        if ($user->role === 'admin') return;
        
        $isAssigned = $task->assignments()->where('user_id', $user->user_id)->exists();
        $isProjectMember = DB::table('project_members')
            ->where('user_id', $user->user_id)
            ->where('project_id', $task->board->project_id)
            ->exists();
        
        if (!$isAssigned && !$isProjectMember) {
            abort(403, 'You do not have permission to comment on this task.');
        }
    }

    private function getTaskPermissions($user, $task, $projectRole, $isAssigned)
    {
        $canEdit = $isAssigned || $user->role === 'admin' || $user->role === 'leader' || $projectRole === 'project_manager';
        $canComment = $isAssigned || $canEdit;
        $canChangeStatus = $isAssigned || $projectRole === 'project_manager' || $user->role === 'admin';

        return compact('canEdit', 'canComment', 'canChangeStatus');
    }

    private function isValidStatusTransition($oldStatus, $newStatus)
    {
        $validTransitions = [
            'todo' => ['in_progress'],
            'in_progress' => ['review', 'todo'],
            'review' => ['done', 'in_progress'],
            'done' => ['review'] // Allow reopening if needed
        ];

        return isset($validTransitions[$oldStatus]) && 
               in_array($newStatus, $validTransitions[$oldStatus]);
    }

    private function clearTaskCaches($userId)
    {
        Cache::forget("user_project_roles_{$userId}");
        Cache::forget("task_stats_{$userId}_");
        // Clear role-specific caches
        $roles = ['developer', 'designer', 'project_manager'];
        foreach ($roles as $role) {
            Cache::forget("task_stats_{$userId}_{$role}");
        }
    }

    private function getRoleDashboardData($user, $role)
    {
        // Get projects where user has this role
        $projectIds = DB::table('project_members')
            ->where('user_id', $user->user_id)
            ->where('role', $role)
            ->pluck('project_id');

        // Get recent tasks
        $tasks = Card::whereHas('assignments', function($q) use ($user) {
            $q->where('user_id', $user->user_id);
        })->whereHas('board', function($q) use ($projectIds) {
            $q->whereIn('project_id', $projectIds);
        })->with(['board.project', 'assignments.user'])
          ->orderBy('due_date')
          ->limit(10)
          ->get();

        // Role-specific statistics
        $stats = [
            'active_projects' => $projectIds->count(),
            'active_tasks' => Card::whereHas('assignments', function($q) use ($user) {
                $q->where('user_id', $user->user_id);
            })->whereHas('board', function($q) use ($projectIds) {
                $q->whereIn('project_id', $projectIds);
            })->where('status', '!=', 'done')->count(),
            'completed_tasks' => Card::whereHas('assignments', function($q) use ($user) {
                $q->where('user_id', $user->user_id);
            })->whereHas('board', function($q) use ($projectIds) {
                $q->whereIn('project_id', $projectIds);
            })->where('status', 'done')->count(),
        ];

        return compact('tasks', 'stats', 'role', 'projectIds');
    }
}