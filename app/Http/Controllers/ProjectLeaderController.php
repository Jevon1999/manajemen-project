<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Helpers\NotificationHelper;

class ProjectLeaderController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role.leader']);
    }

    /**
     * Show projects where user is assigned as project manager or leader
     */
    public function myProjects()
    {
        $user = Auth::user();
        
        // Get projects where user is either:
        // 1. Assigned as leader_id in projects table (direct assignment)
        // 2. OR listed as project_manager in project_members table
        $projects = Project::where(function($query) use ($user) {
            $query->where('leader_id', $user->user_id)
                  ->orWhereHas('members', function($q) use ($user) {
                      $q->where('user_id', $user->user_id)
                        ->where('role', 'project_manager');
                  });
        })
        ->with(['members.user', 'creator', 'leader'])
        ->withCount(['members', 'boards', 'cards'])
        ->orderBy('created_at', 'desc')
        ->get();

        return view('leader.projects.index', compact('projects'));
    }

    /**
     * Show form for creating new project (Leader only)
     */
    public function create()
    {
        return view('leader.projects.create');
    }

    /**
     * Store new project created by leader
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'deadline' => 'nullable|date|after_or_equal:today',
            'status' => 'nullable|in:planning,active,on_hold,completed,cancelled',
            'category' => 'nullable|in:web_development,mobile_app,desktop_software,data_analysis,marketing,design,research,other',
            'priority' => 'nullable|in:low,medium,high,critical',
        ]);

        try {
            DB::beginTransaction();

            $project = Project::create([
                'project_name' => $request->name,
                'description' => $request->description,
                'deadline' => $request->deadline,
                'status' => $request->status ?? 'planning',
                'category' => $request->category ?? 'other',
                'priority' => $request->priority ?? 'medium',
                'created_by' => Auth::id(),
                'leader_id' => Auth::id(),
                'last_activity_at' => now(),
            ]);

            // Automatically add leader as project manager
            ProjectMember::create([
                'project_id' => $project->project_id,
                'user_id' => Auth::id(),
                'role' => 'project_manager',
                'joined_at' => now(),
            ]);

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Project created successfully!',
                    'project' => $project,
                    'redirect' => route('leader.projects.show', $project->project_id)
                ]);
            }

            return redirect()->route('leader.projects.show', $project->project_id)
                ->with('success', 'Project created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Create project failed: ' . $e->getMessage());

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create project: ' . $e->getMessage()
                ], 500);
            }

            return back()->withInput()
                ->with('error', 'Failed to create project: ' . $e->getMessage());
        }
    }

    /**
     * Show form for editing project (Leader only)
     */
    public function edit($projectId)
    {
        $user = Auth::user();
        
        // Verify user is project manager for this project
        $membership = ProjectMember::where('project_id', $projectId)
            ->where('user_id', $user->user_id)
            ->where('role', 'project_manager')
            ->first();

        if (!$membership) {
            abort(403, 'You are not authorized to edit this project');
        }

        $project = Project::findOrFail($projectId);

        return view('leader.projects.edit', compact('project'));
    }

    /**
     * Update project (Leader only)
     */
    public function update(Request $request, $projectId)
    {
        $user = Auth::user();
        
        // Verify user is project manager for this project
        $membership = ProjectMember::where('project_id', $projectId)
            ->where('user_id', $user->user_id)
            ->where('role', 'project_manager')
            ->first();

        if (!$membership) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to edit this project'
            ], 403);
        }

        $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'deadline' => 'nullable|date',
            'status' => 'nullable|in:planning,active,on_hold,completed,cancelled',
            'category' => 'nullable|in:web_development,mobile_app,desktop_software,data_analysis,marketing,design,research,other',
            'priority' => 'nullable|in:low,medium,high,critical',
        ]);

        try {
            $project = Project::findOrFail($projectId);
            
            $project->update([
                'project_name' => $request->name,
                'description' => $request->description,
                'deadline' => $request->deadline,
                'status' => $request->status,
                'category' => $request->category,
                'priority' => $request->priority,
                'last_activity_at' => now(),
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Project updated successfully!',
                    'project' => $project
                ]);
            }

            return redirect()->route('leader.projects.show', $projectId)
                ->with('success', 'Project updated successfully!');

        } catch (\Exception $e) {
            Log::error('Update project failed: ' . $e->getMessage());

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update project: ' . $e->getMessage()
                ], 500);
            }

            return back()->withInput()
                ->with('error', 'Failed to update project: ' . $e->getMessage());
        }
    }

    /**
     * Show detailed project dashboard for leaders
     */
    public function show($projectId)
    {
        $user = Auth::user();
        
        // Verify user is leader or project manager for this project
        $project = Project::findOrFail($projectId);
        
        $isLeader = $project->leader_id === $user->user_id;
        $isProjectManager = ProjectMember::where('project_id', $projectId)
            ->where('user_id', $user->user_id)
            ->where('role', 'project_manager')
            ->exists();

        if (!$isLeader && !$isProjectManager && $user->role !== 'admin') {
            abort(403, 'You are not authorized to manage this project.');
        }

        // Load project with relationships
        $project = $project->load([
            'members.user', 
            'creator',
            'leader',
            'boards.cards.assignments.user'
        ]);

        // Get project statistics
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

        // Team performance
        $teamMembers = $project->members()
            ->whereIn('role', ['developer', 'designer'])
            ->with('user')
            ->get();

        $teamPerformance = $teamMembers->map(function($member) use ($project) {
            $userTasks = $project->boards->flatMap->cards
                ->filter(function($card) use ($member) {
                    return $card->assignments->contains('user_id', $member->user_id);
                });
            
            return (object)[
                'user' => $member->user,
                'role' => $member->role,
                'total_tasks' => $userTasks->count(),
                'completed_tasks' => $userTasks->where('status', 'done')->count(),
                'active_tasks' => $userTasks->where('status', 'in_progress')->count(),
                'overdue_tasks' => $userTasks->filter(function($card) {
                    return $card->due_date && 
                           \Carbon\Carbon::parse($card->due_date)->isPast() && 
                           $card->status !== 'done';
                })->count()
            ];
        });

        // Recent tasks
        $recentTasks = $project->boards->flatMap->cards
            ->sortByDesc('created_at')
            ->take(5);

        return view('leader.projects.show', compact(
            'project', 
            'totalTasks', 
            'completedTasks', 
            'inProgressTasks', 
            'todoTasks', 
            'reviewTasks',
            'criticalTasks',
            'highTasks', 
            'mediumTasks', 
            'lowTasks',
            'teamPerformance',
            'recentTasks'
        ));
    }

    /**
     * Show team management for a specific project
     */
    public function manageTeam($projectId)
    {
        $user = Auth::user();
        
        // Verify user is leader or project manager for this project
        $project = Project::with(['members.user', 'creator', 'leader'])->findOrFail($projectId);
        
        $isLeader = $project->leader_id === $user->user_id;
        $isProjectManager = ProjectMember::where('project_id', $projectId)
            ->where('user_id', $user->user_id)
            ->where('role', 'project_manager')
            ->exists();

        if (!$isLeader && !$isProjectManager) {
            abort(403, 'You are not authorized to manage this project team');
        }
        
        // Get available users (excluding admin and current members)
        $currentMemberIds = $project->members->pluck('user_id')->toArray();
        $availableUsers = User::where('status', 'active')
            ->where('role', '!=', 'admin')
            ->whereNotIn('user_id', $currentMemberIds)
            ->select('user_id', 'full_name', 'email', 'role')
            ->get();

        return view('leader.projects.manage-team', compact('project', 'availableUsers'));
    }

    /**
     * Add member to project (Leader can only add team members, not other leaders)
     */
    public function addTeamMember(Request $request, $projectId)
    {
        $user = Auth::user();
        
        // Verify user is project manager for this project
        $membership = ProjectMember::where('project_id', $projectId)
            ->where('user_id', $user->user_id)
            ->where('role', 'project_manager')
            ->first();

        if (!$membership) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to manage this project team'
            ], 403);
        }

        $request->validate([
            'user_id' => 'required|exists:users,user_id',
            'role' => 'required|in:developer,designer,tester'
        ]);

        try {
            // Check if user is not admin and not already a member
            $targetUser = User::findOrFail($request->user_id);
            
            if ($targetUser->role === 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot add system administrator to project'
                ], 400);
            }

            // Check if user is already a member
            $existingMember = ProjectMember::where('project_id', $projectId)
                ->where('user_id', $request->user_id)
                ->first();

            if ($existingMember) {
                return response()->json([
                    'success' => false,
                    'message' => 'User is already a member of this project'
                ], 400);
            }

            $member = ProjectMember::create([
                'project_id' => $projectId,
                'user_id' => $request->user_id,
                'role' => $request->role,
                'joined_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Team member added successfully',
                'member' => $member->load('user')
            ]);

        } catch (\Exception $e) {
            Log::error('Add team member failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to add team member: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove team member from project (Leader cannot remove other project managers)
     */
    public function removeTeamMember(Request $request, $projectId)
    {
        $user = Auth::user();
        
        // Verify user is project manager for this project
        $membership = ProjectMember::where('project_id', $projectId)
            ->where('user_id', $user->user_id)
            ->where('role', 'project_manager')
            ->first();

        if (!$membership) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to manage this project team'
            ], 403);
        }

        $request->validate([
            'user_id' => 'required|exists:users,user_id'
        ]);

        try {
            $member = ProjectMember::where('project_id', $projectId)
                ->where('user_id', $request->user_id)
                ->first();

            if (!$member) {
                return response()->json([
                    'success' => false,
                    'message' => 'User is not a member of this project'
                ], 404);
            }

            // Leaders cannot remove other project managers (only admin can do that)
            if ($member->role === 'project_manager') {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot remove other project managers. Contact system administrator.'
                ], 403);
            }

            // Leaders cannot remove themselves
            if ($member->user_id === $user->user_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot remove yourself from the project'
                ], 403);
            }

            $member->delete();

            return response()->json([
                'success' => true,
                'message' => 'Team member removed successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Remove team member failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove team member: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update team member role (Leader can only change roles of non-managers)
     */
    public function updateMemberRole(Request $request, $projectId)
    {
        $user = Auth::user();
        
        // Verify user is project manager for this project
        $membership = ProjectMember::where('project_id', $projectId)
            ->where('user_id', $user->user_id)
            ->where('role', 'project_manager')
            ->first();

        if (!$membership) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to manage this project team'
            ], 403);
        }

        $request->validate([
            'user_id' => 'required|exists:users,user_id',
            'role' => 'required|in:developer,designer,tester'
        ]);

        try {
            $member = ProjectMember::where('project_id', $projectId)
                ->where('user_id', $request->user_id)
                ->first();

            if (!$member) {
                return response()->json([
                    'success' => false,
                    'message' => 'User is not a member of this project'
                ], 404);
            }

            // Leaders cannot change role of project managers
            if ($member->role === 'project_manager') {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot change the role of project managers'
                ], 403);
            }

            $member->role = $request->role;
            $member->save();

            return response()->json([
                'success' => true,
                'message' => 'Member role updated successfully',
                'member' => $member->load('user')
            ]);

        } catch (\Exception $e) {
            Log::error('Update member role failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update member role: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get project statistics for project manager
     */
    public function getProjectStats($projectId)
    {
        $user = Auth::user();
        
        // Verify user is project manager for this project
        $membership = ProjectMember::where('project_id', $projectId)
            ->where('user_id', $user->user_id)
            ->where('role', 'project_manager')
            ->first();

        if (!$membership) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to view this project statistics'
            ], 403);
        }

        try {
            $project = Project::with(['members.user', 'boards.cards'])->findOrFail($projectId);

            $stats = [
                'total_members' => $project->members->count(),
                'total_tasks' => $project->boards->sum(function($board) {
                    return $board->cards->count();
                }),
                'completed_tasks' => $project->boards->sum(function($board) {
                    return $board->cards->where('status', 'done')->count();
                }),
                'in_progress_tasks' => $project->boards->sum(function($board) {
                    return $board->cards->where('status', 'in_progress')->count();
                }),
                'member_roles' => $project->members->groupBy('role')->map->count(),
            ];

            return response()->json([
                'success' => true,
                'project' => $project,
                'statistics' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Get project stats failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get project statistics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search available users for adding to project
     */
    public function searchAvailableUsers(Request $request, $projectId)
    {
        $user = Auth::user();
        
        // Verify user is project manager for this project
        $membership = ProjectMember::where('project_id', $projectId)
            ->where('user_id', $user->user_id)
            ->where('role', 'project_manager')
            ->first();

        if (!$membership) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to search users for this project'
            ], 403);
        }

        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        // Get current project members
        $currentMemberIds = ProjectMember::where('project_id', $projectId)
            ->pluck('user_id')
            ->toArray();

        $users = User::where(function($q) use ($query) {
                $q->where('full_name', 'LIKE', "%{$query}%")
                  ->orWhere('username', 'LIKE', "%{$query}%")
                  ->orWhere('email', 'LIKE', "%{$query}%");
            })
            ->where('status', 'active')
            ->where('role', '!=', 'admin') // Leaders cannot add admins
            ->whereNotIn('user_id', $currentMemberIds)
            ->limit(10)
            ->get(['user_id', 'full_name', 'username', 'email', 'role']);

        return response()->json($users);
    }

    /**
     * Complete the project
     * 
     * @param Request $request
     * @param int $projectId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function complete(Request $request, $projectId)
    {
        $user = Auth::user();
        
        try {
            $project = Project::with(['boards.cards', 'members.user'])->findOrFail($projectId);
            
            // Check if user is project leader
            $membership = ProjectMember::where('project_id', $projectId)
                ->where('user_id', $user->user_id)
                ->where('role', 'project_manager')
                ->first();

            if (!$membership) {
                return redirect()->back()->with('error', 'Unauthorized: You are not the project leader');
            }
            
            // Check if project already completed
            if ($project->status === 'completed') {
                return redirect()->back()->with('warning', 'Project is already completed');
            }

            // Validate request for completion notes and delay reason
            $validated = $request->validate([
                'completion_notes' => 'nullable|string|max:1000',
                'delay_reason' => 'nullable|required_if:is_overdue,true|string|max:500',
                'is_overdue' => 'nullable|boolean',
                'force_complete' => 'nullable|boolean' // Allow force completion even with pending tasks
            ]);
            
            DB::beginTransaction();
            
            // Validate all tasks are completed (unless force_complete)
            $totalTasks = $project->boards->sum(function($board) {
                return $board->cards->count();
            });
            
            $completedTasks = $project->boards->sum(function($board) {
                return $board->cards->where('status', 'done')->count();
            });
            
            $pendingTasks = $totalTasks - $completedTasks;
            
            // Allow completion even without tasks (project might be cancelled or no tasks needed)
            // Just log a warning if no tasks exist
            if ($totalTasks === 0) {
                Log::warning("Project being completed without any tasks", [
                    'project_id' => $project->project_id,
                    'project_name' => $project->project_name,
                    'leader_id' => $user->user_id
                ]);
            }
            
            // Check pending tasks only if tasks exist and force_complete is not true
            if ($totalTasks > 0 && $pendingTasks > 0 && !($validated['force_complete'] ?? false)) {
                DB::rollBack();
                return redirect()->back()->with('error', "Cannot complete project: {$pendingTasks} task(s) still pending. All tasks must be marked as 'Done' first.");
            }
            
            Log::info("Completing project", [
                'project_id' => $project->project_id,
                'project_name' => $project->project_name,
                'leader_id' => $user->user_id,
                'total_tasks' => $totalTasks,
                'completed_tasks' => $completedTasks,
                'pending_tasks' => $pendingTasks,
                'is_overdue' => $project->isOverdue(),
            ]);
            
            // Use model method to mark as completed with tracking
            $project->markAsCompleted(
                $validated['completion_notes'] ?? null,
                $validated['delay_reason'] ?? null
            );
            
            Log::info("Project marked as completed", [
                'project_id' => $project->project_id,
                'status' => $project->status,
                'completed_at' => now(),
            ]);
            
            // Notify all project members
            foreach ($project->members as $member) {
                if ($member->user_id !== $user->user_id) {
                    NotificationHelper::projectCompleted(
                        $project,
                        $member->user_id,
                        $user->user_id
                    );
                }
            }

            // Send notification to admin when project is completed
            $notificationService = new \App\Services\NotificationService();
            $notificationService->notifyProjectCompletion($project, $user);
            
            DB::commit();
            
            // Prepare success message with delay info
            $message = $project->is_overdue 
                ? "✅ Project berhasil diselesaikan! (Terlambat {$project->delay_days} hari) 🎉"
                : '✅ Project berhasil diselesaikan tepat waktu! 🎉';
            
            return redirect()->route('leader.projects.show', $project->project_id)
                ->with('success', $message);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error completing project', [
                'project_id' => $projectId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->with('error', 'Failed to complete project: ' . $e->getMessage());
        }
    }
}