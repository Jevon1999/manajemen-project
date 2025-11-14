<?php

namespace App\Services;

use App\Models\Card;
use App\Models\User;
use App\Models\TimeLog;
use App\Models\Comment;
use App\Helpers\NotificationHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Task Business Rules Service
 * 
 * Implements the 5 core business rules:
 * 1. One active task per developer
 * 2. Mandatory time tracking
 * 3. Daily progress comments required
 * 4. Approval required before completion
 * 5. Priority-based task assignment
 */
class TaskBusinessRulesService
{
    /**
     * Rule 1: Assign task to developer (with validation)
     * 
     * @param Card $task Task to assign
     * @param int $userId User ID
     * @return array Result array
     */
    public function assignTaskToDeveloper(Card $task, $userId)
    {
        /** @var Card $task */
        /** @var int $userId */
        
        DB::beginTransaction();
        
        try {
            // Check if user already has active task
            if (!Card::canUserTakeNewTask($userId)) {
                $activeTask = Card::getActiveTaskForUser($userId);
                throw new \Exception(
                    "Developer already has an active task: {$activeTask->card_title}. " .
                    "Complete it before starting a new one."
                );
            }
            
            // Calculate assignment score
            $task->calculateAssignmentScore();
            
            // Create assignment
            $task->assignments()->create([
                'user_id' => $userId,
                'assigned_at' => now(),
            ]);
            
            // Start work automatically
            $task->startWork($userId);
            
            DB::commit();
            
            Log::info("Task {$task->card_id} assigned to user {$userId}");
            
            return [
                'success' => true,
                'message' => 'Task assigned successfully',
                'task' => $task->fresh(),
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to assign task: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Rule 2: Log time for task (mandatory before updates)
     * 
     * @param Card $task Task to log time for
     * @param int $userId User ID
     * @param float $hours Hours logged
     * @param string|null $description Optional description
     * @return array Result array
     */
    public function logTime(Card $task, $userId, $hours, $description = null)
    {
        /** @var Card $task */
        /** @var int $userId */
        /** @var float $hours */
        /** @var string|null $description */
        
        try {
            $timeLog = $task->timeLogs()->create([
                'user_id' => $userId,
                'start_time' => now()->subHours($hours),
                'end_time' => now(),
                'hours_logged' => $hours,
                'description' => $description,
            ]);
            
            // Update flag
            $task->update(['has_time_log_today' => true]);
            
            // Update actual hours (using update instead of increment to avoid Intelephense warning)
            $task->actual_hours = ($task->actual_hours ?? 0) + $hours;
            $task->save();
            
            Log::info("Time logged for task {$task->card_id}: {$hours} hours");
            
            return [
                'success' => true,
                'message' => 'Time logged successfully',
                'time_log' => $timeLog,
            ];
            
        } catch (\Exception $e) {
            Log::error("Failed to log time: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Rule 3: Add daily progress comment
     * 
     * @param Card $task Task to add comment to
     * @param int $userId User ID
     * @param string $comment Comment text
     * @return array Result array
     */
    public function addProgressComment(Card $task, $userId, $comment)
    {
        /** @var Card $task */
        /** @var int $userId */
        /** @var string $comment */
        
        try {
            // Check if time logged today
            if (!$task->hasTimeLogToday()) {
                throw new \Exception('You must log time before adding progress comments');
            }
            
            $commentRecord = $task->comments()->create([
                'user_id' => $userId,
                'comment_text' => $comment,
                'created_at' => now(),
            ]);
            
            // Update last progress update
            $task->update(['last_progress_update' => now()]);
            
            Log::info("Progress comment added for task {$task->card_id}");
            
            return [
                'success' => true,
                'message' => 'Progress comment added successfully',
                'comment' => $commentRecord,
            ];
            
        } catch (\Exception $e) {
            Log::error("Failed to add progress comment: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Rule 4: Request approval for task completion
     * 
     * @param Card $task Task to request approval for
     * @param int $userId User ID
     * @return array Result array
     */
    public function requestApproval(Card $task, $userId)
    {
        /** @var Card $task */
        /** @var int $userId */
        
        DB::beginTransaction();
        
        try {
            // Validate time log
            if (!$task->hasTimeLogToday()) {
                throw new \Exception('Time tracking is required before requesting approval');
            }
            
            // Validate daily comment
            if ($task->needsDailyUpdate()) {
                throw new \Exception('Daily progress comment is required before requesting approval');
            }
            
            // Mark as complete (moves to review)
            $task->markAsComplete();
            
            DB::commit();
            
            // Send notification to leader/admin for approval
            if ($task->project && $task->project->leader_id) {
                $leader = User::find($task->project->leader_id);
                $submitter = User::find($userId);
                if ($leader && $submitter) {
                    NotificationHelper::taskStatusChanged($task, 'in_progress', 'review', $submitter);
                }
            }
            
            Log::info("Approval requested for task {$task->card_id}");
            
            return [
                'success' => true,
                'message' => 'Task submitted for approval',
                'task' => $task->fresh(),
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to request approval: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Rule 4: Approve task completion (by leader/admin)
     * 
     * @param Card $task Task to approve
     * @param int $userId User ID (admin/leader)
     * @return array Result array
     */
    public function approveTask(Card $task, $userId)
    {
        /** @var Card $task */
        /** @var int $userId */
        
        try {
            // Check if user has permission (leader or admin)
            $user = User::find($userId);
            if (!in_array($user->role, ['admin', 'leader'])) {
                throw new \Exception('Only admin or leader can approve tasks');
            }
            
            // Approve the task
            $task->approve($userId);
            
            // Send notification to developer
            NotificationHelper::taskApproved($task, $user);
            
            Log::info("Task {$task->card_id} approved by user {$userId}");
            
            return [
                'success' => true,
                'message' => 'Task approved successfully',
                'task' => $task->fresh(),
            ];
            
        } catch (\Exception $e) {
            Log::error("Failed to approve task: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Rule 4: Reject task (send back for revision)
     * 
     * @param Card $task Task to reject
     * @param int $userId User ID (admin/leader)
     * @param string $reason Rejection reason
     * @return array Result array
     */
    public function rejectTask(Card $task, $userId, $reason)
    {
        /** @var Card $task */
        /** @var int $userId */
        /** @var string $reason */
        
        DB::beginTransaction();
        
        try {
            // Check permission
            $user = User::find($userId);
            if (!in_array($user->role, ['admin', 'leader'])) {
                throw new \Exception('Only admin or leader can reject tasks');
            }
            
            // Move back to in progress
            $task->update([
                'status' => 'in_progress',
                'is_active' => true,
            ]);
            
            // Add rejection comment
            $task->comments()->create([
                'user_id' => $userId,
                'comment_text' => "Task rejected: {$reason}",
                'created_at' => now(),
            ]);
            
            DB::commit();
            
            // Send notification to developer
            NotificationHelper::taskRejected($task, $user, $reason);
            
            Log::info("Task {$task->card_id} rejected by user {$userId}");
            
            return [
                'success' => true,
                'message' => 'Task rejected and sent back for revision',
                'task' => $task->fresh(),
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to reject task: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Rule 5: Get next recommended task for developer
     * 
     * @param int $userId User ID
     * @return Card|null Next recommended task or null
     */
    public function getNextRecommendedTask($userId)
    {
        /** @var int $userId */
        
        try {
            // Check if user already has active task
            if (Card::userHasActiveTask($userId)) {
                $activeTask = Card::getActiveTaskForUser($userId);
                return [
                    'success' => false,
                    'message' => 'You already have an active task',
                    'active_task' => $activeTask,
                ];
            }
            
            // Get tasks assigned to user, ready for work
            $recommendedTask = Card::assignedTo($userId)
                ->readyForAssignment()
                ->first();
            
            if (!$recommendedTask) {
                return [
                    'success' => false,
                    'message' => 'No tasks available for assignment',
                ];
            }
            
            return [
                'success' => true,
                'message' => 'Recommended task found',
                'task' => $recommendedTask,
            ];
            
        } catch (\Exception $e) {
            Log::error("Failed to get recommended task: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Check compliance for active tasks
     * 
     * @param Card $task Task to check
     * @return array Compliance result
     */
    public function checkTaskCompliance(Card $task)
    {
        /** @var Card $task */
        
        $issues = [];
        
        if (!$task->is_active) {
            return [
                'compliant' => true,
                'issues' => [],
            ];
        }
        
        // Check time log
        if (!$task->hasTimeLogToday()) {
            $issues[] = 'No time logged today';
        }
        
        // Check daily comment
        if ($task->needsDailyUpdate()) {
            $issues[] = 'Daily progress update required';
        }
        
        return [
            'compliant' => empty($issues),
            'issues' => $issues,
            'task' => $task,
        ];
    }
    
    /**
     * Get compliance report for all active tasks
     * 
     * @return array Compliance report
     */
    public function getComplianceReport()
    {
        $activeTasks = Card::where('is_active', true)->with(['assignments.user'])->get();
        
        $report = [
            'total_active_tasks' => $activeTasks->count(),
            'compliant_tasks' => 0,
            'non_compliant_tasks' => 0,
            'tasks' => [],
        ];
        
        foreach ($activeTasks as $task) {
            $compliance = $this->checkTaskCompliance($task);
            
            if ($compliance['compliant']) {
                $report['compliant_tasks']++;
            } else {
                $report['non_compliant_tasks']++;
            }
            
            $report['tasks'][] = $compliance;
        }
        
        return $report;
    }
    
    /**
     * Auto-update task status based on business rules
     * 
     * @return array Update statistics
     */
    public function enforceBusinessRules()
    {
        $updates = [
            'time_log_flags_reset' => 0,
            'overdue_tasks_flagged' => 0,
        ];
        
        // Reset time log flags daily
        Card::where('has_time_log_today', true)->update(['has_time_log_today' => false]);
        $updates['time_log_flags_reset'] = Card::where('has_time_log_today', true)->count();
        
        // Flag overdue tasks
        $overdueTasks = Card::where('status', 'in_progress')
            ->where('due_date', '<', now())
            ->get();
        
        foreach ($overdueTasks as $task) {
            $task->calculateAssignmentScore(); // Increase priority
        }
        
        $updates['overdue_tasks_flagged'] = $overdueTasks->count();
        
        Log::info("Business rules enforced", $updates);
        
        return $updates;
    }
}
