<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\CardAssignment;
use App\Models\Project;
use App\Models\Board;
use App\Models\ProjectMember;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Helpers\NotificationHelper;

class LeaderTaskController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // Only apply leader check for specific methods, not for create/store during initial load
        $this->middleware(function ($request, $next) {
            if (!$this->isLeaderOfProject($request)) {
                Log::warning('Leader access denied', [
                    'user_id' => Auth::id(),
                    'role' => Auth::user()->role ?? 'unknown',
                    'project_id' => $request->route('project'),
                    'method' => $request->method()
                ]);
                abort(403, 'Only project leaders can manage tasks in this project.');
            }
            return $next($request);
        });
    }

    /**
     * Check if current user is a leader of the project
     */
    private function isLeaderOfProject($request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return false;
        }
        
        // Admin can manage any project
        if ($user->role === 'admin') {
            return true;
        }

        // Check if user is a leader assigned as project manager
        if ($user->role === 'leader') {
            $projectId = $request->route('project');
            
            if (!$projectId) {
                Log::warning('No project ID in route', ['route' => $request->route()->getName()]);
                return false;
            }
            
            $isMember = ProjectMember::where('user_id', $user->user_id)
                ->where('project_id', $projectId)
                ->where('role', 'project_manager')
                ->exists();
            
            Log::info('Leader project access check', [
                'user_id' => $user->user_id,
                'project_id' => $projectId,
                'is_project_manager' => $isMember
            ]);
            
            return $isMember;
        }

        return false;
    }

    /**
     * Show task creation form for leaders
     */
    public function create($projectId)
    {
        $project = Project::with(['boards', 'members.user'])->findOrFail($projectId);
        
        // Get team members (developers and designers) for this project
        $teamMembers = $project->members()
            ->whereIn('role', ['developer', 'designer'])
            ->with('user')
            ->get();

        return view('leader.tasks.create', compact('project', 'teamMembers'));
    }

    /**
     * Store a new task assigned by leader
     */
    public function store(Request $request, $projectId)
    {
        Log::info('Leader task creation started', [
            'user_id' => Auth::id(),
            'project_id' => $projectId,
            'data' => $request->all()
        ]);
        
        $request->validate([
            'board_id' => 'required|exists:boards,board_id',
            'title' => 'required|string|max:100',
            'description' => 'nullable|string|max:1000',
            'priority' => 'required|in:low,medium,high,critical',
            'status' => 'required|in:todo,in_progress,review,done',
            'due_date' => 'nullable|date|after:today',
            'estimated_hours' => 'nullable|numeric|min:0.1|max:999.99',
            'assigned_users' => 'required|array|min:1',
            'assigned_users.*' => 'exists:users,user_id'
        ]);
        
        Log::info('Leader task validation passed');

        // Verify board belongs to project
        $board = Board::where('board_id', $request->board_id)
            ->where('project_id', $projectId)
            ->firstOrFail();

        // Verify assigned users are project members
        $projectMembers = ProjectMember::where('project_id', $projectId)
            ->whereIn('user_id', $request->assigned_users)
            ->whereIn('role', ['developer', 'designer'])
            ->pluck('user_id')
            ->toArray();

        if (count($projectMembers) !== count($request->assigned_users)) {
            return back()->withErrors(['assigned_users' => 'Some selected users are not project members.']);
        }

        DB::beginTransaction();
        try {
            // Create the task
            $task = Card::create([
                'board_id' => $request->board_id,
                'card_title' => $request->title,
                'description' => $request->description,
                'created_by' => Auth::id(),
                'priority' => $request->priority,
                'status' => $request->status,
                'due_date' => $request->due_date,
                'estimated_hours' => $request->estimated_hours,
            ]);

            // Assign users to the task and send notifications
            foreach ($request->assigned_users as $userId) {
                CardAssignment::create([
                    'card_id' => $task->card_id,
                    'user_id' => $userId,
                    'assigned_at' => now(),
                    'assignment_status' => 'assigned'
                ]);
                
                // Notify assigned user
                NotificationHelper::taskCreatedAndAssigned(
                    $task,
                    $userId,
                    Auth::id()
                );
            }

            Log::info('Task created and notifications sent', [
                'task_id' => $task->card_id,
                'assigned_users' => $request->assigned_users
            ]);

            DB::commit();

            return redirect()
                ->route('leader.projects.show', $projectId)
                ->with('success', 'Task created and assigned successfully!');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Leader task creation failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to create task. Please try again.']);
        }
    }

    /**
     * Show task details for leaders with management options
     */
    public function show($projectId, $taskId)
    {
        $task = Card::with([
            'board.project',
            'assignments.user',
            'subtasks',
            'comments.user'
        ])->findOrFail($taskId);

        // Verify task belongs to the project
        if ($task->board->project_id != $projectId) {
            abort(404, 'Task not found in this project.');
        }

        $project = Project::with('members.user')->findOrFail($projectId);
        
        // Get available team members for reassignment
        $teamMembers = $project->members()
            ->whereIn('role', ['developer', 'designer'])
            ->with('user')
            ->get();

        return view('leader.tasks.show', compact('task', 'project', 'teamMembers'));
    }

    /**
     * Update task priority and status
     */
    public function updatePriorityAndStatus(Request $request, $projectId, $taskId)
    {
        $request->validate([
            'priority' => 'required|in:low,medium,high,critical',
            'status' => 'required|in:todo,in_progress,review,done'
        ]);

        $task = Card::whereHas('board', function($query) use ($projectId) {
            $query->where('project_id', $projectId);
        })->findOrFail($taskId);

        $task->update([
            'priority' => $request->priority,
            'status' => $request->status
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Task priority and status updated successfully!'
        ]);
    }

    /**
     * Reassign task to different team members
     */
    public function reassignTask(Request $request, $projectId, $taskId)
    {
        $request->validate([
            'assigned_users' => 'required|array|min:1',
            'assigned_users.*' => 'exists:users,user_id'
        ]);

        $task = Card::whereHas('board', function($query) use ($projectId) {
            $query->where('project_id', $projectId);
        })->findOrFail($taskId);

        // Verify assigned users are project members
        $projectMembers = ProjectMember::where('project_id', $projectId)
            ->whereIn('user_id', $request->assigned_users)
            ->whereIn('role', ['developer', 'designer'])
            ->pluck('user_id')
            ->toArray();

        if (count($projectMembers) !== count($request->assigned_users)) {
            return response()->json([
                'success' => false,
                'message' => 'Some selected users are not project members.'
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Remove existing assignments
            CardAssignment::where('card_id', $taskId)->delete();

            // Create new assignments
            foreach ($request->assigned_users as $userId) {
                CardAssignment::create([
                    'card_id' => $taskId,
                    'user_id' => $userId,
                    'assigned_at' => now(),
                    'assignment_status' => 'assigned'
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Task reassigned successfully!'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Task reassignment failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to reassign task. Please try again.'
            ], 500);
        }
    }

    /**
     * Get project progress overview for leaders
     */
    public function getProjectProgress($projectId)
    {
        $project = Project::with(['members.user', 'boards.cards'])->findOrFail($projectId);

        // Task statistics
        $totalTasks = $project->boards->flatMap->cards->count();
        $completedTasks = $project->boards->flatMap->cards->where('status', 'done')->count();
        $inProgressTasks = $project->boards->flatMap->cards->where('status', 'in_progress')->count();
        $todoTasks = $project->boards->flatMap->cards->where('status', 'todo')->count();
        $reviewTasks = $project->boards->flatMap->cards->where('status', 'review')->count();

        // Priority breakdown
        $criticalTasks = $project->boards->flatMap->cards->where('priority', 'critical')->count();
        $highTasks = $project->boards->flatMap->cards->where('priority', 'high')->count();
        $mediumTasks = $project->boards->flatMap->cards->where('priority', 'medium')->count();
        $lowTasks = $project->boards->flatMap->cards->where('priority', 'low')->count();

        // Team member performance
        $teamPerformance = DB::table('project_members')
            ->join('users', 'project_members.user_id', '=', 'users.user_id')
            ->leftJoin('card_assignments', 'users.user_id', '=', 'card_assignments.user_id')
            ->leftJoin('cards', 'card_assignments.card_id', '=', 'cards.card_id')
            ->leftJoin('boards', 'cards.board_id', '=', 'boards.board_id')
            ->where('project_members.project_id', $projectId)
            ->whereIn('project_members.role', ['developer', 'designer'])
            ->select(
                'users.user_id',
                'users.full_name',
                'users.email',
                'project_members.role',
                DB::raw('COUNT(DISTINCT cards.card_id) as total_tasks'),
                DB::raw('COUNT(DISTINCT CASE WHEN cards.status = "done" THEN cards.card_id END) as completed_tasks'),
                DB::raw('COUNT(DISTINCT CASE WHEN cards.status = "in_progress" THEN cards.card_id END) as active_tasks')
            )
            ->groupBy('users.user_id', 'users.full_name', 'users.email', 'project_members.role')
            ->get();

        return response()->json([
            'project' => $project,
            'statistics' => [
                'total_tasks' => $totalTasks,
                'completed_tasks' => $completedTasks,
                'in_progress_tasks' => $inProgressTasks,
                'todo_tasks' => $todoTasks,
                'review_tasks' => $reviewTasks,
                'completion_percentage' => $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 2) : 0
            ],
            'priority_breakdown' => [
                'critical' => $criticalTasks,
                'high' => $highTasks,
                'medium' => $mediumTasks,
                'low' => $lowTasks
            ],
            'team_performance' => $teamPerformance
        ]);
    }

    /**
     * Get tasks list for a project (for leaders)
     */
    public function getProjectTasks(Request $request, $projectId)
    {
        $query = Card::with(['assignments.user', 'board'])
            ->whereHas('board', function($q) use ($projectId) {
                $q->where('project_id', $projectId);
            });

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('assigned_to')) {
            $query->whereHas('assignments', function($q) use ($request) {
                $q->where('user_id', $request->assigned_to);
            });
        }

        $tasks = $query->orderBy('created_at', 'desc')
                      ->orderBy('priority', 'desc')
                      ->paginate(20);

        return response()->json($tasks);
    }
}