<?php

namespace App\Services;

use App\Models\Task;
use App\Models\TimeLog;
use App\Models\User;
use App\Helpers\NotificationHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Board Transition Service
 * 
 * Handles automatic status transitions for tasks with business rules:
 * - todo → in_progress (when timer starts)
 * - in_progress → review (when user marks as complete)
 * - review → done (when leader approves)
 */
class BoardTransitionService
{
    /**
     * Status constants
     */
    const STATUS_TODO = 'todo';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_REVIEW = 'review';
    const STATUS_DONE = 'done';
    
    /**
     * Valid status transitions
     */
    const VALID_TRANSITIONS = [
        self::STATUS_TODO => [self::STATUS_IN_PROGRESS],
        self::STATUS_IN_PROGRESS => [self::STATUS_TODO, self::STATUS_REVIEW],
        self::STATUS_REVIEW => [self::STATUS_IN_PROGRESS, self::STATUS_DONE],
        self::STATUS_DONE => [], // Cannot transition from done
    ];
    
    /**
     * Check if status transition is valid
     */
    public function isValidTransition(string $fromStatus, string $toStatus): bool
    {
        if (!isset(self::VALID_TRANSITIONS[$fromStatus])) {
            return false;
        }
        
        return in_array($toStatus, self::VALID_TRANSITIONS[$fromStatus]);
    }
    
    /**
     * Transition task to 'in_progress' automatically when timer starts
     * Called automatically by TimeLogController
     */
    public function transitionToInProgress(Task $task): bool
    {
        // Check if task is blocked
        if ($task->is_blocked) {
            throw new \Exception('Task is blocked: ' . ($task->block_reason ?? 'Please request deadline extension from your leader.'));
        }
        
        if ($task->status !== self::STATUS_TODO) {
            return false; // Already in progress or beyond
        }
        
        $task->status = self::STATUS_IN_PROGRESS;
        $task->save();
        
        Log::info("Task {$task->task_id} auto-transitioned to in_progress (timer started)");
        
        return true;
    }
    
    /**
     * Transition task to 'review' when user marks as complete
     * Only task owner can do this
     */
    public function transitionToReview(Task $task, int $userId): array
    {
        // Reload task to get fresh data (in case of recent extension approval)
        $task->refresh();
        
        Log::info("TransitionToReview attempt", [
            'task_id' => $task->task_id,
            'user_id' => $userId,
            'current_status' => $task->status,
            'is_blocked' => $task->is_blocked,
            'block_reason' => $task->block_reason,
        ]);
        
        // Check if task is blocked
        if ($task->is_blocked) {
            Log::warning("Task {$task->task_id} is blocked, cannot transition to review", [
                'block_reason' => $task->block_reason
            ]);
            return [
                'success' => false,
                'message' => 'Task is blocked: ' . ($task->block_reason ?? 'Please request deadline extension from your leader.'),
            ];
        }
        
        // Validate user is assigned to task
        if ($task->assigned_to !== $userId) {
            Log::warning("User {$userId} not assigned to task {$task->task_id}");
            return [
                'success' => false,
                'message' => 'Hanya user yang di-assign yang bisa menyelesaikan task ini.',
            ];
        }
        
        // Validate current status - allow in_progress only
        if ($task->status !== self::STATUS_IN_PROGRESS) {
            Log::warning("Task {$task->task_id} has invalid status for review transition", [
                'current_status' => $task->status,
                'expected' => self::STATUS_IN_PROGRESS
            ]);
            return [
                'success' => false,
                'message' => 'Task harus dalam status "In Progress" untuk bisa diselesaikan. Current status: ' . $task->status,
            ];
        }
        
        // Check if there's a running timer
        $runningTimer = TimeLog::where('task_id', $task->task_id)
                               ->where('user_id', $userId)
                               ->whereNull('end_time')
                               ->first();
        
        if ($runningTimer) {
            Log::warning("Task {$task->task_id} has running timer, cannot transition");
            return [
                'success' => false,
                'message' => 'Timer masih berjalan. Hentikan timer terlebih dahulu sebelum menyelesaikan task.',
            ];
        }
        
        // Transition to review
        $task->status = self::STATUS_REVIEW;
        $task->completed_at = now();
        $task->save();
        
        // Send notification to project leader
        if ($task->project && $task->project->leader_id) {
            $leader = User::find($task->project->leader_id);
            $submitter = User::find($userId);
            if ($leader && $submitter) {
                NotificationHelper::taskStatusChanged($task, 'in_progress', 'review', $submitter);
            }
        }
        
        Log::info("Task {$task->task_id} successfully transitioned to review by user {$userId}");
        
        return [
            'success' => true,
            'message' => 'Task berhasil diselesaikan dan menunggu review dari leader! 🎉',
            'new_status' => self::STATUS_REVIEW,
        ];
    }
    
    /**
     * Transition task to 'done' when leader approves
     * Only project leader can do this
     * 
     * @param Task $task The task to transition
     * @param int $userId The user performing the action
     * @return array Response array with success status and message
     */
    public function transitionToDone(Task $task, int $userId): array
    {
        /** @var Task $task */
        /** @var int $userId */
        
        // Check if task is blocked
        if ($task->is_blocked) {
            return [
                'success' => false,
                'message' => 'Task is blocked: ' . ($task->block_reason ?? 'Cannot complete blocked tasks.'),
            ];
        }
        
        // Check if user is project leader
        if (!$this->isProjectLeader($task->project_id, $userId)) {
            return [
                'success' => false,
                'message' => 'Hanya leader yang bisa approve task.',
            ];
        }
        
        // Validate current status
        if ($task->status !== self::STATUS_REVIEW) {
            return [
                'success' => false,
                'message' => 'Task harus dalam status "Review" untuk bisa di-approve.',
            ];
        }
        
        // Transition to done
        $task->status = self::STATUS_DONE;
        $task->completed_at = now();
        $task->save();
        
        // Send approval notification to task owner
        if ($task->assigned_to) {
            $approver = User::find($userId);
            if ($approver) {
                NotificationHelper::taskApproved($task, $approver);
            }
        }
        
        Log::info("Task {$task->task_id} approved and marked as done by leader {$userId}");
        
        return [
            'success' => true,
            'message' => 'Task berhasil di-approve dan ditandai sebagai selesai! ✅',
            'new_status' => self::STATUS_DONE,
        ];
    }
    
    /**
     * Reject task (send back to in_progress)
     * Only project leader can do this
     * 
     * @param Task $task The task to reject
     * @param int $userId The user performing the action
     * @param string|null $reason Optional rejection reason
     * @return array Response array with success status and message
     */
    public function rejectTask(Task $task, int $userId, ?string $reason = null): array
    {
        /** @var Task $task */
        /** @var int $userId */
        /** @var string|null $reason */
        
        // Check if user is project leader
        if (!$this->isProjectLeader($task->project_id, $userId)) {
            return [
                'success' => false,
                'message' => 'Hanya leader yang bisa reject task.',
            ];
        }
        
        // Validate current status
        if ($task->status !== self::STATUS_REVIEW) {
            return [
                'success' => false,
                'message' => 'Task harus dalam status "Review" untuk bisa di-reject.',
            ];
        }
        
        // Send back to in_progress
        $task->status = self::STATUS_IN_PROGRESS;
        $task->completed_at = null;
        
        // Store rejection reason if provided
        if ($reason) {
            $task->rejection_reason = $reason;
        }
        
        $task->save();
        
        // Send rejection notification to task owner
        if ($task->assigned_to) {
            $rejector = User::find($userId);
            if ($rejector) {
                NotificationHelper::taskRejected($task, $rejector, $reason);
            }
        }
        
        Log::info("Task {$task->task_id} rejected by leader {$userId}", ['reason' => $reason]);
        
        return [
            'success' => true,
            'message' => 'Task dikembalikan ke In Progress. User perlu memperbaiki task ini.',
            'new_status' => self::STATUS_IN_PROGRESS,
        ];
    }
    
    /**
     * Manual status change with validation
     * For admin/leader to manually change status if needed
     * 
     * @param Task $task The task to change
     * @param string $newStatus The new status
     * @param int $userId The user performing the action
     * @return array Response array with success status and message
     */
    public function changeStatus(Task $task, string $newStatus, int $userId): array
    {
        /** @var Task $task */
        /** @var string $newStatus */
        /** @var int $userId */
        
        // Check permissions
        if (!$this->canChangeStatus($task, $userId)) {
            return [
                'success' => false,
                'message' => 'Anda tidak memiliki permission untuk mengubah status task ini.',
            ];
        }
        
        // Validate transition
        if (!$this->isValidTransition($task->status, $newStatus)) {
            return [
                'success' => false,
                'message' => "Transisi dari '{$task->status}' ke '{$newStatus}' tidak valid.",
                'valid_transitions' => self::VALID_TRANSITIONS[$task->status] ?? [],
            ];
        }
        
        $oldStatus = $task->status;
        $task->status = $newStatus;
        
        // Update completed_at for review/done
        if (in_array($newStatus, [self::STATUS_REVIEW, self::STATUS_DONE])) {
            $task->completed_at = now();
        } else {
            $task->completed_at = null;
        }
        
        $task->save();
        
        Log::info("Task {$task->task_id} status changed from {$oldStatus} to {$newStatus} by user {$userId}");
        
        return [
            'success' => true,
            'message' => "Status berhasil diubah dari '{$oldStatus}' ke '{$newStatus}'.",
            'new_status' => $newStatus,
        ];
    }
    
    /**
     * Get available status transitions for a task
     * 
     * @param Task $task The task to check
     * @param int $userId The user ID
     * @return array Available transitions
     */
    public function getAvailableTransitions(Task $task, int $userId): array
    {
        /** @var Task $task */
        /** @var int $userId */
        
        $currentStatus = $task->status;
        $availableTransitions = [];
        
        // For task owner
        if ($task->assigned_to === $userId) {
            if ($currentStatus === self::STATUS_IN_PROGRESS) {
                $availableTransitions[] = [
                    'status' => self::STATUS_REVIEW,
                    'label' => 'Selesai (Kirim ke Review)',
                    'action' => 'complete',
                    'color' => 'purple',
                ];
            }
        }
        
        // For project leader
        if ($this->isProjectLeader($task->project_id, $userId)) {
            if ($currentStatus === self::STATUS_REVIEW) {
                $availableTransitions[] = [
                    'status' => self::STATUS_DONE,
                    'label' => 'Approve (Tandai Selesai)',
                    'action' => 'approve',
                    'color' => 'green',
                ];
                $availableTransitions[] = [
                    'status' => self::STATUS_IN_PROGRESS,
                    'label' => 'Reject (Kembalikan)',
                    'action' => 'reject',
                    'color' => 'red',
                ];
            }
        }
        
        return $availableTransitions;
    }
    
    /**
     * Check if user can change task status
     * 
     * @param Task $task The task to check
     * @param int $userId The user ID
     * @return bool
     */
    private function canChangeStatus(Task $task, int $userId): bool
    {
        /** @var Task $task */
        /** @var int $userId */
        
        // Task owner can change their task
        if ($task->assigned_to === $userId) {
            return true;
        }
        
        // Project leader can change any task in their project
        if ($this->isProjectLeader($task->project_id, $userId)) {
            return true;
        }
        
        // Admin can change any task
        $user = \App\Models\User::find($userId);
        if ($user && $user->role === 'admin') {
            return true;
        }
        
        return false;
    }
    
    /**
     * Check if user is project leader
     * 
     * @param int $projectId Project ID
     * @param int $userId User ID
     * @return bool
     * @phpstan-ignore-next-line
     */
    private function isProjectLeader(int $projectId, int $userId): bool
    {
        /** @var int $projectId */
        /** @var int $userId */
        
        // Check if user is the assigned project leader
        $isProjectLeader = DB::table('projects')
                 ->where('project_id', $projectId)
                 ->where('leader_id', $userId)
                 ->exists();
        
        if ($isProjectLeader) {
            return true;
        }
        
        // Check if user has project_manager role in this project
        $isProjectManager = DB::table('project_members')
                 ->where('project_id', $projectId)
                 ->where('user_id', $userId)
                 ->where('role', 'project_manager')
                 ->exists();
        
        if ($isProjectManager) {
            return true;
        }
        
        // Check if user has admin or leader role in the system
        $user = \App\Models\User::find($userId);
        if ($user && in_array($user->role, ['admin', 'leader'])) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Get status flow description
     */
    public function getStatusFlowDescription(): string
    {
        return "Status Flow:\n" .
               "1. 📋 TODO - Task baru dibuat\n" .
               "2. 🚀 IN PROGRESS - User mulai kerja (auto saat start timer)\n" .
               "3. 👀 REVIEW - User selesai, menunggu approval leader\n" .
               "4. ✅ DONE - Leader approve, task selesai\n\n" .
               "Rules:\n" .
               "- User hanya bisa move in_progress → review (setelah stop timer)\n" .
               "- Leader bisa approve (review → done) atau reject (review → in_progress)\n" .
               "- Task done tidak bisa diubah lagi";
    }
}
