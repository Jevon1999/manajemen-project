<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\User;
use App\Models\Board;
use App\Models\Card;
use App\Models\TimeLog;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class ProjectAdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role.admin']);
    }

    /**
     * Display the project administration dashboard
     */
    public function index()
    {
        $statistics = [
            'total_projects' => Project::count(),
            'active_projects' => Project::where('status', 'active')->count(),
            'completed_projects' => Project::where('status', 'completed')->count(),
            'on_hold_projects' => Project::where('status', 'on-hold')->count(),
        ];

        return view('admin.project-admin.index', compact('statistics'));
    }

    /**
     * Create/Delete Projects Management
     */
    public function manageProjects()
    {
        $projects = Project::with(['creator', 'members'])
            ->withCount(['members', 'boards', 'cards' => function($query) {
                $query->join('boards', 'cards.board_id', '=', 'boards.board_id');
            }])
            ->orderBy('created_at', 'desc')
            ->get();

        $statistics = [
            'total_projects' => $projects->count(),
            'active_projects' => $projects->where('status', 'active')->count(),
            'completed_projects' => $projects->where('status', 'completed')->count(),
            'planning_projects' => $projects->where('status', 'planning')->count(),
        ];

        return view('admin.project-admin.manage-projects', compact('projects', 'statistics'));
    }

    /**
     * Team Members Management
     */
    public function manageTeamMembers()
    {
        $projects = Project::with([
            'members.user',
            'creator'
        ])->get();

        $users = User::where('status', 'active')
            ->whereIn('role', ['user', 'leader'])
            ->get();

        $memberStatistics = DB::table('project_members')
            ->join('users', 'project_members.user_id', '=', 'users.user_id')
            ->join('projects', 'project_members.project_id', '=', 'projects.project_id')
            ->select(
                'users.user_id',
                'users.full_name',
                'users.email',
                'users.role',
                DB::raw('COUNT(DISTINCT project_members.project_id) as project_count'),
                DB::raw('GROUP_CONCAT(DISTINCT project_members.role) as project_roles')
            )
            ->groupBy('users.user_id', 'users.full_name', 'users.email', 'users.role')
            ->get();

        return view('admin.project-admin.manage-team-members', compact('projects', 'users', 'memberStatistics'));
    }

    /**
     * Data Access Control Management
     */
    public function manageDataAccess()
    {
        $projects = Project::with(['members.user', 'creator'])
            ->get();

        $accessLogs = DB::table('time_logs as tl')
            ->join('users as u', 'tl.user_id', '=', 'u.user_id')
            ->join('cards as c', 'tl.card_id', '=', 'c.card_id')
            ->join('boards as b', 'c.board_id', '=', 'b.board_id')
            ->join('projects as p', 'b.project_id', '=', 'p.project_id')
            ->select(
                'u.full_name',
                'u.email',
                'p.project_name',
                'c.title as card_title',
                'tl.start_time',
                'tl.end_time',
                DB::raw('TIMESTAMPDIFF(SECOND, tl.start_time, tl.end_time) as duration_seconds')
            )
            ->orderBy('tl.start_time', 'desc')
            ->limit(100)
            ->get();

        return view('admin.project-admin.manage-data-access', compact('projects', 'accessLogs'));
    }

    /**
     * Task Management for all projects
     */
    public function manageTasks()
    {
        return view('admin.project-admin.manage-tasks');
    }

    /**
     * Create new project (API endpoint)
     */
    public function createProject(Request $request)
    {
        $request->validate([
            'project_name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'priority' => 'nullable|in:low,medium,high,critical',
            'category' => 'nullable|string',
            'budget' => 'nullable|numeric|min:0',
            'leader_id' => 'nullable|exists:users,user_id',
        ]);

        DB::beginTransaction();
        try {
            $project = Project::create([
                'project_name' => $request->project_name,
                'description' => $request->description,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'status' => 'planning',
                'priority' => $request->priority ?? 'medium',
                'category' => $request->category,
                'budget' => $request->budget,
                'leader_id' => $request->leader_id,
                'created_by' => Auth::id(),
                'last_activity_at' => now(),
            ]);

            // Create default boards
            $defaultBoards = ['To Do', 'In Progress', 'Review', 'Done'];
            foreach ($defaultBoards as $index => $boardName) {
                Board::create([
                    'project_id' => $project->project_id,
                    'name' => $boardName,
                    'position' => $index + 1,
                    'color' => $this->getDefaultBoardColor($index),
                ]);
            }

            // Assign project leader if specified
            if ($request->leader_id) {
                ProjectMember::create([
                    'project_id' => $project->project_id,
                    'user_id' => $request->leader_id,
                    'role' => 'project_manager',
                    'joined_at' => now(),
                ]);
                
                // Send notification to leader using helper
                $leader = User::find($request->leader_id);
                $admin = Auth::user();
                if ($leader && $admin) {
                    \App\Helpers\NotificationHelper::projectLeaderAssigned($project, $leader, $admin);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Project created successfully',
                'project' => $project->load('creator', 'members.user')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Project creation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create project: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete project (API endpoint)
     * 
     * @param int|string $projectId Project ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteProject($projectId)
    {
        /** @var int|string $projectId */
        
        try {
            $project = Project::findOrFail($projectId);
            
            DB::beginTransaction();

            // Delete related data in correct order
            TimeLog::whereIn('card_id', function($query) use ($projectId) {
                $query->select('c.card_id')
                    ->from('cards as c')
                    ->join('boards as b', 'c.board_id', '=', 'b.board_id')
                    ->where('b.project_id', $projectId);
            })->delete();

            Card::whereIn('board_id', function($query) use ($projectId) {
                $query->select('board_id')
                    ->from('boards')
                    ->where('project_id', $projectId);
            })->delete();

            Board::where('project_id', $projectId)->delete();
            ProjectMember::where('project_id', $projectId)->delete();
            
            $project->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Project deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Project deletion failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete project: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add member to project (API endpoint)
     * 
     * @param Request $request HTTP request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addMemberToProject(Request $request)
    {
        /** @var Request $request */
        
        $request->validate([
            'project_id' => 'required|exists:projects,project_id',
            'user_id' => 'required|exists:users,user_id',
            'role' => 'required|in:developer,designer,tester,project_manager'
        ]);

        try {
            // Check if user is already a member
            $existingMember = ProjectMember::where('project_id', $request->project_id)
                ->where('user_id', $request->user_id)
                ->first();

            if ($existingMember) {
                return response()->json([
                    'success' => false,
                    'message' => 'User is already a member of this project'
                ], 400);
            }

            $member = ProjectMember::create([
                'project_id' => $request->project_id,
                'user_id' => $request->user_id,
                'role' => $request->role,
                'joined_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Member added successfully',
                'member' => $member->load('user')
            ]);

        } catch (\Exception $e) {
            Log::error('Add member failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to add member: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove member from project (API endpoint)
     * 
     * @param Request $request HTTP request
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeMemberFromProject(Request $request)
    {
        /** @var Request $request */
        
        $request->validate([
            'project_id' => 'required|exists:projects,project_id',
            'user_id' => 'required|exists:users,user_id'
        ]);

        try {
            $member = ProjectMember::where('project_id', $request->project_id)
                ->where('user_id', $request->user_id)
                ->first();

            if (!$member) {
                return response()->json([
                    'success' => false,
                    'message' => 'Member not found in this project'
                ], 404);
            }

            $member->delete();

            return response()->json([
                'success' => true,
                'message' => 'Member removed successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Remove member failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove member: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update project status (API endpoint)
     * 
     * @param Request $request HTTP request
     * @param int|string $projectId Project ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProjectStatus(Request $request, $projectId)
    {
        /** @var Request $request */
        /** @var int|string $projectId */
        
        $request->validate([
            'status' => 'required|in:planning,active,completed,on-hold'
        ]);

        try {
            $project = Project::findOrFail($projectId);
            $project->status = $request->status;
            $project->last_activity_at = now();
            
            if ($request->status === 'completed') {
                $project->completed_at = now();
            }
            
            $project->save();

            return response()->json([
                'success' => true,
                'message' => 'Project status updated successfully',
                'project' => $project
            ]);

        } catch (\Exception $e) {
            Log::error('Update project status failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update project status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Change project leader (Only admin can do this)
     * 
     * @param Request $request HTTP request
     * @param int|string $projectId Project ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function changeProjectLeader(Request $request, $projectId)
    {
        /** @var Request $request */
        /** @var int|string $projectId */
        
        $request->validate([
            'leader_id' => 'required|exists:users,user_id'
        ]);

        try {
            $project = Project::findOrFail($projectId);
            $newLeader = User::findOrFail($request->leader_id);

            // Verify the new leader has 'leader' role
            if ($newLeader->role !== 'leader') {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected user is not a team leader'
                ], 400);
            }

            DB::beginTransaction();

            // Remove existing project managers
            ProjectMember::where('project_id', $projectId)
                ->where('role', 'project_manager')
                ->delete();

            // Add new leader as project manager
            ProjectMember::create([
                'project_id' => $projectId,
                'user_id' => $request->leader_id,
                'role' => 'project_manager',
                'joined_at' => now(),
            ]);

            // Update project last activity
            $project->last_activity_at = now();
            $project->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Project leader changed successfully',
                'new_leader' => $newLeader
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Change project leader failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to change project leader: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get project analytics data
     * 
     * @param int|string $projectId Project ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProjectAnalytics($projectId)
    {
        /** @var int|string $projectId */
        
        try {
            $project = Project::with(['members.user', 'boards.cards'])->findOrFail($projectId);

            $analytics = [
                'completion_rate' => $this->calculateCompletionRate($projectId),
                'total_hours' => $this->calculateTotalHours($projectId),
                'member_performance' => $this->getMemberPerformance($projectId),
                'task_distribution' => $this->getTaskDistribution($projectId),
                'timeline_progress' => $this->getTimelineProgress($projectId)
            ];

            return response()->json([
                'success' => true,
                'project' => $project,
                'analytics' => $analytics
            ]);

        } catch (\Exception $e) {
            Log::error('Get project analytics failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get project analytics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Private helper methods
     * 
     * @param int $index Board index
     * @return string Color hex code
     */
    private function getDefaultBoardColor($index)
    {
        /** @var int $index */
        
        $colors = ['#6366f1', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4'];
        return $colors[$index % count($colors)];
    }

    /**
     * Calculate completion rate for a project
     * 
     * @param int|string $projectId Project ID
     * @return float Completion rate percentage
     */
    private function calculateCompletionRate($projectId)
    {
        /** @var int|string $projectId */
        
        $totalTasks = DB::table('cards')
            ->join('boards', 'cards.board_id', '=', 'boards.board_id')
            ->where('boards.project_id', $projectId)
            ->count();

        if ($totalTasks === 0) return 0;

        $completedTasks = DB::table('cards')
            ->join('boards', 'cards.board_id', '=', 'boards.board_id')
            ->where('boards.project_id', $projectId)
            ->where('cards.status', 'done')
            ->count();

        return round(($completedTasks / $totalTasks) * 100, 2);
    }

    /**
     * Calculate total hours worked on a project
     * 
     * @param int|string $projectId Project ID
     * @return float Total hours
     */
    private function calculateTotalHours($projectId)
    {
        /** @var int|string $projectId */
        
        return DB::table('time_logs')
            ->join('tasks', 'time_logs.task_id', '=', 'tasks.task_id')
            ->where('tasks.project_id', $projectId)
            ->sum('time_logs.duration_seconds') / 3600; // Convert seconds to hours
    }

    /**
     * Get member performance for a project
     * 
     * @param int|string $projectId Project ID
     * @return \Illuminate\Support\Collection Member performance data
     */
    private function getMemberPerformance($projectId)
    {
        /** @var int|string $projectId */
        
        return DB::table('project_members')
            ->join('users', 'project_members.user_id', '=', 'users.user_id')
            ->leftJoin('card_assignments', 'project_members.user_id', '=', 'card_assignments.user_id')
            ->leftJoin('cards', 'card_assignments.card_id', '=', 'cards.card_id')
            ->leftJoin('boards', 'cards.board_id', '=', 'boards.board_id')
            ->where('project_members.project_id', $projectId)
            ->where('boards.project_id', $projectId)
            ->select(
                'users.full_name',
                'project_members.role',
                DB::raw('COUNT(DISTINCT card_assignments.card_id) as assigned_tasks'),
                DB::raw('SUM(CASE WHEN cards.status = "done" THEN 1 ELSE 0 END) as completed_tasks')
            )
            ->groupBy('users.user_id', 'users.full_name', 'project_members.role')
            ->get();
    }

    /**
     * Get task distribution for a project
     * 
     * @param int|string $projectId Project ID
     * @return \Illuminate\Support\Collection Task distribution data
     */
    private function getTaskDistribution($projectId)
    {
        /** @var int|string $projectId */
        
        return DB::table('cards')
            ->join('boards', 'cards.board_id', '=', 'boards.board_id')
            ->where('boards.project_id', $projectId)
            ->select(
                'cards.status',
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('cards.status')
            ->get();
    }

    /**
     * Get timeline progress for a project
     * 
     * @param int|string $projectId Project ID
     * @return array|null Timeline progress data
     */
    private function getTimelineProgress($projectId)
    {
        /** @var int|string $projectId */
        
        $project = Project::findOrFail($projectId);
        
        if (!$project->start_date || !$project->end_date) {
            return null;
        }

        $totalDays = Carbon::parse($project->start_date)->diffInDays(Carbon::parse($project->end_date));
        $elapsedDays = Carbon::parse($project->start_date)->diffInDays(Carbon::now());
        
        $timeProgress = $totalDays > 0 ? min(($elapsedDays / $totalDays) * 100, 100) : 0;
        $completionRate = $this->calculateCompletionRate($projectId);

        return [
            'time_progress' => round($timeProgress, 2),
            'completion_progress' => $completionRate,
            'is_on_track' => $completionRate >= $timeProgress,
            'days_remaining' => max($totalDays - $elapsedDays, 0)
        ];
    }

    // Task Management Methods
    public function getTaskStatistics()
    {
        try {
            $statistics = DB::table('cards')
                ->join('boards', 'cards.board_id', '=', 'boards.board_id')
                ->join('projects', 'boards.project_id', '=', 'projects.project_id')
                ->select(
                    DB::raw('COUNT(*) as total'),
                    DB::raw('SUM(CASE WHEN cards.status = "todo" THEN 1 else 0 END) as todo'),
                    DB::raw('SUM(CASE WHEN cards.status = "in_progress" THEN 1 else 0 END) as in_progress'),
                    DB::raw('SUM(CASE WHEN cards.status = "review" THEN 1 else 0 END) as review'),
                    DB::raw('SUM(CASE WHEN cards.status = "done" THEN 1 else 0 END) as done')
                )
                ->first();

            return response()->json([
                'success' => true,
                'statistics' => $statistics
            ]);
        } catch (\Exception $e) {
            Log::error('Get task statistics failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load statistics'
            ], 500);
        }
    }

    public function getProjectsList()
    {
        try {
            $projects = Project::select('project_id', 'project_name')
                ->orderBy('project_name')
                ->get();

            return response()->json([
                'success' => true,
                'projects' => $projects
            ]);
        } catch (\Exception $e) {
            Log::error('Get projects list failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load projects'
            ], 500);
        }
    }

    /**
     * Get tasks with filters
     * 
     * @param Request $request HTTP request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTasks(Request $request)
    {
        /** @var Request $request */
        
        try {
            $query = DB::table('cards')
                ->join('boards', 'cards.board_id', '=', 'boards.board_id')
                ->join('projects', 'boards.project_id', '=', 'projects.project_id')
                ->leftJoin('card_assignments', 'cards.card_id', '=', 'card_assignments.card_id')
                ->leftJoin('users', 'card_assignments.user_id', '=', 'users.user_id')
                ->select(
                    'cards.*',
                    'projects.project_name',
                    'projects.project_id',
                    DB::raw('GROUP_CONCAT(DISTINCT users.full_name) as assigned_users_names'),
                    DB::raw('GROUP_CONCAT(DISTINCT users.user_id) as assigned_user_ids')
                )
                ->groupBy(
                    'cards.card_id',
                    'cards.title',
                    'cards.description',
                    'cards.status',
                    'cards.priority',
                    'cards.due_date',
                    'cards.board_id',
                    'cards.position',
                    'cards.created_at',
                    'cards.updated_at',
                    'projects.project_name',
                    'projects.project_id'
                );

            // Apply filters
            if ($request->filled('project_id')) {
                $query->where('projects.project_id', $request->project_id);
            }

            if ($request->filled('status')) {
                $query->where('cards.status', $request->status);
            }

            if ($request->filled('priority')) {
                $query->where('cards.priority', $request->priority);
            }

            $tasks = $query->orderBy('cards.created_at', 'desc')->get();

            // Transform the data to include assigned users as array
            $tasks = $tasks->map(function ($task) {
                $assignedUsers = [];
                if ($task->assigned_users_names && $task->assigned_user_ids) {
                    $names = explode(',', $task->assigned_users_names);
                    $ids = explode(',', $task->assigned_user_ids);
                    
                    for ($i = 0; $i < count($names); $i++) {
                        if (isset($ids[$i])) {
                            $assignedUsers[] = [
                                'user_id' => $ids[$i],
                                'full_name' => $names[$i]
                            ];
                        }
                    }
                }
                
                $task->assigned_users = $assignedUsers;
                unset($task->assigned_users_names);
                unset($task->assigned_user_ids);
                
                return $task;
            });

            return response()->json([
                'success' => true,
                'tasks' => $tasks
            ]);
        } catch (\Exception $e) {
            Log::error('Get tasks failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load tasks'
            ], 500);
        }
    }

    /**
     * Get task details by ID
     * 
     * @param int|string $taskId Task ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTaskDetails($taskId)
    {
        /** @var int|string $taskId */
        
        try {
            $task = DB::table('cards')
                ->join('boards', 'cards.board_id', '=', 'boards.board_id')
                ->join('projects', 'boards.project_id', '=', 'projects.project_id')
                ->leftJoin('card_assignments', 'cards.card_id', '=', 'card_assignments.card_id')
                ->leftJoin('users', 'card_assignments.user_id', '=', 'users.user_id')
                ->where('cards.card_id', $taskId)
                ->select(
                    'cards.*',
                    'projects.project_name',
                    'projects.project_id',
                    'boards.board_name',
                    DB::raw('GROUP_CONCAT(DISTINCT CONCAT(users.user_id, ":", users.full_name, ":", users.email)) as assigned_users_data')
                )
                ->groupBy(
                    'cards.card_id',
                    'cards.title',
                    'cards.description',
                    'cards.status',
                    'cards.priority',
                    'cards.due_date',
                    'cards.board_id',
                    'cards.position',
                    'cards.created_at',
                    'cards.updated_at',
                    'projects.project_name',
                    'projects.project_id',
                    'boards.board_name'
                )
                ->first();

            if (!$task) {
                return response()->json([
                    'success' => false,
                    'message' => 'Task not found'
                ], 404);
            }

            // Parse assigned users
            $assignedUsers = [];
            if ($task->assigned_users_data) {
                $usersData = explode(',', $task->assigned_users_data);
                foreach ($usersData as $userData) {
                    $parts = explode(':', $userData);
                    if (count($parts) >= 3) {
                        $assignedUsers[] = [
                            'user_id' => $parts[0],
                            'full_name' => $parts[1],
                            'email' => $parts[2]
                        ];
                    }
                }
            }
            
            $task->assigned_users = $assignedUsers;
            unset($task->assigned_users_data);

            // Get subtasks
            $subtasks = DB::table('subtasks')
                ->where('card_id', $taskId)
                ->orderBy('created_at')
                ->get();

            // Get comments
            $comments = DB::table('comments')
                ->join('users', 'comments.user_id', '=', 'users.user_id')
                ->where('comments.card_id', $taskId)
                ->select('comments.*', 'users.full_name', 'users.email')
                ->orderBy('comments.created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'task' => $task,
                'subtasks' => $subtasks,
                'comments' => $comments
            ]);
        } catch (\Exception $e) {
            Log::error('Get task details failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load task details'
            ], 500);
        }
    }

    /**
     * Update task details
     * 
     * @param Request $request HTTP request
     * @param int|string $taskId Task ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateTask(Request $request, $taskId)
    {
        /** @var Request $request */
        /** @var int|string $taskId */
        
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:todo,in_progress,review,done',
            'priority' => 'nullable|in:low,medium,high,critical',
            'due_date' => 'nullable|date'
        ]);

        DB::beginTransaction();
        try {
            $task = DB::table('cards')->where('card_id', $taskId)->first();
            if (!$task) {
                return response()->json([
                    'success' => false,
                    'message' => 'Task not found'
                ], 404);
            }

            DB::table('cards')
                ->where('card_id', $taskId)
                ->update([
                    'title' => $request->title,
                    'description' => $request->description,
                    'status' => $request->status,
                    'priority' => $request->priority,
                    'due_date' => $request->due_date,
                    'updated_at' => now()
                ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Task updated successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Update task failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update task'
            ], 500);
        }
    }

    /**
     * Reassign task to different users
     * 
     * @param Request $request HTTP request
     * @param int|string $taskId Task ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function reassignTask(Request $request, $taskId)
    {
        /** @var Request $request */
        /** @var int|string $taskId */
        
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,user_id'
        ]);

        DB::beginTransaction();
        try {
            // Remove existing assignments
            DB::table('card_assignments')->where('card_id', $taskId)->delete();

            // Add new assignments
            $assignments = [];
            foreach ($request->user_ids as $userId) {
                $assignments[] = [
                    'card_id' => $taskId,
                    'user_id' => $userId,
                    'assigned_at' => now()
                ];
            }

            DB::table('card_assignments')->insert($assignments);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Task reassigned successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Reassign task failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to reassign task'
            ], 500);
        }
    }

    // Enhanced Bulk Operations
    /**
     * Bulk update project status
     * 
     * @param Request $request HTTP request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkUpdateProjectStatus(Request $request)
    {
        /** @var Request $request */
        
        $request->validate([
            'project_ids' => 'required|array',
            'project_ids.*' => 'exists:projects,project_id',
            'status' => 'required|in:planning,in_progress,review,completed,on_hold'
        ]);

        DB::beginTransaction();
        try {
            $updatedCount = DB::table('projects')
                ->whereIn('project_id', $request->project_ids)
                ->update([
                    'status' => $request->status,
                    'updated_at' => now()
                ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully updated {$updatedCount} projects to {$request->status}"
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Bulk update project status failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update project status'
            ], 500);
        }
    }

    /**
     * Bulk assign users to projects
     * 
     * @param Request $request HTTP request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkAssignUsers(Request $request)
    {
        /** @var Request $request */
        
        $request->validate([
            'project_ids' => 'required|array',
            'project_ids.*' => 'exists:projects,project_id',
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,user_id',
            'role' => 'required|in:member,team_lead,project_manager'
        ]);

        DB::beginTransaction();
        try {
            $assignedCount = 0;
            
            foreach ($request->project_ids as $projectId) {
                foreach ($request->user_ids as $userId) {
                    // Check if assignment already exists
                    $existing = DB::table('project_members')
                        ->where('project_id', $projectId)
                        ->where('user_id', $userId)
                        ->first();

                    if (!$existing) {
                        DB::table('project_members')->insert([
                            'project_id' => $projectId,
                            'user_id' => $userId,
                            'role' => $request->role,
                            'joined_at' => now()
                        ]);
                        $assignedCount++;
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully assigned {$assignedCount} user-project combinations"
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Bulk assign users failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign users'
            ], 500);
        }
    }

    /**
     * Bulk remove users from projects
     * 
     * @param Request $request HTTP request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkRemoveUsers(Request $request)
    {
        /** @var Request $request */
        
        $request->validate([
            'project_ids' => 'required|array',
            'project_ids.*' => 'exists:projects,project_id',
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,user_id'
        ]);

        DB::beginTransaction();
        try {
            $removedCount = 0;
            
            foreach ($request->project_ids as $projectId) {
                foreach ($request->user_ids as $userId) {
                    $deleted = DB::table('project_members')
                        ->where('project_id', $projectId)
                        ->where('user_id', $userId)
                        ->delete();
                    
                    $removedCount += $deleted;
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully removed {$removedCount} user assignments"
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Bulk remove users failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove users'
            ], 500);
        }
    }

    /**
     * Bulk update task status
     * 
     * @param Request $request HTTP request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkUpdateTaskStatus(Request $request)
    {
        /** @var Request $request */
        
        $request->validate([
            'task_ids' => 'required|array',
            'task_ids.*' => 'exists:cards,card_id',
            'status' => 'required|in:todo,in_progress,review,done'
        ]);

        DB::beginTransaction();
        try {
            $updatedCount = DB::table('cards')
                ->whereIn('card_id', $request->task_ids)
                ->update([
                    'status' => $request->status,
                    'updated_at' => now()
                ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully updated {$updatedCount} tasks to {$request->status}"
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Bulk update task status failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update task status'
            ], 500);
        }
    }

    /**
     * Bulk update task priority
     * 
     * @param Request $request HTTP request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkUpdateTaskPriority(Request $request)
    {
        /** @var Request $request */
        
        $request->validate([
            'task_ids' => 'required|array',
            'task_ids.*' => 'exists:cards,card_id',
            'priority' => 'required|in:low,medium,high,critical'
        ]);

        DB::beginTransaction();
        try {
            $updatedCount = DB::table('cards')
                ->whereIn('card_id', $request->task_ids)
                ->update([
                    'priority' => $request->priority,
                    'updated_at' => now()
                ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully updated priority for {$updatedCount} tasks"
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Bulk update task priority failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update task priority'
            ], 500);
        }
    }

    /**
     * Bulk delete tasks
     * 
     * @param Request $request HTTP request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkDeleteTasks(Request $request)
    {
        /** @var Request $request */
        
        $request->validate([
            'task_ids' => 'required|array',
            'task_ids.*' => 'exists:cards,card_id'
        ]);

        DB::beginTransaction();
        try {
            // Delete related data first
            DB::table('card_assignments')->whereIn('card_id', $request->task_ids)->delete();
            DB::table('comments')->whereIn('card_id', $request->task_ids)->delete();
            DB::table('subtasks')->whereIn('card_id', $request->task_ids)->delete();
            DB::table('time_logs')->whereIn('card_id', $request->task_ids)->delete();
            
            // Delete tasks
            $deletedCount = DB::table('cards')->whereIn('card_id', $request->task_ids)->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully deleted {$deletedCount} tasks"
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Bulk delete tasks failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete tasks'
            ], 500);
        }
    }

    /**
     * Export project data to CSV or JSON
     * 
     * @param Request $request HTTP request
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function exportProjectData(Request $request)
    {
        /** @var Request $request */
        
        try {
            $format = $request->input('format', 'csv');
            $projectIds = $request->input('project_ids', []);
            
            // Get project data
            $query = DB::table('projects as p')
                ->leftJoin('users as u', 'p.created_by', '=', 'u.user_id')
                ->select(
                    'p.project_id',
                    'p.project_name',
                    'p.description',
                    'p.status',
                    'p.priority',
                    'p.start_date',
                    'p.end_date',
                    'p.budget',
                    'p.created_at',
                    'u.full_name as created_by_name'
                );

            if (!empty($projectIds)) {
                $query->whereIn('p.project_id', $projectIds);
            }

            $projects = $query->get();

            if ($format === 'json') {
                return response()->json([
                    'success' => true,
                    'data' => $projects,
                    'exported_at' => now()->toISOString()
                ]);
            }

            // CSV export
            $filename = 'projects_export_' . now()->format('Y-m-d_H-i-s') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\""
            ];

            $callback = function() use ($projects) {
                $file = fopen('php://output', 'w');
                
                // Header row
                fputcsv($file, [
                    'Project ID', 'Name', 'Description', 'Status', 'Priority',
                    'Start Date', 'End Date', 'Budget', 'Created At', 'Created By'
                ]);
                
                // Data rows
                foreach ($projects as $project) {
                    /** @var object $project */
                    fputcsv($file, [
                        $project->project_id,
                        $project->project_name,
                        $project->description,
                        $project->status,
                        $project->priority,
                        $project->start_date,
                        $project->end_date,
                        $project->budget,
                        $project->created_at,
                        $project->created_by_name
                    ]);
                }
                
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            Log::error('Export project data failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to export project data'
            ], 500);
        }
    }

    /**
     * Get bulk operation progress
     * 
     * @param int|string $operationId Operation ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBulkOperationProgress($operationId)
    {
        /** @var int|string $operationId */
        
        try {
            // This would typically check a job queue or cache for progress
            $progress = Cache::get("bulk_operation_{$operationId}", [
                'status' => 'completed',
                'progress' => 100,
                'processed' => 0,
                'total' => 0,
                'errors' => []
            ]);

            return response()->json([
                'success' => true,
                'progress' => $progress
            ]);
        } catch (\Exception $e) {
            Log::error('Get bulk operation progress failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get operation progress'
            ], 500);
        }
    }
}