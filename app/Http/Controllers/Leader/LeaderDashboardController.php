<?php

namespace App\Http\Controllers\Leader;

use App\Http\Controllers\Controller;
use App\Models\Card;
use App\Models\CardAssignment;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\User;
use App\Models\Board;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LeaderDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role.leader');
    }

    /**
     * Show the leader dashboard
     */
    public function index()
    {
        return view('leader.dashboard');
    }

    /**
     * Get dashboard data for leader
     */
    public function getDashboardData()
    {
        $leaderId = Auth::id();
        
        // Get projects where user is a leader (assigned via leader_id or project_manager role)
        $projects = Project::where(function($query) use ($leaderId) {
            $query->where('leader_id', $leaderId)
                  ->orWhereHas('members', function($q) use ($leaderId) {
                      $q->where('user_id', $leaderId)
                        ->where('role', 'project_manager');
                  });
        })->with(['boards.cards.assignments.user', 'leader'])->get();

        // Calculate statistics
        $stats = $this->calculateStats($projects);
        
        // Get tasks from leader's projects
        $tasks = $this->getLeaderTasks($projects);
        
        // Get recent tasks (last 5)
        $recentTasks = $this->getRecentTasks($projects);
        
        // Get project progress
        $projectProgress = $this->getProjectProgress($projects);
        
        // Get team performance
        $teamPerformance = $this->getTeamPerformance($projects);

        // Get completed projects with details
        $completedProjects = $this->getCompletedProjects($projects);

        return response()->json([
            'stats' => $stats,
            'projects' => $projects->map(function($project) {
                return [
                    'project_id' => $project->project_id,
                    'project_name' => $project->project_name,
                    'status' => $project->status
                ];
            }),
            'tasks' => $tasks,
            'recent_tasks' => $recentTasks,
            'project_progress' => $projectProgress,
            'team_performance' => $teamPerformance,
            'completed_projects' => $completedProjects
        ]);
    }

    /**
     * Get team members for a specific project
     */
    public function getProjectTeamMembers($projectId)
    {
        // Verify leader has access to this project
        $hasAccess = ProjectMember::where('user_id', Auth::id())
            ->where('project_id', $projectId)
            ->where('role', 'project_manager')
            ->exists();

        if (!$hasAccess && Auth::user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $teamMembers = User::whereHas('projectMembers', function($query) use ($projectId) {
            $query->where('project_id', $projectId)
                  ->whereIn('role', ['developer', 'designer']);
        })->with(['projectMembers' => function($query) use ($projectId) {
            $query->where('project_id', $projectId);
        }])->get();

        return response()->json([
            'team_members' => $teamMembers->map(function($user) {
                return [
                    'user_id' => $user->user_id,
                    'full_name' => $user->full_name,
                    'email' => $user->email,
                    'role' => $user->projectMembers->first()->role ?? 'member'
                ];
            })
        ]);
    }

    /**
     * Quick assign task
     */
    public function quickAssignTask(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,project_id',
            'task_title' => 'required|string|max:100',
            'assignee' => 'required|exists:users,user_id'
        ]);

        // Verify leader has access to this project
        $hasAccess = ProjectMember::where('user_id', Auth::id())
            ->where('project_id', $request->project_id)
            ->where('role', 'project_manager')
            ->exists();

        if (!$hasAccess && Auth::user()->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // Verify assignee is a project member
        $isMember = ProjectMember::where('user_id', $request->assignee)
            ->where('project_id', $request->project_id)
            ->whereIn('role', ['developer', 'designer'])
            ->exists();

        if (!$isMember) {
            return response()->json(['success' => false, 'message' => 'User is not a project member'], 422);
        }

        // Get default board for the project
        $board = Board::where('project_id', $request->project_id)->first();
        
        if (!$board) {
            return response()->json(['success' => false, 'message' => 'No board found for this project'], 422);
        }

        DB::beginTransaction();
        try {
            // Create the task
            $task = Card::create([
                'board_id' => $board->board_id,
                'card_title' => $request->task_title,
                'description' => 'Quick assigned task',
                'created_by' => Auth::id(),
                'priority' => 'medium',
                'status' => 'todo',
            ]);

            // Assign user to the task
            CardAssignment::create([
                'card_id' => $task->card_id,
                'user_id' => $request->assignee,
                'assigned_at' => now(),
                'assignment_status' => 'assigned'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Task assigned successfully!',
                'task' => $task
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Quick task assignment failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to assign task'], 500);
        }
    }

    /**
     * Update task priority
     */
    public function updateTaskPriority(Request $request, $taskId)
    {
        $request->validate([
            'priority' => 'required|in:low,medium,high,critical'
        ]);

        // Get task and verify leader has access
        $task = Card::with('board.project.members')->findOrFail($taskId);
        
        $hasAccess = $task->board->project->members
            ->where('user_id', Auth::id())
            ->where('role', 'project_manager')
            ->isNotEmpty();

        if (!$hasAccess && Auth::user()->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $task->update(['priority' => $request->priority]);

        return response()->json([
            'success' => true,
            'message' => 'Task priority updated successfully!'
        ]);
    }

    /**
     * Update task status
     */
    public function updateTaskStatus(Request $request, $taskId)
    {
        $request->validate([
            'status' => 'required|in:todo,in_progress,review,done'
        ]);

        // Get task and verify leader has access
        $task = Card::with('board.project.members')->findOrFail($taskId);
        
        $hasAccess = $task->board->project->members
            ->where('user_id', Auth::id())
            ->where('role', 'project_manager')
            ->isNotEmpty();

        if (!$hasAccess && Auth::user()->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $task->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Task status updated successfully!'
        ]);
    }

    /**
     * Calculate dashboard statistics
     */
    private function calculateStats($projects)
    {
        $totalTasks = 0;
        $completedTasks = 0;
        $pendingTasks = 0;
        
        // Completed projects statistics
        $completedProjects = $projects->where('status', 'completed');
        $totalCompletedProjects = $completedProjects->count();
        $completedOnTime = $completedProjects->where('is_overdue', false)->count();
        $completedLate = $completedProjects->where('is_overdue', true)->count();

        foreach ($projects as $project) {
            foreach ($project->boards as $board) {
                $totalTasks += $board->cards->count();
                $completedTasks += $board->cards->where('status', 'done')->count();
                $pendingTasks += $board->cards->whereIn('status', ['todo', 'in_progress'])->count();
            }
        }

        return [
            'projects' => $projects->count(),
            'total_tasks' => $totalTasks,
            'completed_tasks' => $completedTasks,
            'pending_tasks' => $pendingTasks,
            'completed_projects' => $totalCompletedProjects,
            'completed_on_time' => $completedOnTime,
            'completed_late' => $completedLate
        ];
    }

    /**
     * Get tasks from leader's projects
     */
    private function getLeaderTasks($projects)
    {
        $tasks = [];

        foreach ($projects as $project) {
            foreach ($project->boards as $board) {
                foreach ($board->cards as $card) {
                    $assignees = $card->assignments->map(function($assignment) {
                        return $assignment->user->full_name;
                    })->implode(', ');

                    $tasks[] = [
                        'card_id' => $card->card_id,
                        'card_title' => $card->card_title,
                        'priority' => $card->priority,
                        'status' => $card->status,
                        'project_name' => $project->project_name,
                        'assignee_name' => $assignees ?: 'Unassigned',
                        'due_date' => $card->due_date,
                        'created_at' => $card->created_at->diffForHumans()
                    ];
                }
            }
        }

        return $tasks;
    }

    /**
     * Get recent tasks
     */
    private function getRecentTasks($projects)
    {
        $tasks = [];

        foreach ($projects as $project) {
            foreach ($project->boards as $board) {
                foreach ($board->cards as $card) {
                    $assignees = $card->assignments->map(function($assignment) {
                        return $assignment->user->full_name;
                    })->implode(', ');

                    $tasks[] = [
                        'card_id' => $card->card_id,
                        'card_title' => $card->card_title,
                        'assignee_name' => $assignees ?: 'Unassigned',
                        'created_at' => $card->created_at->diffForHumans()
                    ];
                }
            }
        }

        // Sort by creation date and take last 5
        usort($tasks, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        return array_slice($tasks, 0, 5);
    }

    /**
     * Get project progress
     */
    private function getProjectProgress($projects)
    {
        $progress = [];

        foreach ($projects as $project) {
            $totalTasks = 0;
            $completedTasks = 0;

            foreach ($project->boards as $board) {
                $totalTasks += $board->cards->count();
                $completedTasks += $board->cards->where('status', 'done')->count();
            }

            $completionPercentage = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 2) : 0;

            $progress[] = [
                'project_id' => $project->project_id,
                'project_name' => $project->project_name,
                'total_tasks' => $totalTasks,
                'completed_tasks' => $completedTasks,
                'completion_percentage' => $completionPercentage,
                'status' => $project->status
            ];
        }

        return $progress;
    }

    /**
     * Get team performance
     */
    private function getTeamPerformance($projects)
    {
        $performance = [];

        foreach ($projects as $project) {
            $members = ProjectMember::where('project_id', $project->project_id)
                ->whereIn('role', ['developer', 'designer'])
                ->with('user')
                ->get();

            foreach ($members as $member) {
                $totalTasks = 0;
                $completedTasks = 0;

                foreach ($project->boards as $board) {
                    foreach ($board->cards as $card) {
                        $isAssigned = $card->assignments->where('user_id', $member->user_id)->isNotEmpty();
                        if ($isAssigned) {
                            $totalTasks++;
                            if ($card->status === 'done') {
                                $completedTasks++;
                            }
                        }
                    }
                }

                $existingIndex = array_search($member->user_id, array_column($performance, 'user_id'));
                
                if ($existingIndex !== false) {
                    $performance[$existingIndex]['total_tasks'] += $totalTasks;
                    $performance[$existingIndex]['completed_tasks'] += $completedTasks;
                } else {
                    $performance[] = [
                        'user_id' => $member->user_id,
                        'full_name' => $member->user->full_name,
                        'role' => $member->role,
                        'total_tasks' => $totalTasks,
                        'completed_tasks' => $completedTasks
                    ];
                }
            }
        }

        return $performance;
    }

    /**
     * Get completed projects with details
     */
    private function getCompletedProjects($projects)
    {
        return $projects->where('status', 'completed')
            ->sortByDesc('completed_at')
            ->take(10)
            ->map(function($project) {
                return [
                    'project_id' => $project->project_id,
                    'project_name' => $project->project_name,
                    'deadline' => $project->deadline ? $project->deadline->format('d M Y') : '-',
                    'completed_at' => $project->completed_at ? $project->completed_at->format('d M Y') : '-',
                    'is_overdue' => $project->is_overdue,
                    'delay_days' => $project->delay_days ?? 0,
                    'delay_message' => $project->getDelayMessage(),
                    'badge_color' => $project->getDelayBadgeColor(),
                    'completion_notes' => $project->completion_notes
                ];
            })->values();
    }
}