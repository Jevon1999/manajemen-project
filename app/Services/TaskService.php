<?php

namespace App\Services;

use App\Models\Task;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\User;
use App\Events\TaskAssigned;
use App\Events\TaskStatusChanged;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TaskService
{
    /**
     * Create a new task
     */
    public function createTask(array $data)
    {
        try {
            DB::beginTransaction();
            
            // VALIDASI: 1 Task = 1 User (user tidak boleh punya task aktif lain)
            if (isset($data['assigned_to']) && $data['assigned_to']) {
                $this->validateUserAvailability($data['assigned_to'], $data['project_id']);
            }
            
            $task = Task::create([
                'project_id' => $data['project_id'],
                'assigned_to' => $data['assigned_to'] ?? null,
                'created_by' => Auth::id(),
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'status' => $data['status'] ?? Task::STATUS_TODO,
                'priority' => $data['priority'] ?? Task::PRIORITY_MEDIUM,
                'deadline' => $data['deadline'] ?? null,
            ]);
            
            Log::info('Task created', [
                'task_id' => $task->task_id,
                'title' => $task->title,
                'project_id' => $task->project_id,
                'assigned_to' => $task->assigned_to,
                'created_by' => Auth::id(),
            ]);
            
            // Broadcast task assigned event if user is assigned
            if ($task->assigned_to) {
                $assignedUser = User::find($task->assigned_to);
                $assigner = Auth::user();
                if ($assignedUser) {
                    broadcast(new TaskAssigned($task, $assignedUser, $assigner))->toOthers();
                }
            }
            
            DB::commit();
            
            return $task->load(['assignedUser', 'creator', 'project']);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create task: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Update task
     */
    public function updateTask($taskId, array $data)
    {
        try {
            DB::beginTransaction();
            
            $task = Task::findOrFail($taskId);
            
            // VALIDASI: Jika mengubah assigned_to, cek availability user baru
            if (isset($data['assigned_to']) && $data['assigned_to'] != $task->assigned_to) {
                $this->validateUserAvailability($data['assigned_to'], $task->project_id);
            }
            
            $task->update([
                'title' => $data['title'] ?? $task->title,
                'description' => $data['description'] ?? $task->description,
                'assigned_to' => $data['assigned_to'] ?? $task->assigned_to,
                'priority' => $data['priority'] ?? $task->priority,
                'deadline' => $data['deadline'] ?? $task->deadline,
            ]);
            
            Log::info('Task updated', [
                'task_id' => $task->task_id,
                'title' => $task->title,
                'updated_by' => Auth::id(),
            ]);
            
            DB::commit();
            
            return $task->fresh(['assignedUser', 'creator', 'project']);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update task: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Update task status
     */
    public function updateStatus($taskId, $status)
    {
        try {
            DB::beginTransaction();
            
            $task = Task::findOrFail($taskId);
            $oldStatus = $task->status;
            
            // VALIDASI: Task hanya bisa di-approve ke DONE jika project dalam status in_progress
            // User bisa mengirim ke REVIEW kapan saja, tapi hanya bisa DONE jika project aktif
            if ($status === Task::STATUS_DONE) {
                $project = $task->project;
                if ($project->status !== 'in_progress') {
                    throw new \Exception(
                        "Task tidak dapat di-approve ke status Done. Project '{$project->project_name}' tidak dalam status In Progress. " .
                        "Status project saat ini: " . strtoupper($project->status)
                    );
                }
            }
            
            $task->update([
                'status' => $status,
                'completed_at' => $status === Task::STATUS_DONE ? now() : null,
            ]);
            
            Log::info('Task status updated', [
                'task_id' => $task->task_id,
                'old_status' => $oldStatus,
                'new_status' => $status,
                'updated_by' => Auth::id(),
            ]);
            
            // Broadcast task status changed event
            $changedBy = Auth::user();
            broadcast(new TaskStatusChanged($task, $oldStatus, $status, $changedBy))->toOthers();
            
            DB::commit();
            
            return $task->fresh(['assignedUser', 'creator', 'project']);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update task status: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Assign task to user
     */
    public function assignTask($taskId, $userId)
    {
        try {
            DB::beginTransaction();
            
            $task = Task::findOrFail($taskId);
            
            // Verify user is member of the project
            $isMember = ProjectMember::where('project_id', $task->project_id)
                ->where('user_id', $userId)
                ->exists();
            
            if (!$isMember) {
                throw new \Exception('User is not a member of this project.');
            }
            
            $task->update(['assigned_to' => $userId]);
            
            Log::info('Task assigned', [
                'task_id' => $task->task_id,
                'assigned_to' => $userId,
                'assigned_by' => Auth::id(),
            ]);
            
            DB::commit();
            
            return $task->fresh(['assignedUser', 'creator', 'project']);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to assign task: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Delete task
     */
    public function deleteTask($taskId)
    {
        try {
            DB::beginTransaction();
            
            $task = Task::findOrFail($taskId);
            
            Log::info('Task deleted', [
                'task_id' => $task->task_id,
                'title' => $task->title,
                'deleted_by' => Auth::id(),
            ]);
            
            $task->delete();
            
            DB::commit();
            
            return true;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete task: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Get all tasks for a project with filters
     */
    public function getProjectTasks($projectId, array $filters = [])
    {
        $query = Task::where('project_id', $projectId)
            ->with(['assignedUser', 'creator', 'project', 'subtasks']);
        
        // Filter by status
        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }
        
        // Filter by priority
        if (isset($filters['priority']) && $filters['priority'] !== '') {
            $query->where('priority', $filters['priority']);
        }
        
        // Filter by assigned user
        if (isset($filters['assigned_to']) && $filters['assigned_to'] !== '') {
            $query->where('assigned_to', $filters['assigned_to']);
        }
        
        // Filter overdue
        if (isset($filters['overdue']) && $filters['overdue']) {
            $query->overdue();
        }
        
        // Sort
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);
        
        return $query->paginate(15);
    }
    
    /**
     * Get tasks assigned to a specific user
     */
    public function getMemberTasks($userId, $projectId = null)
    {
        $query = Task::where('assigned_to', $userId)
            ->with(['project', 'creator', 'subtasks' => function($query) {
                $query->orderBy('created_at', 'asc');
            }]);
        
        if ($projectId) {
            $query->where('project_id', $projectId);
        }
        
        return $query->orderBy('deadline', 'asc')
            ->orderBy('priority', 'desc')
            ->get();
    }
    
    /**
     * Get task statistics for a project
     */
    public function getProjectTaskStatistics($projectId)
    {
        $tasks = Task::where('project_id', $projectId)->get();
        
        return [
            'total' => $tasks->count(),
            'todo' => $tasks->where('status', Task::STATUS_TODO)->count(),
            'in_progress' => $tasks->where('status', Task::STATUS_IN_PROGRESS)->count(),
            'review' => $tasks->where('status', Task::STATUS_REVIEW)->count(),
            'done' => $tasks->where('status', Task::STATUS_DONE)->count(),
            'overdue' => $tasks->filter->isOverdue()->count(),
            'completion_rate' => $tasks->count() > 0 
                ? round(($tasks->where('status', Task::STATUS_DONE)->count() / $tasks->count()) * 100, 2) 
                : 0,
            'by_priority' => [
                'high' => $tasks->where('priority', Task::PRIORITY_HIGH)->count(),
                'medium' => $tasks->where('priority', Task::PRIORITY_MEDIUM)->count(),
                'low' => $tasks->where('priority', Task::PRIORITY_LOW)->count(),
            ],
        ];
    }
    
    /**
     * Get tasks grouped by status (for Kanban board)
     */
    public function getTasksByStatus($projectId)
    {
        return [
            'todo' => Task::where('project_id', $projectId)
                ->where('status', Task::STATUS_TODO)
                ->with(['assignedUser'])
                ->orderBy('priority', 'desc')
                ->orderBy('deadline', 'asc')
                ->get(),
            
            'in_progress' => Task::where('project_id', $projectId)
                ->where('status', Task::STATUS_IN_PROGRESS)
                ->with(['assignedUser'])
                ->orderBy('priority', 'desc')
                ->orderBy('deadline', 'asc')
                ->get(),
            
            'review' => Task::where('project_id', $projectId)
                ->where('status', Task::STATUS_REVIEW)
                ->with(['assignedUser'])
                ->orderBy('priority', 'desc')
                ->orderBy('deadline', 'asc')
                ->get(),
            
            'done' => Task::where('project_id', $projectId)
                ->where('status', Task::STATUS_DONE)
                ->with(['assignedUser'])
                ->orderBy('completed_at', 'desc')
                ->get(),
        ];
    }
    
    /**
     * Check if user can manage tasks in a project
     */
    public function canManageTasks($projectId, $userId)
    {
        $project = Project::findOrFail($projectId);
        $user = Auth::user();
        
        // Only project leader can manage tasks (not admin)
        // User must be both: 1) leader of this project AND 2) have 'leader' role
        return $project->leader_id === $userId && $user->role === 'leader';
    }
    
    /**
     * Check if user can view/edit a specific task
     */
    public function canAccessTask($taskId, $userId)
    {
        $task = Task::findOrFail($taskId);
        $project = $task->project;
        $user = Auth::user();
        
        // Admin can access all tasks
        if ($user->role === 'admin') {
            return true;
        }
        
        // Leader can access all tasks in their project
        if ($project->leader_id === $userId && $user->role === 'leader') {
            return true;
        }
        
        // Users can access their assigned tasks (including developer/designer)
        if ($task->assigned_to === $userId) {
            return true;
        }
        
        // Developer and Designer cannot access other people's tasks
        if (in_array($user->role, ['developer', 'designer'])) {
            return false;
        }
        
        return false;
    }
    
    /**
     * Validate user availability for task assignment
     * Rule: 1 Task = 1 User (user can only have 1 active task at a time)
     * 
     * @param int $userId
     * @param int $projectId
     * @throws \Exception
     */
    private function validateUserAvailability($userId, $projectId)
    {
        // Check if user already has an active task (status: todo, in_progress, review, or overdue)
        $activeTask = Task::where('assigned_to', $userId)
            ->whereIn('status', [Task::STATUS_TODO, Task::STATUS_IN_PROGRESS, Task::STATUS_REVIEW, Task::STATUS_OVERDUE])
            ->with('project')
            ->first();
        
        if ($activeTask) {
            $taskTitle = $activeTask->title;
            $projectName = $activeTask->project ? $activeTask->project->project_name : 'Unknown Project';
            
            throw new \Exception(
                "User sudah memiliki task aktif '{$taskTitle}' di project '{$projectName}'. " .
                "User hanya dapat memiliki 1 task aktif pada satu waktu. " .
                "Selesaikan task yang ada terlebih dahulu sebelum assign task baru."
            );
        }
    }
    
    /**
     * Get available users for task assignment in a project
     * Excludes users who already have active tasks
     * 
     * @param int $projectId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAvailableUsersForTask($projectId)
    {
        // Get project members who are 'user' role (not leader)
        $projectMembers = ProjectMember::where('project_id', $projectId)
            ->whereHas('user', function($query) {
                $query->where('role', 'user')
                      ->where('status', 'active');
            })
            ->pluck('user_id')
            ->toArray();
        
        if (empty($projectMembers)) {
            return collect([]);
        }
        
        // Get user IDs who have active tasks
        $usersWithActiveTasks = Task::whereIn('status', [Task::STATUS_TODO, Task::STATUS_IN_PROGRESS, Task::STATUS_REVIEW, Task::STATUS_OVERDUE])
            ->whereIn('assigned_to', $projectMembers)
            ->pluck('assigned_to')
            ->toArray();
        
        // Return users who are project members but don't have active tasks
        $availableUserIds = array_diff($projectMembers, $usersWithActiveTasks);
        
        return \App\Models\User::whereIn('user_id', $availableUserIds)
            ->where('status', 'active')
            ->select('user_id', 'full_name', 'username', 'email')
            ->orderBy('full_name')
            ->get();
    }
    
    /**
     * Check if a user has any active tasks
     * 
     * @param int $userId
     * @return bool
     */
    public function userHasActiveTask($userId)
    {
        return Task::where('assigned_to', $userId)
            ->whereIn('status', [Task::STATUS_TODO, Task::STATUS_IN_PROGRESS, Task::STATUS_REVIEW, Task::STATUS_OVERDUE])
            ->exists();
    }
}
