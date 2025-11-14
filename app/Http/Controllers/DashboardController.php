<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Card;
use App\Models\Task;
use App\Models\WorkSession;
use App\Models\User;
use App\Models\ProjectMember;
use App\Models\Board;
use App\Models\CardAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Role-based dashboard data
        switch ($user->role) {
            case 'admin':
                return $this->adminDashboard();
            case 'leader':
                return $this->leaderDashboard();
            default:
                return $this->userDashboard();
        }
    }
    
    private function adminDashboard()
    {
        // Admin gets system-wide statistics
        $stats = [
            'totalProjects' => Project::count(),
            'activeProjects' => Project::where('status', 'active')->count(),
            'totalUsers' => User::count(),
            'totalLeaders' => User::where('role', 'leader')->count(),
            'totalCards' => Card::count(),
            'completedCards' => Card::where('status', 'done')->count(),
        ];

        // Recent projects system-wide
        $recentProjects = Project::with('creator')
            ->orderBy('created_at', 'desc')
            ->take(6)
            ->get();

        // System-wide overdue tasks
        $overdueTasks = Card::where('due_date', '<', now())
            ->where('status', '!=', 'done')
            ->with(['board.project', 'assignments.user'])
            ->orderBy('due_date')
            ->limit(5)
            ->get();

        // User activity summary
        $userActivity = User::select('role', DB::raw('count(*) as count'))
            ->groupBy('role')
            ->get();

        // Project status distribution
        $projectStatus = Project::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();

        return view('dashboard.admin', compact('stats', 'recentProjects', 'overdueTasks', 'userActivity', 'projectStatus'));
    }
    
    private function leaderDashboard()
    {
        $user = Auth::user();
        
        // Get projects where user is project manager
        $managedProjectIds = ProjectMember::where('user_id', $user->user_id)
            ->where('role', 'project_manager')
            ->pluck('project_id');
        
        // Team leader statistics
        $stats = [
            'managedProjects' => $managedProjectIds->count(),
            'totalTeamMembers' => ProjectMember::whereIn('project_id', $managedProjectIds)
                ->where('user_id', '!=', $user->user_id)
                ->distinct('user_id')
                ->count(),
            'activeTasks' => Card::whereHas('board', function($query) use ($managedProjectIds) {
                $query->whereIn('project_id', $managedProjectIds);
            })->where('status', '!=', 'done')->count(),
            'completedTasks' => Card::whereHas('board', function($query) use ($managedProjectIds) {
                $query->whereIn('project_id', $managedProjectIds);
            })->where('status', 'done')->count(),
        ];

        // My managed projects
        $managedProjects = Project::whereIn('project_id', $managedProjectIds)
            ->with(['members.user'])
            ->get();

        // Tasks needing attention (overdue or high priority)
        $tasksNeedingAttention = Card::whereHas('board', function($query) use ($managedProjectIds) {
            $query->whereIn('project_id', $managedProjectIds);
        })
        ->where(function($query) {
            $query->where('due_date', '<', now())
                  ->orWhere('priority', 'high');
        })
        ->where('status', '!=', 'done')
        ->with(['board.project', 'assignments.user'])
        ->orderBy('due_date')
        ->limit(5)
        ->get();

        // Team performance summary
        $teamPerformance = ProjectMember::whereIn('project_id', $managedProjectIds)
            ->where('user_id', '!=', $user->user_id)
            ->with(['user', 'project'])
            ->get();

        return view('dashboard.leader', compact('stats', 'managedProjects', 'tasksNeedingAttention', 'teamPerformance'));
    }
    
    private function userDashboard()
    {
        $user = Auth::user();
        
        // Route to specialty-specific dashboard
        if ($user->specialty === 'developer') {
            return $this->developerDashboard();
        } elseif ($user->specialty === 'designer') {
            return $this->designerDashboard();
        }
        
        // Default user dashboard (for users without specialty)
        // Get user's assigned tasks from 'tasks' table
        $assignedTasks = \App\Models\Task::where('assigned_to', $user->user_id)
            ->with(['project']);

        // User statistics
        $stats = [
            'totalTasks' => $assignedTasks->count(),
            'activeTasks' => (clone $assignedTasks)->where('status', 'in_progress')->count(),
            'completedTasks' => (clone $assignedTasks)->where('status', 'done')->count(),
            'overdueTasks' => (clone $assignedTasks)->where('deadline', '<', now())
                ->where('status', '!=', 'done')->count(),
        ];

        // My active tasks (not done)
        $myActiveTasks = (clone $assignedTasks)->where('status', '!=', 'done')
            ->orderBy('deadline')
            ->limit(5)
            ->get()
            ->map(function($task) {
                // Map deadline to due_date for view compatibility
                $task->due_date = $task->deadline;
                // Map project_name to project for view compatibility
                if ($task->project) {
                    $task->board = (object)['project' => $task->project];
                }
                return $task;
            });

        // Recent completed tasks
        $recentCompleted = (clone $assignedTasks)->where('status', 'done')
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function($task) {
                // Map project_name to project for view compatibility
                if ($task->project) {
                    $task->board = (object)['project' => $task->project];
                }
                return $task;
            });

        // Time tracking summary from work_sessions table (this week)
        $timeSpent = \App\Models\WorkSession::where('user_id', $user->user_id)
            ->where('work_date', '>=', now()->startOfWeek())
            ->where('work_date', '<=', now()->endOfWeek())
            ->sum('duration_seconds') / 60; // Convert seconds to minutes
        
        // Today's work time
        $todayTime = \App\Models\WorkSession::where('user_id', $user->user_id)
            ->forDate(now())
            ->sum('duration_seconds');
        
        // Format today's time as HH:MM
        $todayHours = floor($todayTime / 3600);
        $todayMinutes = floor(($todayTime % 3600) / 60);
        $todayFormatted = sprintf('%02d:%02d', $todayHours, $todayMinutes);

        return view('dashboard.user', compact('stats', 'myActiveTasks', 'recentCompleted', 'timeSpent', 'todayFormatted'));
    }
    
    private function developerDashboard()
    {
        $user = Auth::user();
        
        // Get developer's assigned tasks
        $assignedTasks = Card::whereHas('assignments', function($query) use ($user) {
            $query->where('user_id', $user->user_id);
        })->with(['board.project']);

        // Developer statistics
        $stats = [
            'totalTasks' => $assignedTasks->count(),
            'activeTasks' => (clone $assignedTasks)->where('status', 'in_progress')->count(),
            'completedTasks' => (clone $assignedTasks)->where('status', 'done')->count(),
            'overdueTasks' => (clone $assignedTasks)->where('due_date', '<', now())
                ->where('status', '!=', 'done')->count(),
        ];

        // Upcoming deadlines (tasks due in next 7 days)
        $upcomingDeadlines = (clone $assignedTasks)
            ->where('status', '!=', 'done')
            ->whereBetween('due_date', [now(), now()->addDays(7)])
            ->orderBy('due_date')
            ->get();

        // Active tasks with time tracking
        $activeTasks = (clone $assignedTasks)
            ->where('status', '!=', 'done')
            ->orderBy('due_date')
            ->limit(10)
            ->get()
            ->map(function($task) use ($user) {
                // Use WorkSession instead of time_logs
                $task->time_spent = WorkSession::where('user_id', $user->user_id)
                    ->where('task_id', $task->card_id)
                    ->sum('duration_seconds') / 3600; // Convert to hours
                return $task;
            });

        // Time tracking summary using WorkSession
        $timeStats = [
            'today' => WorkSession::where('user_id', $user->user_id)
                ->forDate(now())
                ->sum('duration_seconds') / 3600, // Hours
            'thisWeek' => WorkSession::where('user_id', $user->user_id)
                ->where('work_date', '>=', now()->startOfWeek())
                ->where('work_date', '<=', now()->endOfWeek())
                ->sum('duration_seconds') / 3600, // Hours
            'thisMonth' => WorkSession::where('user_id', $user->user_id)
                ->whereMonth('work_date', now()->month)
                ->whereYear('work_date', now()->year)
                ->sum('duration_seconds') / 3600, // Hours
        ];

        // My projects
        $myProjects = Project::whereHas('members', function($query) use ($user) {
            $query->where('user_id', $user->user_id);
        })->with(['creator', 'members'])
        ->get();

        // Recent completed tasks (last 5)
        $recentCompleted = (clone $assignedTasks)
            ->where('status', 'done')
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get();

        return view('dashboard.developer', compact('stats', 'upcomingDeadlines', 'activeTasks', 'timeStats', 'myProjects', 'recentCompleted'));
    }
    
    private function designerDashboard()
    {
        $user = Auth::user();
        
        // Get designer's assigned tasks
        $assignedTasks = Task::where('assigned_to', $user->user_id)
            ->with(['project', 'taskComments']);

        // Designer statistics
        $stats = [
            'totalTasks' => $assignedTasks->count(),
            'activeTasks' => (clone $assignedTasks)->where('status', 'in_progress')->count(),
            'completedTasks' => (clone $assignedTasks)->where('status', 'done')->count(),
            'pendingFeedback' => (clone $assignedTasks)->whereHas('taskComments', function($query) {
                $query->where('created_at', '>', now()->subDays(1));
            })->count(),
        ];

        // Design tasks with status
        $designTasks = (clone $assignedTasks)
            ->where('status', '!=', 'done')
            ->orderBy('deadline')
            ->get();

        // Recent feedback/comments on my tasks
        $recentFeedback = DB::table('comments')
            ->join('tasks', 'comments.task_id', '=', 'tasks.task_id')
            ->where('tasks.assigned_to', $user->user_id)
            ->where('comments.user_id', '!=', $user->user_id)
            ->whereNotNull('comments.task_id')
            ->select('comments.*', 'tasks.title as task_title')
            ->orderBy('comments.created_at', 'desc')
            ->limit(10)
            ->get();

        // Asset requests (tasks labeled as asset/resource needs)
        $assetRequests = (clone $assignedTasks)
            ->where(function($query) {
                $query->where('title', 'like', '%asset%')
                      ->orWhere('title', 'like', '%resource%')
                      ->orWhere('description', 'like', '%asset%')
                      ->orWhere('description', 'like', '%resource%');
            })
            ->where('status', '!=', 'done')
            ->get();

        // My projects
        $myProjects = Project::whereHas('members', function($query) use ($user) {
            $query->where('user_id', $user->user_id);
        })->with(['leader', 'members'])
        ->get();

        // Review notes (tasks marked for review)
        $reviewNotes = (clone $assignedTasks)
            ->where('status', 'in_review')
            ->orWhere(function($query) {
                $query->where('title', 'like', '%review%')
                      ->orWhere('description', 'like', '%review%');
            })
            ->get();

        // Recent completed designs
        $recentCompleted = (clone $assignedTasks)
            ->where('status', 'done')
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get();

        return view('dashboard.designer', compact('stats', 'designTasks', 'recentFeedback', 'assetRequests', 'myProjects', 'reviewNotes', 'recentCompleted'));
    }
}