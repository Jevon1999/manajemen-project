<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Services\TaskService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TaskController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('task.access')->only(['show', 'updateStatus', 'addProgress']);
        $this->middleware('task.rate-limit')->only(['updateStatus', 'addProgress']);
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
            
            // Optimized query with proper indexing
            $query = Card::select([
                    'cards.*',
                    'boards.name as board_name',
                    'projects.name as project_name'
                ])
                ->join('card_assignments', 'cards.card_id', '=', 'card_assignments.card_id')
                ->join('boards', 'cards.board_id', '=', 'boards.board_id')
                ->join('projects', 'boards.project_id', '=', 'projects.project_id')
                ->where('card_assignments.user_id', $user->user_id)
                ->with(['assignments:card_id,user_id', 'assignments.user:user_id,name,email'])
                ->withCount(['subtasks', 'subtasks as completed_subtasks_count' => function($query) {
                    $query->where('is_completed', true);
                }]);

            // Apply filters with proper indexing
            $this->applyTaskFilters($query, $filter, $role, $user, $userProjectRoles);

            $tasks = $query->orderByRaw('FIELD(priority, "high", "medium", "low")')
                          ->orderBy('due_date')
                          ->paginate(20);

            // Cache task statistics for better performance
            $stats = $this->getTaskStatistics($user, $userProjectRoles, $role);

            return view('tasks.index', compact('tasks', 'stats', 'filter', 'role', 'userProjectRoles'));
            
        } catch (\Exception $e) {
            Log::error('Error loading tasks: ' . $e->getMessage());
            return back()->with('error', 'Failed to load tasks. Please try again.');
        }


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
        $userProjectRoles = DB::table('project_members')
            ->where('user_id', $user->user_id)
            ->pluck('role')
            ->toArray();

        if ($role && in_array($role, $userProjectRoles)) {
            // Filter tasks by project role context
            $projectIds = DB::table('project_members')
                ->where('user_id', $user->user_id)
                ->where('role', $role)
                ->pluck('project_id');
                
            $query->whereHas('board', function($q) use ($projectIds) {
                $q->whereIn('project_id', $projectIds);
            });
        }

        $tasks = $query->orderBy('due_date')
                      ->orderBy('priority', 'desc')
                      ->paginate(20);

        // Task statistics
        $stats = [
            'total' => Card::whereHas('assignments', function($q) use ($user) {
                $q->where('user_id', $user->user_id);
            })->count(),
            'todo' => Card::whereHas('assignments', function($q) use ($user) {
                $q->where('user_id', $user->user_id);
            })->where('status', 'todo')->count(),
            'in_progress' => Card::whereHas('assignments', function($q) use ($user) {
                $q->where('user_id', $user->user_id);
            })->where('status', 'in_progress')->count(),
            'review' => Card::whereHas('assignments', function($q) use ($user) {
                $q->where('user_id', $user->user_id);
            })->where('status', 'review')->count(),
            'done' => Card::whereHas('assignments', function($q) use ($user) {
                $q->where('user_id', $user->user_id);
            })->where('status', 'done')->count(),
            'overdue' => Card::whereHas('assignments', function($q) use ($user) {
                $q->where('user_id', $user->user_id);
            })->where('due_date', '<', now())->where('status', '!=', 'done')->count(),
        ];
        
        return view('tasks.index', compact('tasks', 'stats', 'filter', 'role', 'userProjectRoles'));
    }

    /**
     * Show task details with role-specific actions
     */
    public function show($id)
    {
        $user = Auth::user();
        
        $task = Card::with([
            'board.project',
            'assignments.user',
            'subtasks',
            'comments.user'
        ])->findOrFail($id);

        // Check if user is assigned to this task
        $isAssigned = $task->assignments()->where('user_id', $user->user_id)->exists();
        
        // Check user's role in the project
        $projectRole = DB::table('project_members')
            ->where('user_id', $user->user_id)
            ->where('project_id', $task->board->project_id)
            ->value('role');

        // Determine available actions based on role and assignment
        $canEdit = $isAssigned || $user->role === 'admin' || $user->role === 'leader' || $projectRole === 'project_manager';
        $canComment = $isAssigned || $canEdit;
        $canChangeStatus = $isAssigned || $projectRole === 'project_manager';

        // Load project members for assignment when leader/admin
        $projectMembers = collect();
        if ($user->role === 'admin' || $projectRole === 'project_manager') {
            $projectMembers = ProjectMember::where('project_id', $task->board->project_id)
                ->with('user:user_id,full_name,username,email,role,status')
                ->orderBy('role')
                ->get();
        }

        return view('tasks.show', compact('task', 'isAssigned', 'projectRole', 'canEdit', 'canComment', 'canChangeStatus', 'projectMembers'));
    }

    /**
     * Update task status (for assigned users)
     */
    public function updateStatus(Request $request, $id)
    {
        $user = Auth::user();
        $task = Card::findOrFail($id);
        
        // Check if user can update status
        $isAssigned = $task->assignments()->where('user_id', $user->user_id)->exists();
        $projectRole = DB::table('project_members')
            ->where('user_id', $user->user_id)
            ->where('project_id', $task->board->project_id)
            ->value('role');
        
        if (!$isAssigned && $projectRole !== 'project_manager' && $user->role !== 'admin') {
            abort(403, 'You cannot update this task status.');
        }

        $request->validate([
            'status' => 'required|in:todo,in_progress,review,done'
        ]);

        $task->update([
            'status' => $request->status,
            'updated_at' => now()
        ]);

        return back()->with('success', 'Task status updated successfully!');
    }

    /**
     * Add progress update/comment
     */
    public function addProgress(Request $request, $id)
    {
        $user = Auth::user();
        $task = Card::findOrFail($id);
        
        // Check if user can add progress
        $isAssigned = $task->assignments()->where('user_id', $user->user_id)->exists();
        $projectRole = DB::table('project_members')
            ->where('user_id', $user->user_id)
            ->where('project_id', $task->board->project_id)
            ->value('role');
        
        if (!$isAssigned && $projectRole !== 'project_manager' && $user->role !== 'admin') {
            abort(403, 'You cannot add progress to this task.');
        }

        $request->validate([
            'comment' => 'required|string|max:1000'
        ]);

        // Add comment as progress update
        $task->comments()->create([
            'user_id' => $user->user_id,
            'comment_text' => $request->comment,
            'commented_at' => now()
        ]);

        return back()->with('success', 'Progress update added successfully!');
    }

    /**
     * Assign one or more project members to a task (Leader/Admin only)
     */
    public function assignMembers(Request $request, $id)
    {
        $user = Auth::user();
        $task = Card::with('board')->findOrFail($id);

        // Determine role within the project
        $projectRole = DB::table('project_members')
            ->where('user_id', $user->user_id)
            ->where('project_id', $task->board->project_id)
            ->value('role');

        if (!($user->role === 'admin' || $projectRole === 'project_manager')) {
            abort(403, 'You are not allowed to assign members to this task.');
        }

        $data = $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'integer|exists:users,user_id',
        ]);

        // Ensure assignees belong to the same project
        $projectMemberIds = ProjectMember::where('project_id', $task->board->project_id)
            ->pluck('user_id')
            ->toArray();

        $toAssign = array_values(array_intersect($data['user_ids'], $projectMemberIds));
        if (empty($toAssign)) {
            return back()->with('error', 'Selected users are not members of this project.');
        }

        foreach ($toAssign as $assigneeId) {
            CardAssignment::firstOrCreate(
                [
                    'card_id' => $task->card_id,
                    'user_id' => $assigneeId,
                ],
                [
                    'assigned_at' => now(),
                    'assignment_status' => 'assigned',
                ]
            );
        }

        return back()->with('success', 'Members assigned successfully.');
    }

    /**
     * Update task priority (Leader/Admin only)
     */
    public function updatePriority(Request $request, $id)
    {
        $user = Auth::user();
        $task = Card::with('board')->findOrFail($id);

        $projectRole = DB::table('project_members')
            ->where('user_id', $user->user_id)
            ->where('project_id', $task->board->project_id)
            ->value('role');

        if (!($user->role === 'admin' || $projectRole === 'project_manager')) {
            abort(403, 'You are not allowed to update task priority.');
        }

        $request->validate([
            'priority' => 'required|in:high,medium,low',
        ]);

        $task->update([
            'priority' => $request->priority,
        ]);

        return back()->with('success', 'Task priority updated.');
    }

    /**
     * Role-specific task dashboard
     */
    public function roleDashboard($role)
    {
        $user = Auth::user();
        
        // Validate role
        $userProjectRoles = DB::table('project_members')
            ->where('user_id', $user->user_id)
            ->pluck('role')
            ->toArray();
        
        if (!in_array($role, $userProjectRoles)) {
            abort(404, 'Role not found for this user.');
        }

        // Get projects where user has this role
        $projectIds = DB::table('project_members')
            ->where('user_id', $user->user_id)
            ->where('role', $role)
            ->pluck('project_id');

        // Get tasks from these projects
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

        return view("tasks.{$role}-dashboard", compact('tasks', 'stats', 'role', 'projectIds'));
    }
}