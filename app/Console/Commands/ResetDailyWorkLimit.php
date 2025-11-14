<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WorkSession;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ResetDailyWorkLimit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'work:reset-daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset daily work limit at midnight (automatic cleanup of work sessions)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting daily work limit reset...');
        
        try {
            // 1. Check for any "stuck" active sessions (older than 24 hours)
            $stuckSessions = WorkSession::where('status', 'active')
                ->where('started_at', '<', Carbon::now()->subDay())
                ->get();
            
            if ($stuckSessions->count() > 0) {
                $this->warn("Found {$stuckSessions->count()} stuck active sessions");
                
                foreach ($stuckSessions as $session) {
                    // Auto-complete stuck sessions with 0 duration
                    $session->status = 'completed';
                    $session->stopped_at = $session->started_at;
                    $session->duration_seconds = 0;
                    $session->notes = 'Auto-closed by system (stuck session)';
                    $session->save();
                    
                    $this->line("- Closed stuck session ID: {$session->session_id} for user {$session->user_id}");
                }
            }
            
            // 2. Log daily statistics
            $yesterday = Carbon::yesterday();
            $dailyStats = WorkSession::whereDate('work_date', $yesterday)
                ->selectRaw('COUNT(*) as total_sessions, SUM(duration_seconds) as total_seconds, COUNT(DISTINCT user_id) as active_users')
                ->first();
            
            if ($dailyStats) {
                $totalHours = round($dailyStats->total_seconds / 3600, 2);
                $this->info("Yesterday's Statistics:");
                $this->line("- Total Sessions: {$dailyStats->total_sessions}");
                $this->line("- Active Users: {$dailyStats->active_users}");
                $this->line("- Total Hours Logged: {$totalHours}h");
                
                Log::info('Daily Work Reset', [
                    'date' => $yesterday->toDateString(),
                    'total_sessions' => $dailyStats->total_sessions,
                    'active_users' => $dailyStats->active_users,
                    'total_hours' => $totalHours,
                    'stuck_sessions_closed' => $stuckSessions->count()
                ]);
            }
            
            $this->info('✅ Daily work limit reset completed successfully!');
            $this->newLine();
            $this->info('📊 All users can now start fresh with 8 hours limit for today.');
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('Failed to reset daily work limit: ' . $e->getMessage());
            Log::error('Daily Work Reset Failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return Command::FAILURE;
        }
    }
}
