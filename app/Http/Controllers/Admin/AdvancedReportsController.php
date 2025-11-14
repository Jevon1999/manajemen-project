<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use App\Models\Project;
use App\Models\User;
use App\Models\Card;
use App\Models\TimeLog;
use App\Models\ProjectMember;

class AdvancedReportsController extends Controller
{
    /**
     * Show advanced reports dashboard
     */
    public function index()
    {
        $reportCategories = [
            'project_analytics' => [
                'name' => 'Project Analytics',
                'description' => 'Comprehensive project performance and progress reports',
                'reports' => [
                    'project_overview' => 'Project Overview Report',
                    'project_progress' => 'Project Progress Analysis',
                    'project_timeline' => 'Project Timeline Report',
                    'project_budget' => 'Project Budget Analysis'
                ]
            ],
            'team_performance' => [
                'name' => 'Team Performance',
                'description' => 'Team productivity and performance analytics',
                'reports' => [
                    'team_productivity' => 'Team Productivity Report',
                    'individual_performance' => 'Individual Performance Analysis',
                    'workload_distribution' => 'Workload Distribution Report',
                    'time_tracking' => 'Time Tracking Analysis'
                ]
            ],
            'task_analytics' => [
                'name' => 'Task Analytics',
                'description' => 'Task completion rates and efficiency metrics',
                'reports' => [
                    'task_completion' => 'Task Completion Report',
                    'task_efficiency' => 'Task Efficiency Analysis',
                    'overdue_tasks' => 'Overdue Tasks Report',
                    'task_priority' => 'Task Priority Analysis'
                ]
            ],
            'system_reports' => [
                'name' => 'System Reports',
                'description' => 'System usage and performance reports',
                'reports' => [
                    'user_activity' => 'User Activity Report',
                    'system_usage' => 'System Usage Statistics',
                    'error_logs' => 'Error Log Analysis',
                    'performance_metrics' => 'Performance Metrics Report'
                ]
            ]
        ];

        return view('admin.reports.advanced.index', compact('reportCategories'));
    }

    /**
     * Generate specific report
     */
    public function generateReport(Request $request)
    {
        try {
            $reportType = $request->input('report_type');
            $dateRange = $request->input('date_range', '30_days');
            $filters = $request->input('filters', []);

            $dateRanges = $this->getDateRange($dateRange);
            
            switch ($reportType) {
                case 'project_overview':
                    $data = $this->generateProjectOverviewReport($dateRanges, $filters);
                    break;
                case 'project_progress':
                    $data = $this->generateProjectProgressReport($dateRanges, $filters);
                    break;
                case 'team_productivity':
                    $data = $this->generateTeamProductivityReport($dateRanges, $filters);
                    break;
                case 'individual_performance':
                    $data = $this->generateIndividualPerformanceReport($dateRanges, $filters);
                    break;
                case 'task_completion':
                    $data = $this->generateTaskCompletionReport($dateRanges, $filters);
                    break;
                case 'user_activity':
                    $data = $this->generateUserActivityReport($dateRanges, $filters);
                    break;
                default:
                    throw new \Exception('Unknown report type');
            }

            return response()->json([
                'success' => true,
                'data' => $data,
                'report_type' => $reportType,
                'date_range' => $dateRange,
                'generated_at' => now()->format('Y-m-d H:i:s')
            ]);

        } catch (\Exception $e) {
            Log::error('Report generation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export report data
     */
    public function exportReport(Request $request)
    {
        try {
            $reportType = $request->input('report_type');
            $format = $request->input('format', 'excel'); // excel, csv, pdf
            $dateRange = $request->input('date_range', '30_days');
            $filters = $request->input('filters', []);

            // Generate report data
            $reportRequest = new Request([
                'report_type' => $reportType,
                'date_range' => $dateRange,
                'filters' => $filters
            ]);
            
            $reportResponse = $this->generateReport($reportRequest);
            $reportData = json_decode($reportResponse->getContent(), true);

            if (!$reportData['success']) {
                throw new \Exception('Failed to generate report data');
            }

            $data = $reportData['data'];
            $filename = $this->generateExportFilename($reportType, $format);

            switch ($format) {
                case 'csv':
                    return $this->exportToCsv($data, $filename);
                case 'pdf':
                    return $this->exportToPdf($data, $filename, $reportType);
                case 'excel':
                default:
                    return $this->exportToExcel($data, $filename);
            }

        } catch (\Exception $e) {
            Log::error('Report export failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to export report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get report templates
     */
    public function getReportTemplates()
    {
        $templates = [
            'weekly_summary' => [
                'name' => 'Weekly Summary Report',
                'description' => 'Weekly overview of projects and team performance',
                'schedule' => 'weekly',
                'reports' => ['project_overview', 'team_productivity']
            ],
            'monthly_analytics' => [
                'name' => 'Monthly Analytics Report',
                'description' => 'Comprehensive monthly analytics and insights',
                'schedule' => 'monthly',
                'reports' => ['project_progress', 'individual_performance', 'task_completion']
            ],
            'quarterly_review' => [
                'name' => 'Quarterly Review Report',
                'description' => 'Quarterly performance review and strategic insights',
                'schedule' => 'quarterly',
                'reports' => ['project_overview', 'team_productivity', 'system_usage']
            ]
        ];

        return response()->json([
            'success' => true,
            'templates' => $templates
        ]);
    }

    // Private report generation methods

    private function generateProjectOverviewReport($dateRange, $filters)
    {
        $projects = Project::query()
            ->when(!empty($filters['project_ids']), function($q) use ($filters) {
                $q->whereIn('project_id', $filters['project_ids']);
            })
            ->when(!empty($filters['status']), function($q) use ($filters) {
                $q->where('status', $filters['status']);
            })
            ->withCount(['cards', 'members'])
            ->get();

        $summary = [
            'total_projects' => $projects->count(),
            'active_projects' => $projects->where('status', 'active')->count(),
            'completed_projects' => $projects->where('status', 'completed')->count(),
            'on_hold_projects' => $projects->where('status', 'on_hold')->count(),
            'total_tasks' => $projects->sum('cards_count'),
            'total_members' => $projects->sum('members_count'),
            'average_completion_rate' => $this->calculateAverageCompletionRate($projects)
        ];

        $projectDetails = $projects->map(function($project) {
            $completionRate = $this->calculateProjectCompletionRate($project->project_id);
            return [
                'project_id' => $project->project_id,
                'name' => $project->name,
                'status' => $project->status,
                'start_date' => $project->start_date,
                'end_date' => $project->end_date,
                'completion_rate' => $completionRate,
                'total_tasks' => $project->cards_count,
                'completed_tasks' => $this->getCompletedTasksCount($project->project_id),
                'team_size' => $project->members_count,
                'budget_utilization' => $this->calculateBudgetUtilization($project->project_id)
            ];
        });

        return [
            'summary' => $summary,
            'projects' => $projectDetails,
            'charts' => [
                'status_distribution' => $this->getProjectStatusDistribution($projects),
                'completion_trends' => $this->getProjectCompletionTrends($dateRange)
            ]
        ];
    }

    private function generateProjectProgressReport($dateRange, $filters)
    {
        $projects = Project::query()
            ->when(!empty($filters['project_ids']), function($q) use ($filters) {
                $q->whereIn('project_id', $filters['project_ids']);
            })
            ->get();

        $progressData = $projects->map(function($project) use ($dateRange) {
            $milestones = $this->getProjectMilestones($project->project_id, $dateRange);
            $velocity = $this->calculateProjectVelocity($project->project_id, $dateRange);
            
            return [
                'project_id' => $project->project_id,
                'name' => $project->name,
                'progress_percentage' => $this->calculateProjectCompletionRate($project->project_id),
                'milestones' => $milestones,
                'velocity' => $velocity,
                'estimated_completion' => $this->estimateCompletionDate($project->project_id),
                'risk_factors' => $this->identifyRiskFactors($project->project_id),
                'timeline_variance' => $this->calculateTimelineVariance($project->project_id)
            ];
        });

        return [
            'projects' => $progressData,
            'summary' => [
                'average_progress' => $progressData->avg('progress_percentage'),
                'projects_on_track' => $progressData->where('timeline_variance', '<=', 0)->count(),
                'projects_at_risk' => $progressData->where('timeline_variance', '>', 10)->count()
            ],
            'charts' => [
                'progress_trends' => $this->getProgressTrends($dateRange),
                'velocity_comparison' => $this->getVelocityComparison($projects, $dateRange)
            ]
        ];
    }

    private function generateTeamProductivityReport($dateRange, $filters)
    {
        $teams = DB::table('project_members')
            ->join('projects', 'project_members.project_id', '=', 'projects.project_id')
            ->join('users', 'project_members.user_id', '=', 'users.user_id')
            ->when(!empty($filters['project_ids']), function($q) use ($filters) {
                $q->whereIn('projects.project_id', $filters['project_ids']);
            })
            ->when(!empty($filters['team_roles']), function($q) use ($filters) {
                $q->whereIn('project_members.role', $filters['team_roles']);
            })
            ->select([
                'projects.project_id',
                'projects.name as project_name',
                'users.user_id',
                'users.full_name',
                'project_members.role'
            ])
            ->get()
            ->groupBy('project_id');

        $teamMetrics = $teams->map(function($teamMembers, $projectId) use ($dateRange) {
            $productivity = $teamMembers->map(function($member) use ($dateRange) {
                return [
                    'user_id' => $member->user_id,
                    'name' => $member->full_name,
                    'role' => $member->role,
                    'tasks_completed' => $this->getTasksCompletedByUser($member->user_id, $dateRange),
                    'hours_logged' => $this->getHoursLoggedByUser($member->user_id, $dateRange),
                    'productivity_score' => $this->calculateProductivityScore($member->user_id, $dateRange),
                    'collaboration_score' => $this->calculateCollaborationScore($member->user_id, $dateRange)
                ];
            });

            return [
                'project_id' => $projectId,
                'project_name' => $teamMembers->first()->project_name,
                'team_size' => $teamMembers->count(),
                'total_productivity' => $productivity->sum('productivity_score'),
                'average_productivity' => $productivity->avg('productivity_score'),
                'members' => $productivity,
                'team_efficiency' => $this->calculateTeamEfficiency($projectId, $dateRange)
            ];
        });

        return [
            'teams' => $teamMetrics,
            'summary' => [
                'total_teams' => $teamMetrics->count(),
                'average_team_productivity' => $teamMetrics->avg('average_productivity'),
                'most_productive_team' => $teamMetrics->sortByDesc('average_productivity')->first(),
                'total_hours_logged' => $teamMetrics->sum(function($team) {
                    return $team['members']->sum('hours_logged');
                })
            ],
            'charts' => [
                'productivity_trends' => $this->getProductivityTrends($dateRange),
                'team_comparison' => $this->getTeamProductivityComparison($teamMetrics)
            ]
        ];
    }

    private function generateIndividualPerformanceReport($dateRange, $filters)
    {
        $users = User::query()
            ->when(!empty($filters['user_ids']), function($q) use ($filters) {
                $q->whereIn('user_id', $filters['user_ids']);
            })
            ->when(!empty($filters['roles']), function($q) use ($filters) {
                $q->whereIn('role', $filters['roles']);
            })
            ->where('status', 'active')
            ->get();

        $performanceData = $users->map(function($user) use ($dateRange) {
            $projectRoles = $this->getUserProjectRoles($user->user_id);
            $taskMetrics = $this->getUserTaskMetrics($user->user_id, $dateRange);
            $timeMetrics = $this->getUserTimeMetrics($user->user_id, $dateRange);
            
            return [
                'user_id' => $user->user_id,
                'name' => $user->full_name,
                'email' => $user->email,
                'role' => $user->role,
                'project_roles' => $projectRoles,
                'tasks_assigned' => $taskMetrics['assigned'],
                'tasks_completed' => $taskMetrics['completed'],
                'tasks_in_progress' => $taskMetrics['in_progress'],
                'completion_rate' => $taskMetrics['completion_rate'],
                'average_task_duration' => $taskMetrics['average_duration'],
                'hours_logged' => $timeMetrics['total_hours'],
                'productivity_score' => $this->calculateProductivityScore($user->user_id, $dateRange),
                'quality_score' => $this->calculateQualityScore($user->user_id, $dateRange),
                'collaboration_score' => $this->calculateCollaborationScore($user->user_id, $dateRange),
                'performance_trends' => $this->getUserPerformanceTrends($user->user_id, $dateRange)
            ];
        });

        return [
            'users' => $performanceData,
            'summary' => [
                'total_users' => $performanceData->count(),
                'average_completion_rate' => $performanceData->avg('completion_rate'),
                'average_productivity' => $performanceData->avg('productivity_score'),
                'top_performers' => $performanceData->sortByDesc('productivity_score')->take(5),
                'improvement_needed' => $performanceData->where('productivity_score', '<', 70)
            ],
            'charts' => [
                'performance_distribution' => $this->getPerformanceDistribution($performanceData),
                'skill_matrix' => $this->getSkillMatrix($users)
            ]
        ];
    }

    private function generateTaskCompletionReport($dateRange, $filters)
    {
        $tasks = Card::query()
            ->when(!empty($filters['project_ids']), function($q) use ($filters) {
                $q->whereHas('board', function($subQ) use ($filters) {
                    $subQ->whereIn('project_id', $filters['project_ids']);
                });
            })
            ->when(!empty($filters['priorities']), function($q) use ($filters) {
                $q->whereIn('priority', $filters['priorities']);
            })
            ->when(!empty($filters['statuses']), function($q) use ($filters) {
                $q->whereIn('status', $filters['statuses']);
            })
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->with(['board.project', 'assignments.user'])
            ->get();

        $summary = [
            'total_tasks' => $tasks->count(),
            'completed_tasks' => $tasks->where('status', 'done')->count(),
            'in_progress_tasks' => $tasks->where('status', 'in_progress')->count(),
            'pending_tasks' => $tasks->where('status', 'todo')->count(),
            'overdue_tasks' => $tasks->where('due_date', '<', now())->where('status', '!=', 'done')->count(),
            'completion_rate' => $tasks->count() > 0 ? ($tasks->where('status', 'done')->count() / $tasks->count()) * 100 : 0,
            'average_completion_time' => $this->calculateAverageCompletionTime($tasks->where('status', 'done'))
        ];

        $priorityBreakdown = [
            'high' => $tasks->where('priority', 'high')->groupBy('status')->map->count(),
            'medium' => $tasks->where('priority', 'medium')->groupBy('status')->map->count(),
            'low' => $tasks->where('priority', 'low')->groupBy('status')->map->count()
        ];

        return [
            'summary' => $summary,
            'priority_breakdown' => $priorityBreakdown,
            'task_details' => $tasks->map(function($task) {
                return [
                    'card_id' => $task->card_id,
                    'title' => $task->title,
                    'status' => $task->status,
                    'priority' => $task->priority,
                    'project_name' => $task->board->project->name,
                    'assigned_to' => $task->assignments->pluck('user.full_name'),
                    'created_at' => $task->created_at,
                    'completed_at' => $task->updated_at,
                    'duration' => $this->calculateTaskDuration($task)
                ];
            }),
            'charts' => [
                'completion_trends' => $this->getTaskCompletionTrends($dateRange),
                'priority_distribution' => $this->getTaskPriorityDistribution($tasks)
            ]
        ];
    }

    private function generateUserActivityReport($dateRange, $filters)
    {
        // This would integrate with activity logging system
        // For now, return sample data structure
        return [
            'summary' => [
                'total_active_users' => User::where('status', 'active')->count(),
                'login_sessions' => 0, // Would come from session tracking
                'page_views' => 0, // Would come from activity logs
                'feature_usage' => []
            ],
            'activity_details' => [],
            'charts' => [
                'daily_activity' => [],
                'feature_usage' => []
            ]
        ];
    }

    // Helper methods

    private function getDateRange($range)
    {
        $end = Carbon::now();
        
        switch ($range) {
            case '7_days':
                $start = $end->copy()->subDays(7);
                break;
            case '30_days':
                $start = $end->copy()->subDays(30);
                break;
            case '90_days':
                $start = $end->copy()->subDays(90);
                break;
            case '1_year':
                $start = $end->copy()->subYear();
                break;
            default:
                $start = $end->copy()->subDays(30);
        }

        return ['start' => $start, 'end' => $end];
    }

    private function calculateProjectCompletionRate($projectId)
    {
        $totalTasks = Card::whereHas('board', function($q) use ($projectId) {
            $q->where('project_id', $projectId);
        })->count();

        if ($totalTasks === 0) return 0;

        $completedTasks = Card::whereHas('board', function($q) use ($projectId) {
            $q->where('project_id', $projectId);
        })->where('status', 'done')->count();

        return round(($completedTasks / $totalTasks) * 100, 2);
    }

    private function calculateAverageCompletionRate($projects)
    {
        if ($projects->isEmpty()) return 0;

        $totalRate = 0;
        foreach ($projects as $project) {
            $totalRate += $this->calculateProjectCompletionRate($project->project_id);
        }

        return round($totalRate / $projects->count(), 2);
    }

    private function getCompletedTasksCount($projectId)
    {
        return Card::whereHas('board', function($q) use ($projectId) {
            $q->where('project_id', $projectId);
        })->where('status', 'done')->count();
    }

    private function calculateBudgetUtilization($projectId)
    {
        // Placeholder - would integrate with budget tracking
        return rand(60, 95);
    }

    private function getProjectStatusDistribution($projects)
    {
        return [
            'active' => $projects->where('status', 'active')->count(),
            'completed' => $projects->where('status', 'completed')->count(),
            'on_hold' => $projects->where('status', 'on_hold')->count(),
            'planning' => $projects->where('status', 'planning')->count()
        ];
    }

    private function getProjectCompletionTrends($dateRange)
    {
        // Placeholder for chart data
        return [];
    }

    private function calculateProductivityScore($userId, $dateRange)
    {
        $tasksCompleted = $this->getTasksCompletedByUser($userId, $dateRange);
        $hoursLogged = $this->getHoursLoggedByUser($userId, $dateRange);
        
        // Simple productivity calculation
        return $hoursLogged > 0 ? min(100, ($tasksCompleted * 10) + ($hoursLogged * 2)) : 0;
    }

    private function calculateCollaborationScore($userId, $dateRange)
    {
        // Placeholder - would calculate based on comments, shared tasks, etc.
        return rand(60, 95);
    }

    private function getTasksCompletedByUser($userId, $dateRange)
    {
        return Card::whereHas('assignments', function($q) use ($userId) {
            $q->where('user_id', $userId);
        })
        ->where('status', 'done')
        ->whereBetween('updated_at', [$dateRange['start'], $dateRange['end']])
        ->count();
    }

    private function getHoursLoggedByUser($userId, $dateRange)
    {
        return TimeLog::where('user_id', $userId)
            ->whereBetween('logged_date', [$dateRange['start'], $dateRange['end']])
            ->sum('hours_worked') ?? 0;
    }

    private function getUserTaskMetrics($userId, $dateRange)
    {
        $assigned = Card::whereHas('assignments', function($q) use ($userId) {
            $q->where('user_id', $userId);
        })->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])->count();

        $completed = Card::whereHas('assignments', function($q) use ($userId) {
            $q->where('user_id', $userId);
        })->where('status', 'done')->whereBetween('updated_at', [$dateRange['start'], $dateRange['end']])->count();

        $inProgress = Card::whereHas('assignments', function($q) use ($userId) {
            $q->where('user_id', $userId);
        })->where('status', 'in_progress')->count();

        return [
            'assigned' => $assigned,
            'completed' => $completed,
            'in_progress' => $inProgress,
            'completion_rate' => $assigned > 0 ? round(($completed / $assigned) * 100, 2) : 0,
            'average_duration' => 0 // Placeholder
        ];
    }

    private function getUserTimeMetrics($userId, $dateRange)
    {
        return [
            'total_hours' => $this->getHoursLoggedByUser($userId, $dateRange)
        ];
    }

    private function getUserProjectRoles($userId)
    {
        return DB::table('project_members')
            ->join('projects', 'project_members.project_id', '=', 'projects.project_id')
            ->where('project_members.user_id', $userId)
            ->select('projects.name', 'project_members.role')
            ->get()
            ->toArray();
    }

    private function generateExportFilename($reportType, $format)
    {
        $timestamp = now()->format('Y-m-d_H-i-s');
        return "report_{$reportType}_{$timestamp}.{$format}";
    }

    private function exportToCsv($data, $filename)
    {
        // Placeholder for CSV export
        return response()->json(['message' => 'CSV export functionality coming soon']);
    }

    private function exportToExcel($data, $filename)
    {
        // Placeholder for Excel export
        return response()->json(['message' => 'Excel export functionality coming soon']);
    }

    private function exportToPdf($data, $filename, $reportType)
    {
        // Placeholder for PDF export
        return response()->json(['message' => 'PDF export functionality coming soon']);
    }

    // Additional placeholder methods for complex calculations
    private function getProjectMilestones($projectId, $dateRange) { return []; }
    private function calculateProjectVelocity($projectId, $dateRange) { return 0; }
    private function estimateCompletionDate($projectId) { return null; }
    private function identifyRiskFactors($projectId) { return []; }
    private function calculateTimelineVariance($projectId) { return 0; }
    private function getProgressTrends($dateRange) { return []; }
    private function getVelocityComparison($projects, $dateRange) { return []; }
    private function calculateTeamEfficiency($projectId, $dateRange) { return 0; }
    private function getProductivityTrends($dateRange) { return []; }
    private function getTeamProductivityComparison($teamMetrics) { return []; }
    private function calculateQualityScore($userId, $dateRange) { return rand(70, 95); }
    private function getUserPerformanceTrends($userId, $dateRange) { return []; }
    private function getPerformanceDistribution($performanceData) { return []; }
    private function getSkillMatrix($users) { return []; }
    private function calculateAverageCompletionTime($completedTasks) { return 0; }
    private function calculateTaskDuration($task) { return 0; }
    private function getTaskCompletionTrends($dateRange) { return []; }
    private function getTaskPriorityDistribution($tasks) { return []; }
}