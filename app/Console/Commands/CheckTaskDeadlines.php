<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Card;
use App\Models\Task;
use App\Models\User;
use App\Helpers\NotificationHelper;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CheckTaskDeadlines extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:check-deadlines';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check task deadlines and send notifications (reminders & overdue alerts)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Checking task deadlines...');
        
        try {
            $now = Carbon::now('Asia/Jakarta');
            $stats = [
                'reminders_sent' => 0,
                'overdue_alerts' => 0,
                'escalations_sent' => 0
            ];
            
            // 1. Check for deadline reminders (24 hours before)
            $this->info("\n📅 Checking for upcoming deadlines (Cards)...");
            $stats['reminders_sent'] = $this->sendDeadlineReminders($now);
            
            $this->info("\n📅 Checking for upcoming deadlines (Tasks)...");
            $stats['reminders_sent'] += $this->sendTaskDeadlineReminders($now);
            
            // 2. Check for newly overdue tasks
            $this->info("\n⚠️ Checking for overdue tasks (Cards)...");
            $stats['overdue_alerts'] = $this->sendOverdueAlerts($now);
            
            $this->info("\n⚠️ Checking for overdue tasks (Tasks)...");
            $stats['overdue_alerts'] += $this->sendTaskOverdueAlerts($now);
            
            // 3. Check for escalation (tasks overdue > 3 days)
            $this->info("\n🚨 Checking for critical overdue tasks (Cards)...");
            $stats['escalations_sent'] = $this->sendEscalationAlerts($now);
            
            $this->info("\n🚨 Checking for critical overdue tasks (Tasks)...");
            $stats['escalations_sent'] += $this->sendTaskEscalationAlerts($now);
            
            // Summary
            $this->newLine();
            $this->info('✅ Task deadline check completed!');
            $this->table(
                ['Type', 'Count'],
                [
                    ['Deadline Reminders (24h)', $stats['reminders_sent']],
                    ['Overdue Alerts (New)', $stats['overdue_alerts']],
                    ['Critical Escalations (3+ days)', $stats['escalations_sent']],
                ]
            );
            
            Log::info('Task Deadline Check Completed', $stats);
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('Failed to check deadlines: ' . $e->getMessage());
            Log::error('Task Deadline Check Failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return Command::FAILURE;
        }
    }
    
    /**
     * Send reminders for tasks due in 24 hours
     */
    private function sendDeadlineReminders($now)
    {
        $tomorrow = $now->copy()->addDay();
        $tomorrowStart = $tomorrow->copy()->startOfDay();
        $tomorrowEnd = $tomorrow->copy()->endOfDay();
        
        // Get tasks due tomorrow that are not done/cancelled
        $tasks = Card::whereBetween('due_date', [$tomorrowStart, $tomorrowEnd])
            ->whereNotIn('status', ['done', 'cancelled'])
            ->with(['assignments.user', 'board.project', 'board.project.leader'])
            ->get();
        
        $count = 0;
        
        foreach ($tasks as $task) {
            // Get assigned users
            $assignedUsers = $task->assignments->pluck('user')->filter();
            
            if ($assignedUsers->isEmpty()) {
                continue;
            }
            
            foreach ($assignedUsers as $user) {
                // Send reminder to developer
                NotificationHelper::deadlineReminder($task, $user, 1);
                $count++;
                
                $this->line("  → Reminder sent to {$user->name} for task: {$task->card_name}");
            }
            
            // Also notify leader
            if ($task->board && $task->board->project && $task->board->project->leader) {
                $leader = $task->board->project->leader;
                NotificationHelper::deadlineReminder($task, $leader, 1);
                $this->line("  → Reminder sent to leader {$leader->name}");
            }
        }
        
        return $count;
    }
    
    /**
     * Send alerts for tasks that are now overdue
     */
    private function sendOverdueAlerts($now)
    {
        // Get tasks that became overdue today (due_date was yesterday or earlier)
        $tasks = Card::where('due_date', '<', $now->copy()->startOfDay())
            ->whereNotIn('status', ['done', 'cancelled'])
            ->with(['assignments.user', 'board.project', 'board.project.leader'])
            ->get();
        
        $count = 0;
        
        foreach ($tasks as $task) {
            $daysOverdue = $now->diffInDays(Carbon::parse($task->due_date));
            
            // Auto-block task if not already blocked and not has pending/approved extension
            if (!$task->is_blocked && !$task->hasPendingExtensionRequest() && !$task->hasApprovedExtension()) {
                $task->block("Task overdue since {$daysOverdue} day(s). Please request extension from your leader.");
                $this->warn("  → Task blocked: {$task->card_name} ({$daysOverdue} days overdue)");
            }
            
            // Skip if already sent alert today (check last_overdue_alert_at)
            if ($task->last_overdue_alert_at && 
                Carbon::parse($task->last_overdue_alert_at)->isToday()) {
                continue;
            }
            
            // Get assigned users
            $assignedUsers = $task->assignments->pluck('user')->filter();
            
            if ($assignedUsers->isEmpty()) {
                continue;
            }
            
            foreach ($assignedUsers as $user) {
                // Send overdue alert to developer
                NotificationHelper::taskOverdue($task, $user, $daysOverdue);
                $count++;
                
                $this->line("  → Overdue alert sent to {$user->name} for task: {$task->card_name} ({$daysOverdue} days)");
            }
            
            // Notify leader
            if ($task->board && $task->board->project && $task->board->project->leader) {
                $leader = $task->board->project->leader;
                NotificationHelper::taskOverdue($task, $leader, $daysOverdue);
                $this->line("  → Overdue alert sent to leader {$leader->name}");
            }
            
            // Update last alert timestamp
            $task->last_overdue_alert_at = $now;
            $task->overdue_notification_count++;
            $task->save();
        }
        
        return $count;
    }
    
    /**
     * Send escalation for critical overdue tasks (3+ days)
     */
    private function sendEscalationAlerts($now)
    {
        // Get tasks overdue more than 3 days
        $criticalDate = $now->copy()->subDays(3);
        
        $tasks = Card::where('due_date', '<', $criticalDate)
            ->whereNotIn('status', ['done', 'cancelled'])
            ->with(['assignments.user', 'board.project', 'board.project.leader'])
            ->get();
        
        $count = 0;
        
        foreach ($tasks as $task) {
            $daysOverdue = $now->diffInDays(Carbon::parse($task->due_date));
            
            // Skip if escalation sent in last 24 hours
            if ($task->last_escalation_at && 
                Carbon::parse($task->last_escalation_at)->isToday()) {
                continue;
            }
            
            // Get assigned users
            $assignedUsers = $task->assignments->pluck('user')->filter();
            
            if ($assignedUsers->isEmpty()) {
                continue;
            }
            
            foreach ($assignedUsers as $user) {
                // Send critical alert
                NotificationHelper::taskCriticalOverdue($task, $user, $daysOverdue);
                $count++;
                
                $this->error("  → CRITICAL: Task {$task->card_name} is {$daysOverdue} days overdue!");
            }
            
            // Notify leader and admin
            if ($task->board && $task->board->project && $task->board->project->leader) {
                $leader = $task->board->project->leader;
                NotificationHelper::taskCriticalOverdue($task, $leader, $daysOverdue);
            }
            
            // Notify all admins for critical cases (7+ days)
            if ($daysOverdue >= 7) {
                $admins = User::where('role', 'admin')->get();
                foreach ($admins as $admin) {
                    NotificationHelper::taskCriticalOverdue($task, $admin, $daysOverdue);
                }
                $this->error("  → Escalated to all admins!");
            }
            
            // Update escalation timestamp
            $task->last_escalation_at = $now;
            $task->save();
        }
        
        return $count;
    }
    
    // ========== TASK MODEL METHODS (for tasks table) ==========
    
    /**
     * Send reminders for tasks (Task model) due in 24 hours
     */
    private function sendTaskDeadlineReminders($now)
    {
        $tomorrow = $now->copy()->addDay();
        $tomorrowStart = $tomorrow->copy()->startOfDay();
        $tomorrowEnd = $tomorrow->copy()->endOfDay();
        
        $tasks = Task::whereBetween('deadline', [$tomorrowStart, $tomorrowEnd])
            ->whereNotIn('status', ['done', 'cancelled'])
            ->with(['project', 'assignedUser'])
            ->get();
        
        $count = 0;
        
        foreach ($tasks as $task) {
            if ($task->assignedUser) {
                // Send reminder to assigned user
                $this->line("  → Reminder sent to {$task->assignedUser->name} for task: {$task->title}");
                $count++;
            }
        }
        
        return $count;
    }
    
    /**
     * Send alerts for tasks (Task model) that are now overdue
     */
    private function sendTaskOverdueAlerts($now)
    {
        // Get tasks with deadline before or equal to now
        $tasks = Task::where('deadline', '<=', $now)
            ->whereNotIn('status', ['done', 'cancelled', 'overdue'])
            ->with(['project', 'assignedUser'])
            ->get();
        
        $count = 0;
        
        foreach ($tasks as $task) {
            $daysOverdue = $now->diffInDays(Carbon::parse($task->deadline));
            
            // Change status to overdue
            $task->status = Task::STATUS_OVERDUE;
            $task->save();
            
            // Auto-block task if not already blocked and not has pending/approved extension
            if (!$task->is_blocked && !$task->hasPendingExtensionRequest() && !$task->hasApprovedExtension()) {
                $task->block("Task overdue since {$daysOverdue} day(s). Please request extension from your leader.");
                $this->warn("  → Task status changed to OVERDUE and blocked: {$task->title} ({$daysOverdue} days overdue)");
            }
            
            if ($task->assignedUser) {
                $this->line("  → Overdue alert sent to {$task->assignedUser->name} for task: {$task->title} ({$daysOverdue} days)");
                $count++;
            }
        }
        
        return $count;
    }
    
    /**
     * Send escalation for critical overdue tasks (Task model, 3+ days)
     */
    private function sendTaskEscalationAlerts($now)
    {
        $tasks = Task::where('deadline', '<', $now->copy()->subDays(3))
            ->whereNotIn('status', ['done', 'cancelled'])
            ->with(['project', 'assignedUser'])
            ->get();
        
        $count = 0;
        
        foreach ($tasks as $task) {
            $daysOverdue = $now->diffInDays(Carbon::parse($task->deadline));
            
            if ($task->assignedUser) {
                $this->error("  → Critical escalation sent to {$task->assignedUser->name} for task: {$task->title} ({$daysOverdue} days overdue)");
                $count++;
            }
        }
        
        return $count;
    }
}
