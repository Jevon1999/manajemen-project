<?php

namespace App\Services;

use App\Models\Card;
use App\Models\User;
use App\Mail\TaskComplianceReminder;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

/**
 * Task Notification Service
 * 
 * Handles all task-related notifications:
 * - Daily reminders for time logging
 * - Daily reminders for progress updates
 * - Approval notifications for leaders
 * - Overdue task alerts
 */
class TaskNotificationService
{
    /**
     * Send time log reminder to developer
     */
    public function sendTimeLogReminder(User $user, Card $task)
    {
        try {
            $issues = ['No time logged today'];
            
            Mail::to($user->email)->send(
                new TaskComplianceReminder($user, $task, $issues, 'time_log')
            );
            
            Log::info("Time log reminder sent to {$user->email} for task {$task->card_id}");
            
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send time log reminder: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send daily comment reminder to developer
     */
    public function sendDailyCommentReminder(User $user, Card $task)
    {
        try {
            $issues = ['Daily progress update required'];
            
            Mail::to($user->email)->send(
                new TaskComplianceReminder($user, $task, $issues, 'daily_comment')
            );
            
            Log::info("Daily comment reminder sent to {$user->email} for task {$task->card_id}");
            
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send daily comment reminder: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send approval pending notification to leader/admin
     */
    public function sendApprovalPendingNotification(Card $task)
    {
        try {
            // Get leader of the project or fallback to admin
            $project = $task->board->project ?? null;
            
            if (!$project) {
                Log::warning("Task {$task->card_id} has no associated project");
                return false;
            }
            
            // Get project leader first
            $leader = $project->leader;
            
            // If no leader, get all admins
            if (!$leader) {
                $admins = User::where('role', 'admin')
                             ->where('status', 'active')
                             ->get();
                             
                foreach ($admins as $admin) {
                    Mail::to($admin->email)->send(
                        new TaskComplianceReminder($admin, $task, [], 'approval_pending')
                    );
                }
                
                Log::info("Approval pending notifications sent to all admins for task {$task->card_id}");
            } else {
                Mail::to($leader->email)->send(
                    new TaskComplianceReminder($leader, $task, [], 'approval_pending')
                );
                
                Log::info("Approval pending notification sent to leader {$leader->email} for task {$task->card_id}");
            }
            
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send approval pending notification: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send overdue task alert
     */
    public function sendOverdueAlert(User $user, Card $task)
    {
        try {
            $daysOverdue = now()->diffInDays($task->due_date, false);
            $issues = [
                "Task is " . abs($daysOverdue) . " day(s) overdue",
                "Please update status or request extension"
            ];
            
            Mail::to($user->email)->send(
                new TaskComplianceReminder($user, $task, $issues, 'overdue')
            );
            
            Log::info("Overdue alert sent to {$user->email} for task {$task->card_id}");
            
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send overdue alert: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send compliance summary to developer
     */
    public function sendComplianceSummary(User $user, array $tasks)
    {
        try {
            // Group tasks by issue type
            $tasksNeedingTimeLog = [];
            $tasksNeedingComment = [];
            $overdueTasks = [];
            
            foreach ($tasks as $task) {
                if (!$task->hasTimeLogToday()) {
                    $tasksNeedingTimeLog[] = $task;
                }
                if ($task->needsDailyUpdate()) {
                    $tasksNeedingComment[] = $task;
                }
                if ($task->due_date && $task->due_date->isPast()) {
                    $overdueTasks[] = $task;
                }
            }
            
            // Send individual reminders for each issue type
            foreach ($tasksNeedingTimeLog as $task) {
                $this->sendTimeLogReminder($user, $task);
            }
            
            foreach ($tasksNeedingComment as $task) {
                $this->sendDailyCommentReminder($user, $task);
            }
            
            foreach ($overdueTasks as $task) {
                $this->sendOverdueAlert($user, $task);
            }
            
            Log::info("Compliance summary sent to {$user->email}");
            
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send compliance summary: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send daily reminders to all developers with active tasks
     */
    public function sendDailyReminders()
    {
        $sent = 0;
        $failed = 0;
        
        // Get all active tasks
        $activeTasks = Card::where('is_active', true)
                          ->with(['assignments.user'])
                          ->get();
        
        foreach ($activeTasks as $task) {
            $developer = $task->assignments->first()?->user;
            
            if (!$developer) {
                continue;
            }
            
            $needsReminder = false;
            
            // Check if needs time log
            if (!$task->hasTimeLogToday()) {
                if ($this->sendTimeLogReminder($developer, $task)) {
                    $sent++;
                    $needsReminder = true;
                } else {
                    $failed++;
                }
            }
            
            // Check if needs daily comment
            if ($task->needsDailyUpdate()) {
                if ($this->sendDailyCommentReminder($developer, $task)) {
                    $sent++;
                    $needsReminder = true;
                } else {
                    $failed++;
                }
            }
            
            // Check if overdue
            if ($task->due_date && $task->due_date->isPast()) {
                if ($this->sendOverdueAlert($developer, $task)) {
                    $sent++;
                } else {
                    $failed++;
                }
            }
        }
        
        Log::info("Daily reminders sent: {$sent} successful, {$failed} failed");
        
        return [
            'sent' => $sent,
            'failed' => $failed,
            'total_tasks' => $activeTasks->count(),
        ];
    }
    
    /**
     * Send approval reminders to leaders/admins
     */
    public function sendApprovalReminders()
    {
        $sent = 0;
        $failed = 0;
        
        // Get all tasks pending approval
        $pendingTasks = Card::pendingApproval()->get();
        
        foreach ($pendingTasks as $task) {
            if ($this->sendApprovalPendingNotification($task)) {
                $sent++;
            } else {
                $failed++;
            }
        }
        
        Log::info("Approval reminders sent: {$sent} successful, {$failed} failed");
        
        return [
            'sent' => $sent,
            'failed' => $failed,
            'pending_tasks' => $pendingTasks->count(),
        ];
    }
    
    /**
     * Send task approved notification to developer
     */
    public function sendTaskApprovedNotification(User $developer, Card $task, User $approver)
    {
        try {
            // TODO: Create separate email template for approval notification
            Log::info("Task {$task->card_id} approved by {$approver->full_name}, notifying {$developer->email}");
            
            // For now, just log. Implement email in next iteration
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send task approved notification: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send task rejected notification to developer
     */
    public function sendTaskRejectedNotification(User $developer, Card $task, User $rejector, string $reason)
    {
        try {
            // TODO: Create separate email template for rejection notification
            Log::info("Task {$task->card_id} rejected by {$rejector->full_name}, notifying {$developer->email}");
            
            // For now, just log. Implement email in next iteration
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send task rejected notification: " . $e->getMessage());
            return false;
        }
    }
}
