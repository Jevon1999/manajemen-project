<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Project;
use App\Services\TaskService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TaskController extends Controller
{
    protected $taskService;
    
    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }
    
    /**
     * Display tasks for a project
     */
    public function index($projectId)
    {
        $project = Project::findOrFail($projectId);
        
        if (!$this->canViewProject($project)) {
            $user = Auth::user();
            $errorMessage = "Anda tidak memiliki akses ke project ini. ";
            $errorMessage .= "User ID: {$user->user_id}, Role: {$user->role}. ";
            $errorMessage .= "Project Leader ID: {$project->leader_id}. ";
            $errorMessage .= "Pastikan Anda adalah leader project atau sudah ditambahkan sebagai member.";
            
            abort(403, $errorMessage);
        }
        
        $filters = [
            'status' => request('status'),
            'priority' => request('priority'),
            'assigned_to' => request('assigned_to'),
            'overdue' => request('overdue') === '1',
            'sort_by' => request('sort_by', 'created_at'),
            'sort_order' => request('sort_order', 'desc'),
        ];
        
        $tasks = $this->taskService->getProjectTasks($projectId, $filters);
        $statistics = $this->taskService->getProjectTaskStatistics($projectId);
        $members = $project->members()->with('user')->get();
        $canManage = $this->taskService->canManageTasks($projectId, Auth::id());
        
        return view('tasks.index', compact('project', 'tasks', 'statistics', 'members', 'canManage', 'filters'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($projectId)
    {
        $project = Project::findOrFail($projectId);
        $user = Auth::user();
        
        // Debug info
        Log::info('Create task attempt', [
            'project_id' => $projectId,
            'project_leader_id' => $project->leader_id,
            'user_id' => $user->user_id,
            'user_role' => $user->role,
            'is_leader' => $project->leader_id === $user->user_id,
            'has_leader_role' => $user->role === 'leader'
        ]);
        
        if (!$this->taskService->canManageTasks($projectId, Auth::id())) {
            abort(403, 'Hanya leader project yang dapat membuat task. User ID: ' . $user->user_id . ', Role: ' . $user->role . ', Project Leader: ' . $project->leader_id);
        }
        
        // Get all members but exclude project managers - only show designers and developers
        $allMembers = $project->members()
            ->with('user')
            ->whereIn('role', ['designer', 'developer'])
            ->get();
        
        // Get active tasks for each member (including overdue)
        $memberActiveTasks = [];
        foreach ($allMembers as $member) {
            $activeTask = Task::where('assigned_to', $member->user->user_id)
                ->whereIn('status', ['todo', 'in_progress', 'overdue'])
                ->with('project')
                ->first();
            
            if ($activeTask) {
                $memberActiveTasks[$member->user->user_id] = $activeTask;
            }
        }
        
        return view('tasks.create', compact('project', 'allMembers', 'memberActiveTasks'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $projectId)
    {
        if (!$this->taskService->canManageTasks($projectId, Auth::id())) {
            abort(403, 'Hanya leader yang dapat membuat task.');
        }
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,user_id',
            'priority' => 'required|in:low,medium,high',
            'deadline' => 'nullable|date',
            'status' => 'nullable|in:todo,in_progress,review,done',
        ]);
        
        try {
            $validated['project_id'] = $projectId;
            $task = $this->taskService->createTask($validated);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Task berhasil dibuat!',
                    'task' => $task,
                ]);
            }
            
            return redirect()->route('admin.projects.tasks.index', $projectId)
                ->with('success', 'Task berhasil dibuat!');
                
        } catch (\Exception $e) {
            Log::error('Failed to create task: ' . $e->getMessage());
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal membuat task: ' . $e->getMessage(),
                ], 422);
            }
            
            return back()->withErrors(['error' => 'Gagal membuat task.'])->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($projectId, $taskId = null)
    {
        try {
            // If only one parameter passed, it's actually the task ID
            if ($taskId === null) {
                $taskId = $projectId;
                $task = Task::with(['project', 'assignedUser', 'creator', 'board', 'board.project'])->findOrFail($taskId);
                $projectId = $task->project_id;
            } else {
                $task = Task::with(['project', 'assignedUser', 'creator', 'board', 'board.project'])->findOrFail($taskId);
            }
            
            if (!$this->taskService->canAccessTask($taskId, Auth::id())) {
                abort(403, 'Anda tidak memiliki akses ke task ini.');
            }
            
            // Ensure task has project loaded
            if (!$task->project) {
                abort(404, 'Project tidak ditemukan untuk task ini.');
            }
            
            $canManage = $this->taskService->canManageTasks($projectId, Auth::id());
            $members = $task->project->members()->with('user')->get();
            $project = $task->project; // Add project variable for the view
            
            return view('tasks.show', compact('task', 'canManage', 'members', 'project'));
            
        } catch (\Exception $e) {
            Log::error('Error in TaskController::show(): ' . $e->getMessage());
            return back()->withErrors(['error' => 'Terjadi kesalahan saat mengakses task. Silakan coba lagi.']);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($projectId, $taskId)
    {
        $task = Task::with(['project', 'assignedUser'])->findOrFail($taskId);
        
        if (!$this->taskService->canManageTasks($projectId, Auth::id())) {
            abort(403, 'Hanya leader yang dapat mengedit task.');
        }
        
        $members = $task->project->members()->with('user')->get();
        
        return view('tasks.edit', compact('task', 'members'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $projectId, $taskId)
    {
        if (!$this->taskService->canManageTasks($projectId, Auth::id())) {
            abort(403, 'Hanya leader yang dapat mengupdate task.');
        }
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,user_id',
            'priority' => 'required|in:low,medium,high',
            'deadline' => 'nullable|date',
        ]);
        
        try {
            $task = $this->taskService->updateTask($taskId, $validated);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Task berhasil diupdate!',
                    'task' => $task,
                ]);
            }
            
            return redirect()->route('admin.projects.tasks.show', [$projectId, $taskId])
                ->with('success', 'Task berhasil diupdate!');
                
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($projectId, $taskId)
    {
        if (!$this->taskService->canManageTasks($projectId, Auth::id())) {
            abort(403, 'Hanya leader yang dapat menghapus task.');
        }
        
        try {
            $this->taskService->deleteTask($taskId);
            
            return response()->json(['success' => true, 'message' => 'Task berhasil dihapus!']);
                
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }
    
    /**
     * Update task status (leader or assigned user)
     */
    public function updateStatus(Request $request, $projectId, $taskId)
    {
        // Debug logging
        \Log::info('UpdateStatus called', [
            'projectId' => $projectId,
            'taskId' => $taskId,
            'userId' => Auth::id(),
            'requestData' => $request->all()
        ]);
        
        if (!$this->taskService->canAccessTask($taskId, Auth::id())) {
            \Log::warning('Access denied to task', ['taskId' => $taskId, 'userId' => Auth::id()]);
            abort(403, 'Anda tidak memiliki akses ke task ini.');
        }
        
        $validated = $request->validate([
            'status' => 'required|in:todo,in_progress,review,done',
        ]);
        
        try {
            $task = $this->taskService->updateStatus($taskId, $validated['status']);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Status task berhasil diupdate!',
                    'task' => $task,
                ]);
            }
            
            return back()->with('success', 'Status task berhasil diupdate!');
                
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
    
    /**
     * Update task status (simple version for My Tasks)
     */
    public function updateTaskStatus(Request $request, $taskId)
    {
        if (!$this->taskService->canAccessTask($taskId, Auth::id())) {
            abort(403, 'Anda tidak memiliki akses ke task ini.');
        }
        
        $validated = $request->validate([
            'status' => 'required|in:todo,in_progress,review,done',
        ]);
        
        try {
            // Log untuk debugging
            Log::info('Attempting to update task status', [
                'task_id' => $taskId,
                'from_user' => Auth::id(),
                'new_status' => $validated['status'],
            ]);
            
            $task = $this->taskService->updateStatus($taskId, $validated['status']);
            
            Log::info('Task status updated successfully', [
                'task_id' => $taskId,
                'new_status' => $task->status,
            ]);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Status task berhasil diupdate!',
                    'task' => $task,
                ]);
            }
            
            return back()->with('success', 'Status task berhasil diupdate!');
                
        } catch (\Exception $e) {
            Log::error('Failed to update task status in controller', [
                'task_id' => $taskId,
                'user_id' => Auth::id(),
                'status' => $validated['status'],
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
    
    /**
     * Show Kanban board (redirect to tasks index for now)
     */
    public function kanban($projectId)
    {
        // Redirect to tasks index instead since kanban view doesn't exist yet
        return redirect()->route('admin.projects.tasks.index', $projectId);
    }
    
    /**
     * My tasks page
     */
    public function myTasks()
    {
        $tasks = $this->taskService->getMemberTasks(Auth::id());
        $statistics = [
            'total' => $tasks->count(),
            'todo' => $tasks->where('status', Task::STATUS_TODO)->count(),
            'in_progress' => $tasks->where('status', Task::STATUS_IN_PROGRESS)->count(),
            'review' => $tasks->where('status', Task::STATUS_REVIEW)->count(),
            'done' => $tasks->where('status', Task::STATUS_DONE)->count(),
            'overdue' => $tasks->where('status', Task::STATUS_OVERDUE)->count(),
        ];
        
        return view('tasks.my-tasks', compact('tasks', 'statistics'));
    }
    
    /**
     * Get subtasks for a task (AJAX endpoint)
     */
    public function getSubtasks($taskId)
    {
        try {
            // Find task by task_id
            $task = Task::with(['subtasks' => function($query) {
                $query->orderBy('priority', 'desc')->orderBy('created_at', 'asc');
            }])->where('task_id', $taskId)->firstOrFail();
            
            // Check if user can access this task
            $user = Auth::user();
            if ($user->role !== 'admin' && $user->role !== 'leader') {
                return response()->json(['error' => 'Unauthorized access'], 403);
            }
            
            // Check project access
            if ($task->project) {
                if (!$this->canViewProject($task->project)) {
                    return response()->json(['error' => 'No access to this project'], 403);
                }
            }
            
            // Format subtasks for frontend
            $subtasks = $task->subtasks->map(function ($subtask) {
                return [
                    'id' => $subtask->subtask_id,
                    'title' => $subtask->title,
                    'description' => $subtask->description,
                    'is_completed' => $subtask->is_completed,
                    'priority' => $subtask->priority ?? 'medium',
                    'created_at' => $subtask->created_at->format('M j, Y'),
                    'updated_at' => $subtask->updated_at->format('M j, Y g:i A')
                ];
            });
            
            Log::info('Subtasks loaded successfully', [
                'task_id' => $task->task_id,
                'subtask_count' => $subtasks->count(),
                'user_id' => Auth::id()
            ]);
            
            return response()->json([
                'success' => true,
                'subtasks' => $subtasks,
                'task_id' => $task->task_id,
                'task_title' => $task->title
            ]);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Task not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error fetching subtasks', [
                'task_id' => $taskId,
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to load subtasks: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's active task (for work timer)
     */
    public function getUserActiveTask()
    {
        try {
            $user = Auth::user();
            
            // First, check if user has a task in_progress
            $task = Task::where('assigned_to', $user->user_id)
                ->where('status', Task::STATUS_IN_PROGRESS)
                ->first();
            
            // If no in_progress task, get first todo task
            if (!$task) {
                $task = Task::where('assigned_to', $user->user_id)
                    ->where('status', Task::STATUS_TODO)
                    ->orderBy('priority', 'desc')
                    ->orderBy('created_at', 'asc')
                    ->first();
            }
            
            if (!$task) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada task yang tersedia untuk dikerjakan'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'task_id' => $task->task_id,
                'task_title' => $task->title,
                'status' => $task->status
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get active task: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if user can view project
     */
    private function canViewProject($project)
    {
        $user = Auth::user();
        
        if ($project->leader_id === $user->user_id) return true;
        if ($project->members()->where('user_id', $user->user_id)->exists()) return true;
        if ($user->role === 'admin') return true;
        
        return false;
    }
}
