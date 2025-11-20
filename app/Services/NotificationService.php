<?php

namespace App\Services;

use App\Models\User;
use App\Models\Card;
use App\Models\Project;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

class NotificationService
{
    /**
     * Send task assignment notification
     */
    public function notifyTaskAssignment(Card $task, User $assignedUser, User $assignedBy)
    {
        try {
            // Create in-app notification
            DB::table('notifications')->insert([
                'user_id' => $assignedUser->user_id,
                'type' => 'task_assigned',
                'title' => 'New Task Assigned',
                'message' => "You have been assigned to task: {$task->title}",
                'data' => json_encode([
                    'task_id' => $task->card_id,
                    'task_title' => $task->title,
                    'assigned_by' => $assignedBy->name,
                    'project_name' => $task->board->project->name
                ]),
                'read_at' => null,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Send email notification (optional)
            if ($assignedUser->email_notifications ?? true) {
                // Mail::to($assignedUser)->send(new TaskAssignedMail($task, $assignedBy));
            }

            Log::info('Task assignment notification sent', [
                'task_id' => $task->card_id,
                'assigned_to' => $assignedUser->user_id,
                'assigned_by' => $assignedBy->user_id
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send task assignment notification: ' . $e->getMessage());
        }
    }

    /**
     * Send task status change notification
     */
    public function notifyTaskStatusChange(Card $task, $oldStatus, $newStatus, User $changedBy)
    {
        try {
            // Notify project managers and assigned users
            $usersToNotify = collect();
            
            // Add assigned users
            $usersToNotify = $usersToNotify->merge(
                $task->assignments()->with('user')->get()->pluck('user')
            );
            
            // Add project managers
            $projectManagers = DB::table('project_members')
                ->join('users', 'project_members.user_id', '=', 'users.user_id')
                ->where('project_members.project_id', $task->board->project_id)
                ->where('project_members.role', 'project_manager')
                ->where('users.user_id', '!=', $changedBy->user_id) // Don't notify the person who made the change
                ->select('users.*')
                ->get();
            
            $usersToNotify = $usersToNotify->merge($projectManagers);
            
            // Remove duplicates
            $usersToNotify = $usersToNotify->unique('user_id');
            
            foreach ($usersToNotify as $user) {
                DB::table('notifications')->insert([
                    'user_id' => $user->user_id,
                    'type' => 'task_status_changed',
                    'title' => 'Task Status Updated',
                    'message' => "Task '{$task->title}' status changed from {$oldStatus} to {$newStatus}",
                    'data' => json_encode([
                        'task_id' => $task->card_id,
                        'task_title' => $task->title,
                        'old_status' => $oldStatus,
                        'new_status' => $newStatus,
                        'changed_by' => $changedBy->name,
                        'project_name' => $task->board->project->name
                    ]),
                    'read_at' => null,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            Log::info('Task status change notifications sent', [
                'task_id' => $task->card_id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'notified_users' => $usersToNotify->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send task status change notifications: ' . $e->getMessage());
        }
    }

    /**
     * Send overdue task notifications
     */
    public function notifyOverdueTasks()
    {
        try {
            $overdueTasks = Card::where('due_date', '<', now())
                ->where('status', '!=', 'done')
                ->with(['assignments.user', 'board.project'])
                ->get();

            foreach ($overdueTasks as $task) {
                foreach ($task->assignments as $assignment) {
                    // Check if notification was already sent today
                    $existingNotification = DB::table('notifications')
                        ->where('user_id', $assignment->user_id)
                        ->where('type', 'task_overdue')
                        ->whereRaw("JSON_EXTRACT(data, '$.task_id') = ?", [$task->card_id])
                        ->whereDate('created_at', today())
                        ->exists();

                    if (!$existingNotification) {
                        DB::table('notifications')->insert([
                            'user_id' => $assignment->user_id,
                            'type' => 'task_overdue',
                            'title' => 'Overdue Task',
                            'message' => "Task '{$task->title}' is overdue",
                            'data' => json_encode([
                                'task_id' => $task->card_id,
                                'task_title' => $task->title,
                                'due_date' => $task->due_date,
                                'project_name' => $task->board->project->name
                            ]),
                            'read_at' => null,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
                }
            }

            Log::info('Overdue task notifications processed', [
                'overdue_tasks_count' => $overdueTasks->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send overdue task notifications: ' . $e->getMessage());
        }
    }

    /**
     * Get user notifications
     */
    public function getUserNotifications(User $user, $limit = 10)
    {
        return DB::table('notifications')
            ->where('user_id', $user->user_id)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($notification) {
                $notification->data = json_decode($notification->data, true);
                return $notification;
            });
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($notificationId, User $user)
    {
        return DB::table('notifications')
            ->where('id', $notificationId)
            ->where('user_id', $user->user_id)
            ->update(['read_at' => now()]);
    }

    /**
     * Send project completion notification to admin
     */
    public function notifyProjectCompletion(Project $project, User $completedBy)
    {
        try {
            // Get all admin users
            $adminUsers = User::where('role', 'admin')->get();

            foreach ($adminUsers as $admin) {
                DB::table('notifications')->insert([
                    'user_id' => $admin->user_id,
                    'type' => 'project_completed',
                    'title' => 'Project Completed',
                    'message' => "Project '{$project->name}' has been completed by {$completedBy->name}",
                    'data' => json_encode([
                        'project_id' => $project->project_id,
                        'project_name' => $project->name,
                        'completed_by' => $completedBy->name,
                        'completed_by_id' => $completedBy->user_id,
                        'completion_date' => now()->toDateTimeString(),
                        'project_start_date' => $project->start_date,
                        'project_end_date' => $project->end_date
                    ]),
                    'read_at' => null,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            Log::info('Project completion notification sent to admin', [
                'project_id' => $project->project_id,
                'completed_by' => $completedBy->user_id,
                'admin_count' => $adminUsers->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send project completion notification: ' . $e->getMessage());
        }
    }
}