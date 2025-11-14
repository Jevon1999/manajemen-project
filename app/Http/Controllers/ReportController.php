<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TimeLog;
use App\Models\Project;
use App\Models\User;
use App\Models\ReportLog;
use App\Models\WorkSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Display admin report generation form
     */
    public function index(Request $request)
    {
        // Check if user is admin
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized');
        }

        // Get filter data
        $projects = Project::orderBy('project_name')->get();
        $users = User::whereIn('role', ['developer', 'designer'])->orderBy('full_name')->get();
        
        // Get recent reports
        $recentReports = ReportLog::with('user')
            ->where('user_id', Auth::id())
            ->orderByDesc('generated_at')
            ->limit(10)
            ->get();

        // Get system overview statistics
        $statistics = [
            'total_projects' => Project::count(),
            'active_projects' => Project::where('status', 'active')->count(),
            'total_tasks' => Task::count(),
            'completed_tasks' => Task::where('status', 'done')->count(),
        ];

        // Get report type from query parameter
        $reportType = $request->get('type', 'general');
        
        // Get additional data based on report type
        $monthlyData = [];
        $yearlyData = [];
        $projectData = [];
        
        if ($reportType === 'monthly') {
            $monthlyData = $this->getMonthlyData();
        } elseif ($reportType === 'yearly') {
            $yearlyData = $this->getYearlyData();
        } elseif ($reportType === 'project') {
            $projectData = $this->getProjectData();
        }

        return view('admin.reports.index', compact('projects', 'users', 'recentReports', 'statistics', 'reportType', 'monthlyData', 'yearlyData', 'projectData'));
    }

    /**
     * Generate combined CSV report
     */
    public function generate(Request $request)
    {
        // Check if user is admin
        if (Auth::user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Validate request
        $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'project_id' => 'nullable|exists:projects,project_id',
            'user_id' => 'nullable|exists:users,user_id',
            'status' => 'nullable|in:todo,in_progress,review,done',
        ]);

        $filters = $request->only(['date_from', 'date_to', 'project_id', 'user_id', 'status']);
        
        try {
            // Generate CSV content
            $csvContent = $this->generateCombinedReport($filters);
            
            // Create filename
            $filename = 'combined_report_' . Carbon::now()->format('Y-m-d_His') . '.csv';
            $filePath = 'reports/' . $filename;
            
            // Save to storage
            Storage::put($filePath, $csvContent);
            
            // Log the report generation
            ReportLog::create([
                'user_id' => Auth::id(),
                'report_type' => 'combined',
                'filters' => $filters,
                'file_path' => $filePath,
                'generated_at' => now(),
            ]);
            
            // Return download response
            return response()->streamDownload(function() use ($csvContent) {
                echo $csvContent;
            }, $filename, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
            
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to generate report: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Generate combined CSV report content
     */
    private function generateCombinedReport($filters)
    {
        $output = fopen('php://temp', 'r+');
        
        // Add BOM for UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // CSV Headers
        $headers = [
            'Report Section',
            'Date',
            'Project',
            'Task',
            'User',
            'Role',
            'Status',
            'Priority',
            'Work Hours',
            'Start Time',
            'Stop Time',
            'Deadline',
            'Completed At',
            'Notes'
        ];
        fputcsv($output, $headers);
        
        // SECTION 1: Project Summary
        $this->addProjectSummary($output, $filters);
        
        // SECTION 2: Task Summary
        $this->addTaskSummary($output, $filters);
        
        // SECTION 3: Work Time Tracking
        $this->addWorkTimeSummary($output, $filters);
        
        // SECTION 4: User Performance
        $this->addUserPerformance($output, $filters);
        
        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);
        
        return $csvContent;
    }

    /**
     * Add Project Summary section to CSV
     */
    private function addProjectSummary($output, $filters)
    {
        $query = Project::with('leader');
        
        if (!empty($filters['project_id'])) {
            $query->where('project_id', $filters['project_id']);
        }
        
        $projects = $query->get();
        
        /** @var \App\Models\Project $project */
        foreach ($projects as $project) {
            // Count tasks in date range
            $taskCount = Task::where('project_id', $project->project_id)
                ->whereBetween('created_at', [$filters['date_from'], $filters['date_to']])
                ->count();
            
            // Calculate total work hours
            $totalSeconds = WorkSession::whereHas('task', function($q) use ($project) {
                    $q->where('project_id', $project->project_id);
                })
                ->whereBetween('work_date', [$filters['date_from'], $filters['date_to']])
                ->sum('duration_seconds') ?? 0;
            
            $workHours = round($totalSeconds / 3600, 2);
            
            fputcsv($output, [
                'Project Summary',
                Carbon::parse($filters['date_from'])->format('Y-m-d') . ' to ' . Carbon::parse($filters['date_to'])->format('Y-m-d'),
                $project->project_name,
                $taskCount . ' tasks',
                $project->leader ? $project->leader->full_name : 'N/A',
                $project->leader ? $project->leader->role : 'N/A',
                $project->status,
                'N/A',
                $workHours,
                'N/A',
                'N/A',
                Carbon::parse($project->deadline)->format('Y-m-d'),
                $project->completed_at ? Carbon::parse($project->completed_at)->format('Y-m-d H:i') : 'N/A',
                $project->description
            ]);
        }
        
        // Add separator
        fputcsv($output, []);
    }

    /**
     * Add Task Summary section to CSV
     */
    private function addTaskSummary($output, $filters)
    {
        $query = Task::with(['project', 'assignedUser'])
            ->whereBetween('created_at', [$filters['date_from'], $filters['date_to']]);
        
        if (!empty($filters['project_id'])) {
            $query->where('project_id', $filters['project_id']);
        }
        
        if (!empty($filters['user_id'])) {
            $query->where('assigned_to', $filters['user_id']);
        }
        
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        $tasks = $query->orderBy('created_at', 'desc')->get();
        
        foreach ($tasks as $task) {
            // Calculate total work hours for this task
            $totalSeconds = WorkSession::where('task_id', $task->task_id)
                ->whereBetween('work_date', [$filters['date_from'], $filters['date_to']])
                ->sum('duration_seconds') ?? 0;
            
            $workHours = round($totalSeconds / 3600, 2);
            
            fputcsv($output, [
                'Task Summary',
                Carbon::parse($task->created_at)->format('Y-m-d H:i'),
                $task->project ? $task->project->project_name : 'N/A',
                $task->title,
                $task->assignedUser ? $task->assignedUser->full_name : 'Unassigned',
                $task->assignedUser ? $task->assignedUser->role : 'N/A',
                $task->status,
                $task->priority,
                $workHours,
                'N/A',
                'N/A',
                $task->deadline ? Carbon::parse($task->deadline)->format('Y-m-d') : 'N/A',
                $task->completed_at ? Carbon::parse($task->completed_at)->format('Y-m-d H:i') : 'N/A',
                $task->description
            ]);
        }
        
        // Add separator
        fputcsv($output, []);
    }

    /**
     * Add Work Time Tracking section to CSV
     */
    private function addWorkTimeSummary($output, $filters)
    {
        $query = WorkSession::with(['user', 'task.project'])
            ->whereBetween('work_date', [$filters['date_from'], $filters['date_to']]);
        
        if (!empty($filters['project_id'])) {
            $query->whereHas('task', function($q) use ($filters) {
                $q->where('project_id', $filters['project_id']);
            });
        }
        
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }
        
        $sessions = $query->orderBy('work_date', 'desc')
            ->orderBy('started_at', 'desc')
            ->get();
        
        foreach ($sessions as $session) {
            $workHours = round($session->duration_seconds / 3600, 2);
            
            fputcsv($output, [
                'Work Time Tracking',
                Carbon::parse($session->work_date)->format('Y-m-d'),
                $session->task && $session->task->project ? $session->task->project->project_name : 'N/A',
                $session->task ? $session->task->title : 'N/A',
                $session->user->full_name,
                $session->user->role,
                $session->status,
                'N/A',
                $workHours,
                $session->started_at ? $session->started_at->format('H:i') : 'N/A',
                $session->stopped_at ? $session->stopped_at->format('H:i') : 'Active',
                'N/A',
                'N/A',
                'Work session'
            ]);
        }
        
        // Add separator
        fputcsv($output, []);
    }

    /**
     * Add User Performance section to CSV
     */
    private function addUserPerformance($output, $filters)
    {
        $query = User::whereIn('role', ['developer', 'designer']);
        
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }
        
        $users = $query->get();
        
        foreach ($users as $user) {
            // Count completed tasks in date range
            $completedTasks = Task::where('assigned_to', $user->user_id)
                ->where('status', 'done')
                ->whereBetween('completed_at', [$filters['date_from'], $filters['date_to']]);
            
            if (!empty($filters['project_id'])) {
                $completedTasks->where('project_id', $filters['project_id']);
            }
            
            $completedCount = $completedTasks->count();
            
            // Calculate total work hours
            $totalSeconds = WorkSession::where('user_id', $user->user_id)
                ->whereBetween('work_date', [$filters['date_from'], $filters['date_to']]);
            
            if (!empty($filters['project_id'])) {
                $totalSeconds->whereHas('task', function($q) use ($filters) {
                    $q->where('project_id', $filters['project_id']);
                });
            }
            
            $seconds = $totalSeconds->sum('duration_seconds') ?? 0;
            $workHours = round($seconds / 3600, 2);
            
            // Calculate average hours per task
            $avgHours = $completedCount > 0 ? round($workHours / $completedCount, 2) : 0;
            
            fputcsv($output, [
                'User Performance',
                Carbon::parse($filters['date_from'])->format('Y-m-d') . ' to ' . Carbon::parse($filters['date_to'])->format('Y-m-d'),
                !empty($filters['project_id']) ? Project::find($filters['project_id'])->project_name : 'All Projects',
                $completedCount . ' completed tasks',
                $user->full_name,
                $user->role,
                'N/A',
                'N/A',
                $workHours,
                'N/A',
                'N/A',
                'N/A',
                'N/A',
                "Avg: {$avgHours}h per task, Specialty: " . ($user->specialty ?? 'N/A')
            ]);
        }
    }

    /**
     * Get overall statistics (for analytics dashboard)
     */
    private function getOverallStats($projectId, $startDate, $endDate)
    {
        $query = Task::whereBetween('created_at', [$startDate, $endDate]);
        
        if ($projectId) {
            $query->where('project_id', $projectId);
        }
        
        $totalTasks = $query->count();
        $completedTasks = $query->where('status', 'done')->count();
        $inProgressTasks = $query->where('status', 'in_progress')->count();
        $overdueTasks = $query->where('status', '!=', 'done')
            ->where('deadline', '<', now())
            ->count();
        
        $completionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 1) : 0;
        
        // Total time spent
        $timeQuery = TimeLog::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('end_time');
            
        if ($projectId) {
            $timeQuery->whereHas('task', function($q) use ($projectId) {
                $q->where('project_id', $projectId);
            });
        }
        
        $totalSeconds = $timeQuery->sum('duration_seconds') ?? 0;
        $avgTaskTime = $completedTasks > 0 ? $totalSeconds / $completedTasks : 0;
        
        return [
            'total_tasks' => $totalTasks,
            'completed_tasks' => $completedTasks,
            'in_progress_tasks' => $inProgressTasks,
            'overdue_tasks' => $overdueTasks,
            'completion_rate' => $completionRate,
            'total_time_seconds' => $totalSeconds,
            'total_time_formatted' => $this->formatDuration($totalSeconds),
            'avg_task_time_seconds' => $avgTaskTime,
            'avg_task_time_formatted' => $this->formatDuration($avgTaskTime),
        ];
    }

    /**
     * Get tasks breakdown by status
     */
    private function getTasksByStatus($projectId, $startDate, $endDate)
    {
        $query = Task::selectRaw('status, COUNT(*) as count')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('status');
            
        if ($projectId) {
            $query->where('project_id', $projectId);
        }
        
        return $query->get()->mapWithKeys(function ($item) {
            return [$item->status => $item->count];
        });
    }

    /**
     * Get tasks breakdown by priority
     */
    private function getTasksByPriority($projectId, $startDate, $endDate)
    {
        $query = Task::selectRaw('priority, COUNT(*) as count')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('priority');
            
        if ($projectId) {
            $query->where('project_id', $projectId);
        }
        
        return $query->get()->mapWithKeys(function ($item) {
            return [$item->priority => $item->count];
        });
    }

    /**
     * Get top performers
     */
    private function getTopPerformers($projectId, $startDate, $endDate, $limit = 5)
    {
        $query = User::select('users.*')
            ->selectRaw('COUNT(tasks.task_id) as completed_tasks')
            ->selectRaw('SUM(time_logs.duration_seconds) as total_time')
            ->join('tasks', function($join) use ($startDate, $endDate) {
                $join->on('users.user_id', '=', 'tasks.assigned_to')
                     ->where('tasks.status', '=', 'done')
                     ->whereBetween('tasks.completed_at', [$startDate, $endDate]);
            })
            ->leftJoin('time_logs', function($join) {
                $join->on('tasks.task_id', '=', 'time_logs.task_id')
                     ->whereNotNull('time_logs.end_time');
            })
            ->groupBy('users.user_id', 'users.name', 'users.email', 'users.role', 'users.specialty', 'users.created_at', 'users.updated_at', 'users.password', 'users.remember_token', 'users.email_verified_at');
            
        if ($projectId) {
            $query->where('tasks.project_id', $projectId);
        }
        
        return $query->orderByDesc('completed_tasks')
            ->limit($limit)
            ->get()
            ->map(function($user) {
                $user->formatted_time = $this->formatDuration($user->total_time ?? 0);
                return $user;
            });
    }

    /**
     * Get time distribution over days
     */
    private function getTimeDistribution($projectId, $startDate, $endDate)
    {
        $query = TimeLog::selectRaw('DATE(created_at) as date, SUM(duration_seconds) as total_seconds')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('end_time')
            ->groupBy('date')
            ->orderBy('date');
            
        if ($projectId) {
            $query->whereHas('task', function($q) use ($projectId) {
                $q->where('project_id', $projectId);
            });
        }
        
        return $query->get()->map(function($item) {
            return [
                'date' => Carbon::parse($item->date)->format('d M'),
                'hours' => round($item->total_seconds / 3600, 1),
            ];
        });
    }

    /**
     * Format duration
     */
    private function formatDuration($seconds)
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        
        if ($hours > 0) {
            return "{$hours}h {$minutes}m";
        }
        return "{$minutes}m";
    }

    /**
     * Export report as JSON
     */
    public function export(Request $request)
    {
        $projectId = $request->get('project_id');
        $startDate = $request->get('start_date', now()->subMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        
        $data = [
            'stats' => $this->getOverallStats($projectId, $startDate, $endDate),
            'tasks_by_status' => $this->getTasksByStatus($projectId, $startDate, $endDate),
            'tasks_by_priority' => $this->getTasksByPriority($projectId, $startDate, $endDate),
            'top_performers' => $this->getTopPerformers($projectId, $startDate, $endDate, 10),
            'time_distribution' => $this->getTimeDistribution($projectId, $startDate, $endDate),
            'generated_at' => now()->toDateTimeString(),
            'filters' => [
                'project_id' => $projectId,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
        ];
        
        return response()->json($data);
    }

    /**
     * Get monthly report data
     */
    private function getMonthlyData()
    {
        $months = [];
        
        // Get last 12 months
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $startOfMonth = $date->copy()->startOfMonth();
            $endOfMonth = $date->copy()->endOfMonth();
            
            // Get all tasks that have activity (work sessions) in this month
            $taskIdsWithActivity = WorkSession::whereBetween('work_date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')])
                ->distinct()
                ->pluck('task_id');
            
            // Get tasks created in this month OR tasks with work activity in this month
            $totalTasks = Task::where(function($query) use ($startOfMonth, $endOfMonth, $taskIdsWithActivity) {
                $query->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                      ->orWhereIn('task_id', $taskIdsWithActivity);
            })->count();
            
            // Get completed tasks in this month
            $completedTasks = Task::where('status', 'done')
                ->where(function($query) use ($startOfMonth, $endOfMonth) {
                    $query->whereBetween('completed_at', [$startOfMonth, $endOfMonth])
                          ->orWhere(function($q) use ($startOfMonth, $endOfMonth) {
                              $q->whereNull('completed_at')
                                ->whereBetween('updated_at', [$startOfMonth, $endOfMonth]);
                          });
                })
                ->count();
            
            // Get total work hours
            $totalSeconds = WorkSession::whereBetween('work_date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')])
                ->sum('duration_seconds') ?? 0;
            
            $workHours = round($totalSeconds / 3600, 2);
            
            $months[] = [
                'month' => $date->format('M Y'),
                'month_number' => $date->format('Y-m'),
                'total_tasks' => $totalTasks,
                'completed_tasks' => $completedTasks,
                'completion_rate' => $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 1) : 0,
                'work_hours' => $workHours,
            ];
        }
        
        return $months;
    }

    /**
     * Get yearly report data
     */
    private function getYearlyData()
    {
        $years = [];
        $currentYear = Carbon::now()->year;
        
        // Get last 5 years
        for ($i = 4; $i >= 0; $i--) {
            $year = $currentYear - $i;
            $startOfYear = Carbon::create($year, 1, 1)->startOfDay();
            $endOfYear = Carbon::create($year, 12, 31)->endOfDay();
            
            // Get projects created in this year OR have activity in this year
            $totalProjects = Project::where(function($query) use ($startOfYear, $endOfYear) {
                $query->whereBetween('created_at', [$startOfYear, $endOfYear])
                      ->orWhere(function($q) use ($startOfYear, $endOfYear) {
                          $q->where('created_at', '<', $startOfYear)
                            ->where(function($q2) use ($endOfYear) {
                                $q2->whereNull('completed_at')
                                   ->orWhere('completed_at', '>=', $endOfYear);
                            });
                      });
            })->count();
            
            $completedProjects = Project::where('status', 'completed')
                ->where(function($query) use ($startOfYear, $endOfYear) {
                    $query->whereBetween('completed_at', [$startOfYear, $endOfYear])
                          ->orWhere(function($q) use ($startOfYear, $endOfYear) {
                              $q->whereNull('completed_at')
                                ->whereBetween('updated_at', [$startOfYear, $endOfYear]);
                          });
                })
                ->count();
            
            // Get tasks with activity in this year
            $taskIdsWithActivity = WorkSession::whereBetween('work_date', [$startOfYear->format('Y-m-d'), $endOfYear->format('Y-m-d')])
                ->distinct()
                ->pluck('task_id');
            
            $totalTasks = Task::where(function($query) use ($startOfYear, $endOfYear, $taskIdsWithActivity) {
                $query->whereBetween('created_at', [$startOfYear, $endOfYear])
                      ->orWhereIn('task_id', $taskIdsWithActivity);
            })->count();
            
            $completedTasks = Task::where('status', 'done')
                ->where(function($query) use ($startOfYear, $endOfYear) {
                    $query->whereBetween('completed_at', [$startOfYear, $endOfYear])
                          ->orWhere(function($q) use ($startOfYear, $endOfYear) {
                              $q->whereNull('completed_at')
                                ->whereBetween('updated_at', [$startOfYear, $endOfYear]);
                          });
                })
                ->count();
            
            $totalSeconds = WorkSession::whereBetween('work_date', [$startOfYear->format('Y-m-d'), $endOfYear->format('Y-m-d')])
                ->sum('duration_seconds') ?? 0;
            
            $workHours = round($totalSeconds / 3600, 2);
            
            $years[] = [
                'year' => $year,
                'total_projects' => $totalProjects,
                'completed_projects' => $completedProjects,
                'total_tasks' => $totalTasks,
                'completed_tasks' => $completedTasks,
                'completion_rate' => $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 1) : 0,
                'work_hours' => $workHours,
            ];
        }
        
        return $years;
    }

    /**
     * Get per-project report data
     */
    private function getProjectData()
    {
        $projects = Project::with('leader')->get();
        $projectStats = [];
        
        /** @var \App\Models\Project $project */
        foreach ($projects as $project) {
            $totalTasks = Task::where('project_id', $project->project_id)->count();
            $completedTasks = Task::where('project_id', $project->project_id)
                ->where('status', 'done')
                ->count();
            
            $inProgressTasks = Task::where('project_id', $project->project_id)
                ->where('status', 'in_progress')
                ->count();
            
            $overdueTasks = Task::where('project_id', $project->project_id)
                ->where('status', '!=', 'done')
                ->where('deadline', '<', now())
                ->count();
            
            $totalSeconds = WorkSession::whereHas('task', function($q) use ($project) {
                    $q->where('project_id', $project->project_id);
                })
                ->sum('duration_seconds') ?? 0;
            
            $workHours = round($totalSeconds / 3600, 2);
            
            $teamMembers = DB::table('project_members')
                ->where('project_id', $project->project_id)
                ->count();
            
            $projectStats[] = [
                'project_id' => $project->project_id,
                'project_name' => $project->project_name,
                'leader_name' => $project->leader ? $project->leader->full_name : 'N/A',
                'status' => $project->status,
                'total_tasks' => $totalTasks,
                'completed_tasks' => $completedTasks,
                'in_progress_tasks' => $inProgressTasks,
                'overdue_tasks' => $overdueTasks,
                'completion_rate' => $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 1) : 0,
                'work_hours' => $workHours,
                'team_members' => $teamMembers,
                'deadline' => $project->deadline ? Carbon::parse($project->deadline)->format('d M Y') : 'N/A',
                'completed_at' => $project->completed_at ? Carbon::parse($project->completed_at)->format('d M Y') : 'Ongoing',
            ];
        }
        
        return $projectStats;
    }

    /**
     * Generate monthly report CSV
     */
    public function generateMonthly(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'month' => 'required|date_format:Y-m',
        ]);

        $month = $request->input('month');
        $date = Carbon::createFromFormat('Y-m', $month);
        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();

        $filters = [
            'date_from' => $startOfMonth->format('Y-m-d'),
            'date_to' => $endOfMonth->format('Y-m-d'),
        ];

        $csvContent = $this->generateCombinedReport($filters);
        $filename = 'monthly_report_' . $month . '.csv';

        // Log the report
        ReportLog::create([
            'user_id' => Auth::id(),
            'report_type' => 'monthly',
            'filters' => ['month' => $month],
            'file_path' => 'reports/' . $filename,
            'generated_at' => now(),
        ]);

        return response()->streamDownload(function() use ($csvContent) {
            echo $csvContent;
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Generate yearly report CSV
     */
    public function generateYearly(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'year' => 'required|integer|min:2020|max:' . (date('Y') + 1),
        ]);

        $year = $request->input('year');
        $startOfYear = Carbon::create($year, 1, 1)->startOfDay();
        $endOfYear = Carbon::create($year, 12, 31)->endOfDay();

        $filters = [
            'date_from' => $startOfYear->format('Y-m-d'),
            'date_to' => $endOfYear->format('Y-m-d'),
        ];

        $csvContent = $this->generateCombinedReport($filters);
        $filename = 'yearly_report_' . $year . '.csv';

        // Log the report
        ReportLog::create([
            'user_id' => Auth::id(),
            'report_type' => 'yearly',
            'filters' => ['year' => $year],
            'file_path' => 'reports/' . $filename,
            'generated_at' => now(),
        ]);

        return response()->streamDownload(function() use ($csvContent) {
            echo $csvContent;
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Generate per-project report CSV
     */
    public function generateProject(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'project_id' => 'required|exists:projects,project_id',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);

        $filters = [
            'project_id' => $request->input('project_id'),
            'date_from' => $request->input('date_from', now()->subYear()->format('Y-m-d')),
            'date_to' => $request->input('date_to', now()->format('Y-m-d')),
        ];

        $csvContent = $this->generateCombinedReport($filters);
        $project = Project::find($filters['project_id']);
        $filename = 'project_report_' . $project->project_name . '_' . Carbon::now()->format('Y-m-d') . '.csv';

        // Log the report
        ReportLog::create([
            'user_id' => Auth::id(),
            'report_type' => 'project',
            'filters' => $filters,
            'file_path' => 'reports/' . $filename,
            'generated_at' => now(),
        ]);

        return response()->streamDownload(function() use ($csvContent) {
            echo $csvContent;
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
