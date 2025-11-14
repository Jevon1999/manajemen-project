<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use App\Models\Card;
use App\Models\TimeLog;
use App\Models\ProjectMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ReportsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role.admin']);
    }

    /**
     * Display comprehensive reports management page
     */
    public function management()
    {
        $data = [
            'overview' => $this->getOverviewStats(),
            'projects' => $this->getProjectStats(),
            'users' => $this->getUserStats(),
            'timeTracking' => $this->getTimeTrackingStats('month', null, null),
            'performance' => $this->getPerformanceStats(),
            'activities' => $this->getRecentActivities(),
            'charts' => $this->getChartData(),
        ];

        return view('admin.reports.management', $data);
    }

    /**
     * Display main reports dashboard
     */
    public function index()
    {
        $data = [
            'overview' => $this->getOverviewStats(),
            'projects' => $this->getProjectStats(),
            'users' => $this->getUserStats(),
            'activities' => $this->getRecentActivities(),
            'charts' => $this->getChartsData()
        ];

        return view('admin.reports.index', $data);
    }

    /**
     * Projects detailed report
     */
    public function projects(Request $request)
    {
        $period = $request->get('period', '30'); // days
        $status = $request->get('status', 'all');
        $category = $request->get('category', 'all');

        $query = Project::with(['creator', 'template', 'members'])
            ->withCount(['members', 'boards']);

        // Filter by period
        if ($period !== 'all') {
            $query->where('created_at', '>=', Carbon::now()->subDays($period));
        }

        // Filter by status
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        // Filter by category
        if ($category !== 'all') {
            $query->where('category', $category);
        }

        $projects = $query->orderBy('created_at', 'desc')->paginate(20);

        $data = [
            'projects' => $projects,
            'filters' => compact('period', 'status', 'category'),
            'stats' => $this->getProjectsDetailedStats($period, $status, $category),
            'categories' => Project::getCategoryOptions(),
            'statuses' => Project::getStatusOptions()
        ];

        return view('admin.reports.projects', $data);
    }

    /**
     * Users performance report
     */
    public function users(Request $request)
    {
        $period = $request->get('period', '30');
        $role = $request->get('role', 'all');

        $query = User::with(['createdProjects', 'projectMemberships'])
            ->withCount(['createdProjects', 'projectMemberships']);

        // Filter by role
        if ($role !== 'all') {
            $query->where('role', $role);
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(20);

        // Get user activity stats
        $userStats = $this->getUserActivityStats($period, $role);

        $data = [
            'users' => $users,
            'userStats' => $userStats,
            'filters' => compact('period', 'role'),
            'roles' => ['admin' => 'Admin', 'leader' => 'Leader', 'user' => 'User']
        ];

        return view('admin.reports.users', $data);
    }

    /**
     * Time tracking report
     */
    public function timeTracking(Request $request)
    {
        $period = $request->get('period', '30');
        $project_id = $request->get('project_id', 'all');
        $user_id = $request->get('user_id', 'all');

        $query = TimeLog::with(['user', 'card.board.project']);

        // Filter by period
        if ($period !== 'all') {
            $query->where('start_time', '>=', Carbon::now()->subDays($period));
        }

        // Filter by project
        if ($project_id !== 'all') {
            $query->whereHas('card.board', function($q) use ($project_id) {
                $q->where('project_id', $project_id);
            });
        }

        // Filter by user
        if ($user_id !== 'all') {
            $query->where('user_id', $user_id);
        }

        $timeLogs = $query->orderBy('start_time', 'desc')->paginate(50);

        $data = [
            'timeLogs' => $timeLogs,
            'filters' => compact('period', 'project_id', 'user_id'),
            'timeStats' => $this->getTimeTrackingStats($period, $project_id, $user_id),
            'projects' => Project::select('project_id', 'project_name')->get(),
            'users' => User::select('user_id', 'name')->get()
        ];

        return view('admin.reports.time-tracking', $data);
    }

    /**
     * Performance analytics
     */
    public function performance(Request $request)
    {
        $period = $request->get('period', '30');

        $data = [
            'productivity' => $this->getProductivityMetrics($period),
            'efficiency' => $this->getEfficiencyMetrics($period),
            'quality' => $this->getQualityMetrics($period),
            'trends' => $this->getTrendAnalysis($period),
            'period' => $period
        ];

        return view('admin.reports.performance', $data);
    }

    /**
     * Export reports to various formats
     */
    public function export(Request $request)
    {
        $type = $request->get('type', 'projects');
        $format = $request->get('format', 'excel');
        
        switch ($type) {
            case 'projects':
                return $this->exportProjects($format, $request);
            case 'users':
                return $this->exportUsers($format, $request);
            case 'time':
                return $this->exportTimeTracking($format, $request);
            default:
                return redirect()->back()->with('error', 'Invalid export type');
        }
    }

    /**
     * Get overview statistics
     */
    private function getOverviewStats()
    {
        return [
            'total_projects' => Project::count(),
            'active_projects' => Project::where('status', 'active')->count(),
            'completed_projects' => Project::where('status', 'completed')->count(),
            'total_users' => User::count(),
            'active_users' => User::where('status', 'active')->count(),
            'total_tasks' => Card::count(),
            'completed_tasks' => Card::where('status', 'done')->count(),
            'total_hours' => TimeLog::sum(DB::raw('duration_minutes / 60')),
            'this_month_projects' => Project::whereMonth('created_at', Carbon::now()->month)->count(),
            'this_month_hours' => TimeLog::whereMonth('start_time', Carbon::now()->month)
                ->sum(DB::raw('duration_minutes / 60'))
        ];
    }

    /**
     * Get project statistics
     */
    private function getProjectStats()
    {
        return [
            'by_status' => Project::select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray(),
            'by_category' => Project::select('category', DB::raw('count(*) as count'))
                ->groupBy('category')
                ->pluck('count', 'category')
                ->toArray(),
            'by_priority' => Project::select('priority', DB::raw('count(*) as count'))
                ->groupBy('priority')
                ->pluck('count', 'priority')
                ->toArray(),
            'completion_rates' => $this->getProjectCompletionRates()
        ];
    }

    /**
     * Get user statistics
     */
    private function getUserStats()
    {
        return [
            'by_role' => User::select('role', DB::raw('count(*) as count'))
                ->groupBy('role')
                ->pluck('count', 'role')
                ->toArray(),
            'by_status' => User::select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray(),
            'most_active' => $this->getMostActiveUsers(),
            'project_leaders' => $this->getProjectLeaderStats()
        ];
    }

    /**
     * Get recent activities
     */
    private function getRecentActivities($limit = 10)
    {
        $activities = collect();

        // Recent projects
        $recentProjects = Project::with('creator')
            ->latest()
            ->take($limit)
            ->get()
            ->map(function($project) {
                return [
                    'type' => 'project_created',
                    'description' => "Project '{$project->project_name}' was created",
                    'user' => $project->creator->name ?? 'Unknown',
                    'date' => $project->created_at,
                    'icon' => 'fas fa-folder-plus',
                    'color' => 'success'
                ];
            });

        // Recent time logs
        $recentLogs = TimeLog::with(['user', 'card'])
            ->latest('start_time')
            ->take($limit)
            ->get()
            ->map(function($log) {
                $cardTitle = $log->card ? $log->card->title : 'Unknown';
                $userName = $log->user ? $log->user->name : 'Unknown';
                return [
                    'type' => 'time_logged',
                    'description' => "Time logged on task '{$cardTitle}'",
                    'user' => $userName,
                    'date' => $log->start_time,
                    'icon' => 'fas fa-clock',
                    'color' => 'info'
                ];
            });

        return $activities->merge($recentProjects)
            ->merge($recentLogs)
            ->sortByDesc('date')
            ->take($limit)
            ->values();
    }

    /**
     * Get charts data
     */
    private function getChartsData()
    {
        return [
            'projects_timeline' => $this->getProjectsTimelineData(),
            'tasks_completion' => $this->getTasksCompletionData(),
            'user_activity' => $this->getUserActivityData(),
            'time_distribution' => $this->getTimeDistributionData()
        ];
    }

    /**
     * Get projects timeline data for charts
     */
    private function getProjectsTimelineData()
    {
        $data = Project::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('count(*) as count')
            )
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'labels' => $data->pluck('date')->map(function($date) {
                return Carbon::parse($date)->format('M j');
            }),
            'data' => $data->pluck('count')
        ];
    }

    /**
     * Get tasks completion data
     */
    private function getTasksCompletionData()
    {
        $statusCounts = Card::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return [
            'labels' => array_keys($statusCounts),
            'data' => array_values($statusCounts)
        ];
    }

    /**
     * Get user activity data
     */
    private function getUserActivityData()
    {
        return TimeLog::select(
                DB::raw('DATE(start_time) as date'),
                DB::raw('SUM(duration_minutes) / 60 as hours')
            )
            ->where('start_time', '>=', Carbon::now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function($item) {
                return [
                    'date' => Carbon::parse($item->date)->format('M j'),
                    'hours' => round($item->hours, 2)
                ];
            });
    }

    /**
     * Get time distribution data
     */
    private function getTimeDistributionData()
    {
        return TimeLog::join('cards', 'time_logs.card_id', '=', 'cards.card_id')
            ->join('boards', 'cards.board_id', '=', 'boards.board_id')
            ->join('projects', 'boards.project_id', '=', 'projects.project_id')
            ->select(
                'projects.category',
                DB::raw('SUM(time_logs.duration_minutes) / 60 as hours')
            )
            ->groupBy('projects.category')
            ->get()
            ->pluck('hours', 'category')
            ->toArray();
    }

    /**
     * Get project completion rates
     */
    private function getProjectCompletionRates()
    {
        $projects = Project::with('boards.cards')->get();
        
        return $projects->map(function($project) {
            $totalTasks = $project->boards->sum(function($board) {
                return $board->cards->count();
            });
            
            $completedTasks = $project->boards->sum(function($board) {
                return $board->cards->where('status', 'done')->count();
            });
            
            $rate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 2) : 0;
            
            return [
                'name' => $project->project_name,
                'completion_rate' => $rate,
                'total_tasks' => $totalTasks,
                'completed_tasks' => $completedTasks
            ];
        })->sortByDesc('completion_rate')->take(10);
    }

    /**
     * Get most active users
     */
    private function getMostActiveUsers()
    {
        return TimeLog::select(
                'users.name',
                'users.user_id',
                DB::raw('SUM(time_logs.duration_minutes) / 60 as total_hours'),
                DB::raw('COUNT(DISTINCT DATE(time_logs.start_time)) as active_days')
            )
            ->join('users', 'time_logs.user_id', '=', 'users.user_id')
            ->where('time_logs.start_time', '>=', Carbon::now()->subDays(30))
            ->groupBy('users.user_id', 'users.name')
            ->orderByDesc('total_hours')
            ->take(10)
            ->get();
    }

    /**
     * Get project leader statistics
     */
    private function getProjectLeaderStats()
    {
        return ProjectMember::select(
                'users.name',
                'users.user_id',
                DB::raw('COUNT(DISTINCT project_members.project_id) as projects_led'),
                DB::raw('AVG(projects.completion_percentage) as avg_completion')
            )
            ->join('users', 'project_members.user_id', '=', 'users.user_id')
            ->join('projects', 'project_members.project_id', '=', 'projects.project_id')
            ->where('project_members.role', 'project_manager')
            ->groupBy('users.user_id', 'users.name')
            ->orderByDesc('projects_led')
            ->take(10)
            ->get();
    }

    /**
     * Get detailed project stats with filters
     */
    private function getProjectsDetailedStats($period, $status, $category)
    {
        $query = Project::query();

        if ($period !== 'all') {
            $query->where('created_at', '>=', Carbon::now()->subDays($period));
        }

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($category !== 'all') {
            $query->where('category', $category);
        }

        return [
            'total' => $query->count(),
            'avg_budget' => $query->whereNotNull('budget')->avg('budget'),
            'total_budget' => $query->sum('budget'),
            'avg_completion' => $query->avg('completion_percentage'),
            'with_templates' => $query->whereNotNull('template_id')->count()
        ];
    }

    /**
     * Get user activity statistics
     */
    private function getUserActivityStats($period, $role)
    {
        $query = User::with('projectMemberships');

        if ($role !== 'all') {
            $query->where('role', $role);
        }

        $users = $query->get();

        $totalProjects = $users->sum(function($user) {
            return $user->projectMemberships ? $user->projectMemberships->count() : 0;
        });
        
        $avgProjectsPerUser = $users->count() > 0 ? round($totalProjects / $users->count(), 2) : 0;

        return [
            'total_users' => $users->count(),
            'active_users' => $users->where('status', 'active')->count(),
            'avg_projects_per_user' => $avgProjectsPerUser,
        ];
    }

    /**
     * Get time tracking statistics
     */
    private function getTimeTrackingStats($period, $project_id, $user_id)
    {
        $query = TimeLog::query();

        // Convert period to days
        $days = match($period) {
            'week' => 7,
            'month' => 30,
            'quarter' => 90,
            'year' => 365,
            default => null
        };

        if ($days !== null && $period !== 'all') {
            $query->where('start_time', '>=', Carbon::now()->subDays($days));
        }

        if ($project_id !== null && $project_id !== 'all') {
            $query->whereHas('card.board', function($q) use ($project_id) {
                $q->where('project_id', $project_id);
            });
        }

        if ($user_id !== null && $user_id !== 'all') {
            $query->where('user_id', $user_id);
        }

        $totalMinutes = $query->sum('duration_minutes') ?: 0;

        return [
            'total_hours' => round($totalMinutes / 60, 2),
            'total_sessions' => $query->count(),
            'avg_session_length' => $query->count() > 0 ? round($totalMinutes / $query->count(), 2) : 0,
            'most_productive_day' => $this->getMostProductiveDay($query)
        ];
    }

    /**
     * Get most productive day from time logs
     */
    private function getMostProductiveDay($query)
    {
        $dayData = $query->select(
                DB::raw('DAYNAME(start_time) as day_name'),
                DB::raw('SUM(duration_minutes) as total_minutes')
            )
            ->groupBy('day_name')
            ->orderByDesc('total_minutes')
            ->first();

        return $dayData ? $dayData->day_name : 'No data';
    }

    /**
     * Get productivity metrics
     */
    private function getProductivityMetrics($period)
    {
        // Implementation for productivity metrics
        return [
            'tasks_per_day' => $this->getTasksPerDay($period),
            'completion_velocity' => $this->getCompletionVelocity($period),
            'productivity_trends' => $this->getProductivityTrends($period)
        ];
    }

    /**
     * Get efficiency metrics
     */
    private function getEfficiencyMetrics($period)
    {
        // Implementation for efficiency metrics
        return [
            'estimated_vs_actual' => $this->getEstimatedVsActual($period),
            'time_utilization' => $this->getTimeUtilization($period),
            'efficiency_trends' => $this->getEfficiencyTrends($period)
        ];
    }

    /**
     * Get quality metrics
     */
    private function getQualityMetrics($period)
    {
        // Implementation for quality metrics
        return [
            'bug_rates' => $this->getBugRates($period),
            'rework_percentage' => $this->getReworkPercentage($period),
            'quality_trends' => $this->getQualityTrends($period)
        ];
    }

    /**
     * Get trend analysis
     */
    private function getTrendAnalysis($period)
    {
        // Implementation for trend analysis
        return [
            'project_trends' => $this->getProjectTrends($period),
            'user_trends' => $this->getUserTrends($period),
            'performance_trends' => $this->getPerformanceTrends($period)
        ];
    }

    // Additional helper methods for metrics calculations...
    private function getTasksPerDay($period) { return 0; }
    private function getCompletionVelocity($period) { return 0; }
    private function getProductivityTrends($period) { return []; }
    private function getEstimatedVsActual($period) { return 0; }
    private function getTimeUtilization($period) { return 0; }
    private function getEfficiencyTrends($period) { return []; }
    private function getBugRates($period) { return 0; }
    private function getReworkPercentage($period) { return 0; }
    private function getQualityTrends($period) { return []; }
    private function getProjectTrends($period) { return []; }
    private function getUserTrends($period) { return []; }
    private function getPerformanceTrends($period) { return []; }

    /**
     * Get chart data for reports management page
     */
    private function getChartData()
    {
        $thirtyDaysAgo = Carbon::now()->subDays(30);
        
        return [
            'projectProgress' => [
                'labels' => Project::select('name')->get()->pluck('name'),
                'data' => Project::select('name', 'progress')->get()->pluck('progress'),
            ],
            'userActivity' => [
                'labels' => ['Active', 'Inactive'],
                'data' => [
                    User::where('status', 'active')->count(),
                    User::where('status', 'inactive')->count(),
                ],
            ],
            'taskStatus' => [
                'labels' => ['To Do', 'In Progress', 'Completed'],
                'data' => [
                    Card::where('status', 'todo')->count(),
                    Card::where('status', 'in_progress')->count(),
                    Card::where('status', 'completed')->count(),
                ],
            ],
            'monthlyProgress' => [
                'labels' => collect(range(1, 30))->map(function($day) {
                    return Carbon::now()->subDays(30 - $day)->format('M d');
                }),
                'data' => collect(range(1, 30))->map(function($day) {
                    $date = Carbon::now()->subDays(30 - $day);
                    return Card::whereDate('created_at', $date)->count();
                }),
            ],
        ];
    }

    /**
     * Export methods
     */
    private function exportProjects($format, $request)
    {
        // Implementation for exporting projects data
        return response()->json(['message' => 'Export functionality coming soon']);
    }

    private function exportUsers($format, $request)
    {
        // Implementation for exporting users data
        return response()->json(['message' => 'Export functionality coming soon']);
    }

    private function exportTimeTracking($format, $request)
    {
        // Implementation for exporting time tracking data
        return response()->json(['message' => 'Export functionality coming soon']);
    }
}