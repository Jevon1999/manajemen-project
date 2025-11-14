<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Services\TaskBusinessRulesService;
use App\Services\TaskNotificationService;
use App\Models\Card;
use App\Models\User;

class EnforceTaskBusinessRules extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:enforce-rules 
                            {--report : Generate compliance report}
                            {--notify : Send notifications to non-compliant users}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enforce task business rules: time tracking, daily comments, and approvals';

    protected $businessRulesService;
    protected $notificationService;

    public function __construct(
        TaskBusinessRulesService $businessRulesService,
        TaskNotificationService $notificationService
    ) {
        parent::__construct();
        $this->businessRulesService = $businessRulesService;
        $this->notificationService = $notificationService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 Enforcing Task Business Rules...');
        $this->newLine();
        
        // Reset daily flags
        $this->info('📋 Resetting daily flags...');
        $updates = $this->businessRulesService->enforceBusinessRules();
        $this->line("   ✓ Reset {$updates['time_log_flags_reset']} time log flags");
        $this->line("   ✓ Flagged {$updates['overdue_tasks_flagged']} overdue tasks");
        $this->newLine();
        
        // Generate compliance report if requested
        if ($this->option('report')) {
            $this->generateComplianceReport();
        }
        
        // Send notifications if requested
        if ($this->option('notify')) {
            $this->sendComplianceNotifications();
        }
        
        $this->info('✅ Business rules enforcement completed!');
        return Command::SUCCESS;
    }
    
    /**
     * Generate and display compliance report
     */
    protected function generateComplianceReport()
    {
        $this->info('📊 Generating Compliance Report...');
        $this->newLine();
        
        $report = $this->businessRulesService->getComplianceReport();
        
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Active Tasks', $report['total_active_tasks']],
                ['Compliant Tasks', $report['compliant_tasks']],
                ['Non-Compliant Tasks', $report['non_compliant_tasks']],
            ]
        );
        
        if ($report['non_compliant_tasks'] > 0) {
            $this->warn('⚠️  Non-Compliant Tasks:');
            $this->newLine();
            
            $nonCompliantData = [];
            foreach ($report['tasks'] as $taskReport) {
                if (!$taskReport['compliant']) {
                    $task = $taskReport['task'];
                    $developer = $task->assignments->first()?->user;
                    
                    $nonCompliantData[] = [
                        $task->card_id,
                        $task->card_title,
                        $developer ? $developer->full_name : 'Unassigned',
                        implode(', ', $taskReport['issues']),
                    ];
                }
            }
            
            $this->table(
                ['ID', 'Task', 'Developer', 'Issues'],
                $nonCompliantData
            );
        } else {
            $this->info('✅ All active tasks are compliant!');
        }
        
        $this->newLine();
    }
    
    /**
     * Send notifications to non-compliant users
     */
    protected function sendComplianceNotifications()
    {
        $this->info('📧 Sending compliance notifications...');
        $this->newLine();
        
        // Send daily reminders for time logging and progress updates
        $this->line('   Sending daily reminders...');
        $dailyResults = $this->notificationService->sendDailyReminders();
        $this->line("   ✓ Sent {$dailyResults['sent']} daily reminders ({$dailyResults['failed']} failed)");
        
        // Send approval reminders to leaders
        $this->line('   Sending approval reminders...');
        $approvalResults = $this->notificationService->sendApprovalReminders();
        $this->line("   ✓ Sent {$approvalResults['sent']} approval reminders ({$approvalResults['failed']} failed)");
        
        $this->newLine();
        $this->info("📊 Total notifications sent: " . ($dailyResults['sent'] + $approvalResults['sent']));
        $this->newLine();
    }
}
