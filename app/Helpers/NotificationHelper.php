<?php

namespace App\Helpers;

use App\Models\Notification;
use App\Models\User;
use App\Models\Task;
use App\Models\Card;
use Illuminate\Support\Facades\Log;

class NotificationHelper
{
    /**
     * Send task assigned notification
     * @param Task|Card $task
     */
    public static function taskAssigned($task, User $assignedTo, User $assignedBy)
    {
        try {
            Notification::create([
                'user_id' => $assignedTo->user_id,
                'type' => Notification::TYPE_TASK_ASSIGNED,
                'title' => 'Task Baru Ditugaskan',
                'message' => "{$assignedBy->display_name} menugaskan task '{$task->title}' kepada Anda",
                'data' => [
                    'task_id' => $task->task_id,
                    'task_title' => $task->title,
                    'assigned_by' => $assignedBy->display_name,
                    'project_name' => $task->project->name ?? 'Unknown Project',
                ],
            ]);
            
            Log::info("Task assigned notification sent", [
                'task_id' => $task->task_id,
                'assigned_to' => $assignedTo->user_id,
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to send task assigned notification: " . $e->getMessage());
        }
    }
    
    /**
     * Send task status changed notification
     * @param Task|Card $task
     */
    public static function taskStatusChanged($task, $oldStatus, $newStatus, User $changedBy)
    {
        try {
            // Notify assigned user if different from who changed it
            if ($task->assigned_to && $task->assigned_to != $changedBy->user_id) {
                $assignedUser = User::find($task->assigned_to);
                if ($assignedUser) {
                    Notification::create([
                        'user_id' => $assignedUser->user_id,
                        'type' => Notification::TYPE_TASK_STATUS_CHANGED,
                        'title' => 'Status Task Berubah',
                        'message' => "{$changedBy->display_name} mengubah status task '{$task->title}' dari {$oldStatus} ke {$newStatus}",
                        'data' => [
                            'task_id' => $task->task_id,
                            'task_title' => $task->title,
                            'old_status' => $oldStatus,
                            'new_status' => $newStatus,
                            'changed_by' => $changedBy->display_name,
                            'project_name' => $task->project->name ?? 'Unknown Project',
                        ],
                    ]);
                }
            }
            
            // Notify project leader
            if ($task->project && $task->project->leader_id && $task->project->leader_id != $changedBy->user_id) {
                Notification::create([
                    'user_id' => $task->project->leader_id,
                    'type' => Notification::TYPE_TASK_STATUS_CHANGED,
                    'title' => 'Status Task Berubah',
                    'message' => "Task '{$task->title}' berubah status dari {$oldStatus} ke {$newStatus}",
                    'data' => [
                        'task_id' => $task->task_id,
                        'task_title' => $task->title,
                        'old_status' => $oldStatus,
                        'new_status' => $newStatus,
                        'changed_by' => $changedBy->display_name,
                        'project_name' => $task->project->name ?? 'Unknown Project',
                    ],
                ]);
            }
            
            Log::info("Task status changed notification sent", [
                'task_id' => $task->task_id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to send task status changed notification: " . $e->getMessage());
        }
    }
    
    /**
     * Send task approved notification
     * @param Task|Card $task
     */
    public static function taskApproved($task, User $approvedBy, $message = null)
    {
        try {
            if ($task->assigned_to) {
                $assignedUser = User::find($task->assigned_to);
                if ($assignedUser) {
                    Notification::create([
                        'user_id' => $assignedUser->user_id,
                        'type' => Notification::TYPE_TASK_APPROVED,
                        'title' => '✅ Task Disetujui!',
                        'message' => $message ?? "{$approvedBy->display_name} menyetujui task '{$task->title}'. Kerja bagus!",
                        'data' => [
                            'task_id' => $task->task_id,
                            'task_title' => $task->title,
                            'approved_by' => $approvedBy->display_name,
                            'project_name' => $task->project->name ?? 'Unknown Project',
                        ],
                    ]);
                }
            }
            
            Log::info("Task approved notification sent", ['task_id' => $task->task_id]);
        } catch (\Exception $e) {
            Log::error("Failed to send task approved notification: " . $e->getMessage());
        }
    }
    
    /**
     * Send task rejected notification
     * @param Task|Card $task
     */
    public static function taskRejected($task, User $rejectedBy, $reason = null)
    {
        try {
            if ($task->assigned_to) {
                $assignedUser = User::find($task->assigned_to);
                if ($assignedUser) {
                    $message = "{$rejectedBy->display_name} menolak task '{$task->title}'";
                    if ($reason) {
                        $message .= ". Alasan: {$reason}";
                    }
                    
                    Notification::create([
                        'user_id' => $assignedUser->user_id,
                        'type' => Notification::TYPE_TASK_REJECTED,
                        'title' => 'Task Ditolak',
                        'message' => $message,
                        'data' => [
                            'task_id' => $task->task_id,
                            'task_title' => $task->title,
                            'rejected_by' => $rejectedBy->display_name,
                            'reason' => $reason,
                            'project_name' => $task->project->name ?? 'Unknown Project',
                        ],
                    ]);
                }
            }
            
            Log::info("Task rejected notification sent", ['task_id' => $task->task_id]);
        } catch (\Exception $e) {
            Log::error("Failed to send task rejected notification: " . $e->getMessage());
        }
    }
    
    /**
     * Send deadline reminder notification (updated for Card model)
     * @param Card $task
     * @param User $user User to notify
     * @param int $daysLeft Days left until deadline
     */
    public static function deadlineReminder($task, User $user, $daysLeft = 1)
    {
        try {
            $message = $daysLeft == 0 
                ? "Task '{$task->card_name}' deadline hari ini!" 
                : "Task '{$task->card_name}' deadline dalam {$daysLeft} hari";
            
            Notification::create([
                'user_id' => $user->user_id,
                'type' => Notification::TYPE_TASK_DEADLINE,
                'title' => '⏰ Deadline Reminder',
                'message' => $message,
                'data' => [
                    'card_id' => $task->card_id,
                    'task_title' => $task->card_name,
                    'due_date' => $task->due_date,
                    'days_left' => $daysLeft,
                    'priority' => $task->priority,
                    'status' => $task->status,
                ],
            ]);
            
            Log::info("Deadline reminder notification sent", [
                'card_id' => $task->card_id,
                'user_id' => $user->user_id,
                'days_left' => $daysLeft
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to send deadline reminder notification: " . $e->getMessage());
        }
    }
    
    /**
     * Send task overdue notification (updated for Card model)
     * @param Card $task
     * @param User $user User to notify
     * @param int $daysOverdue Days overdue
     */
    public static function taskOverdue($task, User $user, $daysOverdue = 0)
    {
        try {
            $message = $daysOverdue == 0
                ? "Task '{$task->card_name}' sudah melewati deadline"
                : "Task '{$task->card_name}' terlambat {$daysOverdue} hari";
            
            Notification::create([
                'user_id' => $user->user_id,
                'type' => 'task_overdue',
                'title' => '🚨 Task Terlambat!',
                'message' => $message,
                'data' => [
                    'card_id' => $task->card_id,
                    'task_title' => $task->card_name,
                    'due_date' => $task->due_date,
                    'days_overdue' => $daysOverdue,
                    'priority' => $task->priority,
                    'status' => $task->status,
                ],
            ]);
            
            Log::info("Task overdue notification sent", [
                'card_id' => $task->card_id,
                'user_id' => $user->user_id,
                'days_overdue' => $daysOverdue
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to send task overdue notification: " . $e->getMessage());
        }
    }
    
    /**
     * Send critical overdue notification (escalation)
     * @param Card $task
     * @param User $user User to notify
     * @param int $daysOverdue Days overdue
     */
    public static function taskCriticalOverdue($task, User $user, $daysOverdue)
    {
        try {
            $urgencyLevel = $daysOverdue >= 7 ? '🔴 URGENT' : '🟠 CRITICAL';
            
            Notification::create([
                'user_id' => $user->user_id,
                'type' => 'task_critical_overdue',
                'title' => "{$urgencyLevel} Task Sangat Terlambat!",
                'message' => "Task '{$task->card_name}' terlambat {$daysOverdue} hari! Segera ambil tindakan!",
                'data' => [
                    'card_id' => $task->card_id,
                    'task_title' => $task->card_name,
                    'due_date' => $task->due_date,
                    'days_overdue' => $daysOverdue,
                    'priority' => $task->priority,
                    'status' => $task->status,
                    'urgency_level' => $daysOverdue >= 7 ? 'urgent' : 'critical',
                ],
            ]);
            
            Log::warning("Critical overdue notification sent", [
                'card_id' => $task->card_id,
                'user_id' => $user->user_id,
                'days_overdue' => $daysOverdue
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to send critical overdue notification: " . $e->getMessage());
        }
    }
    
    /**
     * Send comment added notification
     * @param Task|Card $task
     */
    public static function commentAdded($task, User $commenter, $comment)
    {
        try {
            // Notify assigned user if different from commenter
            if ($task->assigned_to && $task->assigned_to != $commenter->user_id) {
                $assignedUser = User::find($task->assigned_to);
                if ($assignedUser) {
                    Notification::create([
                        'user_id' => $assignedUser->user_id,
                        'type' => Notification::TYPE_TASK_COMMENT,
                        'title' => 'Komentar Baru',
                        'message' => "{$commenter->display_name} menambahkan komentar pada task '{$task->title}'",
                        'data' => [
                            'task_id' => $task->task_id,
                            'task_title' => $task->title,
                            'commenter' => $commenter->display_name,
                            'comment' => substr($comment, 0, 100),
                            'project_name' => $task->project->name ?? 'Unknown Project',
                        ],
                    ]);
                }
            }
            
            Log::info("Comment notification sent", ['task_id' => $task->task_id]);
        } catch (\Exception $e) {
            Log::error("Failed to send comment notification: " . $e->getMessage());
        }
    }
    
    /**
     * Send work session reminder notification
     * @param Task|Card $task
     */
    public static function workSessionReminder(User $user, $task)
    {
        try {
            Notification::create([
                'user_id' => $user->user_id,
                'type' => 'work_session_reminder',
                'title' => '⏱️ Reminder: Log Waktu Kerja',
                'message' => "Jangan lupa log waktu kerja untuk task '{$task->title}'",
                'data' => [
                    'task_id' => $task->task_id,
                    'task_title' => $task->title,
                    'project_name' => $task->project->name ?? 'Unknown Project',
                ],
            ]);
            
            Log::info("Work session reminder sent", ['user_id' => $user->user_id, 'task_id' => $task->task_id]);
        } catch (\Exception $e) {
            Log::error("Failed to send work session reminder: " . $e->getMessage());
        }
    }
    
    /**
     * Send project leader assignment notification
     */
    public static function projectLeaderAssigned($project, User $leader, User $assignedBy)
    {
        try {
            Notification::create([
                'user_id' => $leader->user_id,
                'type' => 'project_assigned',
                'title' => '🎯 Anda Ditunjuk Sebagai Leader Project',
                'message' => "{$assignedBy->full_name} menunjuk Anda sebagai leader untuk project '{$project->project_name}'",
                'data' => [
                    'project_id' => $project->project_id,
                    'project_name' => $project->project_name,
                    'assigned_by' => $assignedBy->full_name,
                    'role' => 'project_manager',
                ],
            ]);
            
            Log::info("Project leader notification sent", [
                'project_id' => $project->project_id,
                'leader_id' => $leader->user_id,
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to send project leader notification: " . $e->getMessage());
        }
    }
    
    /**
     * Notify leader about extension request
     * 
     * @param \App\Models\ExtensionRequest $request Extension request
     * @param int $leaderId Leader user ID
     * @param int $requesterId Requester user ID
     * @return \App\Models\Notification|null
     */
    public static function extensionRequested($request, int $leaderId, int $requesterId)
    {
        try {
            $requester = User::find($requesterId);
            
            // Get entity (card or task)
            if ($request->entity_type === 'task' && $request->task) {
                $entity = $request->task;
                $entityTitle = $entity->title;
                $entityId = $entity->task_id;
                $entityType = 'task';
            } else {
                $entity = $request->card;
                $entityTitle = $entity->card_title;
                $entityId = $entity->card_id;
                $entityType = 'card';
            }
            
            $extensionDays = $request->getExtensionDays();
            
            return Notification::create([
                'user_id' => $leaderId,
                'type' => 'extension_requested',
                'title' => '⏰ Permintaan Perpanjangan Deadline',
                'message' => "{$requester->full_name} meminta perpanjangan deadline {$extensionDays} hari untuk task '{$entityTitle}'",
                'data' => [
                    'request_id' => $request->id,
                    'entity_type' => $entityType,
                    'entity_id' => $entityId,
                    'entity_title' => $entityTitle,
                    'requester_id' => $requesterId,
                    'requester_name' => $requester->full_name,
                    'old_deadline' => $request->old_deadline->format('Y-m-d'),
                    'requested_deadline' => $request->requested_deadline->format('Y-m-d'),
                    'extension_days' => $extensionDays,
                    'reason' => $request->reason,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to send extension requested notification: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Notify developer that extension is approved
     * 
     * @param \App\Models\ExtensionRequest $request Extension request
     * @param int $developerId Developer user ID
     * @param int $approverId Approver user ID
     * @return \App\Models\Notification|null
     */
    public static function extensionApproved($request, int $developerId, int $approverId)
    {
        try {
            $approver = User::find($approverId);
            
            // Get entity (card or task)
            if ($request->entity_type === 'task' && $request->task) {
                $entity = $request->task;
                $entityTitle = $entity->title;
                $entityId = $entity->task_id;
                $entityType = 'task';
            } else {
                $entity = $request->card;
                $entityTitle = $entity->card_title;
                $entityId = $entity->card_id;
                $entityType = 'card';
            }
            
            return Notification::create([
                'user_id' => $developerId,
                'type' => 'extension_approved',
                'title' => '✅ Perpanjangan Deadline Disetujui',
                'message' => "{$approver->full_name} menyetujui perpanjangan deadline untuk task '{$entityTitle}' hingga " . $request->requested_deadline->format('d M Y'),
                'data' => [
                    'request_id' => $request->id,
                    'entity_type' => $entityType,
                    'entity_id' => $entityId,
                    'entity_title' => $entityTitle,
                    'approver_id' => $approverId,
                    'approver_name' => $approver->full_name,
                    'new_deadline' => $request->requested_deadline->format('Y-m-d'),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to send extension approved notification: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Notify developer that extension is rejected
     * 
     * @param \App\Models\ExtensionRequest $request Extension request
     * @param int $developerId Developer user ID
     * @param int $rejecterId Rejecter user ID
     * @return \App\Models\Notification|null
     */
    public static function extensionRejected($request, int $developerId, int $rejecterId)
    {
        try {
            $rejecter = User::find($rejecterId);
            
            // Get entity (card or task)
            if ($request->entity_type === 'task' && $request->task) {
                $entity = $request->task;
                $entityTitle = $entity->title;
                $entityId = $entity->task_id;
                $entityType = 'task';
            } else {
                $entity = $request->card;
                $entityTitle = $entity->card_title;
                $entityId = $entity->card_id;
                $entityType = 'card';
            }
            
            return Notification::create([
                'user_id' => $developerId,
                'type' => 'extension_rejected',
                'title' => '❌ Perpanjangan Deadline Ditolak',
                'message' => "{$rejecter->full_name} menolak perpanjangan deadline untuk task '{$entityTitle}'. Alasan: {$request->rejection_reason}",
                'data' => [
                    'request_id' => $request->id,
                    'entity_type' => $entityType,
                    'entity_id' => $entityId,
                    'entity_title' => $entityTitle,
                    'rejecter_id' => $rejecterId,
                    'rejecter_name' => $rejecter->full_name,
                    'rejection_reason' => $request->rejection_reason,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to send extension rejected notification: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Notify team member that project is completed
     * 
     * @param \App\Models\Project $project Project
     * @param int $recipientId Recipient user ID
     * @param int $completedBy Leader who completed the project
     * @return \App\Models\Notification|null
     */
    public static function projectCompleted($project, int $recipientId, int $completedBy)
    {
        try {
            $leader = User::find($completedBy);
            
            return Notification::create([
                'user_id' => $recipientId,
                'type' => 'project_completed',
                'title' => '🎉 Project Selesai!',
                'message' => "{$leader->full_name} menyelesaikan project '{$project->project_name}'. Terima kasih atas kontribusi Anda!",
                'data' => [
                    'project_id' => $project->project_id,
                    'project_name' => $project->project_name,
                    'completed_by' => $leader->full_name,
                    'completed_at' => now()->toDateTimeString(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to send project completed notification: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Notify user that task has been created and assigned to them
     * 
     * @param \App\Models\Card $task Task
     * @param int $assignedToId User being assigned
     * @param int $createdBy Leader who created the task
     * @return \App\Models\Notification|null
     */
    public static function taskCreatedAndAssigned($task, int $assignedToId, int $createdBy)
    {
        try {
            $creator = User::find($createdBy);
            
            return Notification::create([
                'user_id' => $assignedToId,
                'type' => 'task_assigned',
                'title' => '📋 Task Baru Ditugaskan',
                'message' => "{$creator->full_name} menugaskan task '{$task->card_title}' kepada Anda",
                'data' => [
                    'card_id' => $task->card_id,
                    'task_title' => $task->card_title,
                    'priority' => $task->priority,
                    'due_date' => $task->due_date,
                    'created_by' => $creator->full_name,
                    'board_id' => $task->board_id,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to send task assigned notification: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Notify leader when task status is updated by team member
     * 
     * @param \App\Models\Card $task Task
     * @param string $oldStatus Old status
     * @param string $newStatus New status
     * @param int $updatedBy User who updated
     * @param int $leaderId Project leader ID
     * @return \App\Models\Notification|null
     */
    public static function taskStatusUpdated($task, string $oldStatus, string $newStatus, int $updatedBy, int $leaderId)
    {
        try {
            if ($updatedBy === $leaderId) {
                return null; // Don't notify leader of their own changes
            }
            
            $updater = User::find($updatedBy);
            
            return Notification::create([
                'user_id' => $leaderId,
                'type' => 'task_status_updated',
                'title' => '🔄 Status Task Berubah',
                'message' => "{$updater->full_name} mengubah status task '{$task->card_title}' dari {$oldStatus} ke {$newStatus}",
                'data' => [
                    'card_id' => $task->card_id,
                    'task_title' => $task->card_title,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'updated_by' => $updater->full_name,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to send task status updated notification: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Notify team member added to project
     * 
     * @param \App\Models\Project $project Project
     * @param int $memberId New member user ID
     * @param int $addedBy Leader who added the member
     * @param string $role Member role
     * @return \App\Models\Notification|null
     */
    public static function memberAddedToProject($project, int $memberId, int $addedBy, string $role)
    {
        try {
            $leader = User::find($addedBy);
            
            return Notification::create([
                'user_id' => $memberId,
                'type' => 'member_added',
                'title' => '👥 Ditambahkan ke Project',
                'message' => "{$leader->full_name} menambahkan Anda ke project '{$project->project_name}' sebagai {$role}",
                'data' => [
                    'project_id' => $project->project_id,
                    'project_name' => $project->project_name,
                    'role' => $role,
                    'added_by' => $leader->full_name,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to send member added notification: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Notify team member removed from project
     * 
     * @param \App\Models\Project $project Project
     * @param int $memberId Removed member user ID
     * @param int $removedBy Leader who removed the member
     * @return \App\Models\Notification|null
     */
    public static function memberRemovedFromProject($project, int $memberId, int $removedBy)
    {
        try {
            $leader = User::find($removedBy);
            
            return Notification::create([
                'user_id' => $memberId,
                'type' => 'member_removed',
                'title' => '👋 Dihapus dari Project',
                'message' => "{$leader->full_name} menghapus Anda dari project '{$project->project_name}'",
                'data' => [
                    'project_id' => $project->project_id,
                    'project_name' => $project->project_name,
                    'removed_by' => $leader->full_name,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to send member removed notification: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Notify when someone comments on a task you're involved in
     * 
     * @param \App\Models\Card $task Task
     * @param \App\Models\CardComment $comment Comment
     * @param int $recipientId User to notify
     * @return \App\Models\Notification|null
     */
    public static function newCommentOnTask($task, $comment, int $recipientId)
    {
        try {
            // Don't notify the commenter
            if ($comment->user_id === $recipientId) {
                return null;
            }
            
            $commenter = User::find($comment->user_id);
            
            return Notification::create([
                'user_id' => $recipientId,
                'type' => 'task_comment',
                'title' => '💬 Komentar Baru',
                'message' => "{$commenter->full_name} mengomentari task '{$task->card_title}'",
                'data' => [
                    'card_id' => $task->card_id,
                    'task_title' => $task->card_title,
                    'comment_id' => $comment->comment_id,
                    'comment_preview' => substr($comment->comment, 0, 100),
                    'commenter' => $commenter->full_name,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to send new comment notification: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Notify when task deadline is changed
     * 
     * @param \App\Models\Card $task Task
     * @param string $oldDeadline Old deadline
     * @param string $newDeadline New deadline
     * @param int $recipientId User to notify
     * @param int $changedBy User who changed
     * @return \App\Models\Notification|null
     */
    public static function taskDeadlineChanged($task, $oldDeadline, $newDeadline, int $recipientId, int $changedBy)
    {
        try {
            $changer = User::find($changedBy);
            
            return Notification::create([
                'user_id' => $recipientId,
                'type' => 'deadline_changed',
                'title' => '📅 Deadline Berubah',
                'message' => "{$changer->full_name} mengubah deadline task '{$task->card_title}'",
                'data' => [
                    'card_id' => $task->card_id,
                    'task_title' => $task->card_title,
                    'old_deadline' => $oldDeadline,
                    'new_deadline' => $newDeadline,
                    'changed_by' => $changer->full_name,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to send deadline changed notification: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Notify when subtask is completed
     * 
     * @param \App\Models\Card $task Parent task
     * @param \App\Models\Subtask $subtask Subtask
     * @param int $leaderId Project leader ID
     * @param int $completedBy User who completed
     * @return \App\Models\Notification|null
     */
    public static function subtaskCompleted($task, $subtask, int $leaderId, int $completedBy)
    {
        try {
            if ($completedBy === $leaderId) {
                return null;
            }
            
            $completer = User::find($completedBy);
            
            return Notification::create([
                'user_id' => $leaderId,
                'type' => 'subtask_completed',
                'title' => '✅ Subtask Selesai',
                'message' => "{$completer->full_name} menyelesaikan subtask '{$subtask->subtask_title}' pada task '{$task->card_title}'",
                'data' => [
                    'card_id' => $task->card_id,
                    'task_title' => $task->card_title,
                    'subtask_id' => $subtask->subtask_id,
                    'subtask_title' => $subtask->subtask_title,
                    'completed_by' => $completer->full_name,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to send subtask completed notification: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Notify when work session starts
     * 
     * @param \App\Models\Card $task Task
     * @param int $leaderId Project leader ID
     * @param int $userId User who started work
     * @return \App\Models\Notification|null
     */
    public static function workSessionStarted($task, int $leaderId, int $userId)
    {
        try {
            if ($userId === $leaderId) {
                return null;
            }
            
            $worker = User::find($userId);
            
            return Notification::create([
                'user_id' => $leaderId,
                'type' => 'work_started',
                'title' => '▶️ Mulai Bekerja',
                'message' => "{$worker->full_name} mulai bekerja pada task '{$task->card_title}'",
                'data' => [
                    'card_id' => $task->card_id,
                    'task_title' => $task->card_title,
                    'started_by' => $worker->full_name,
                    'started_at' => now()->toDateTimeString(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to send work session started notification: " . $e->getMessage());
            return null;
        }
    }
}

