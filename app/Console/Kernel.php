<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Reset daily work limit setiap jam 00:00 (midnight)
        $schedule->command('work:reset-daily')
            ->dailyAt('00:00')
            ->timezone('Asia/Jakarta')
            ->runInBackground();
        
        // ✨ NEW: Auto-cleanup stale work sessions (> 24 hours old) every hour
        $schedule->command('work:cleanup-stale-sessions')
            ->hourly()
            ->timezone('Asia/Jakarta')
            ->runInBackground()
            ->onSuccess(function () {
                \Illuminate\Support\Facades\Log::info('Stale session cleanup completed');
            })
            ->onFailure(function () {
                \Illuminate\Support\Facades\Log::error('Stale session cleanup failed');
            });
        
        // ✨ Check task deadlines every day at 08:00 AM
        $schedule->command('tasks:check-deadlines')
            ->dailyAt('08:00')
            ->timezone('Asia/Jakarta')
            ->runInBackground()
            ->onSuccess(function () {
                \Illuminate\Support\Facades\Log::info('Task deadline check completed successfully');
            })
            ->onFailure(function () {
                \Illuminate\Support\Facades\Log::error('Task deadline check failed');
            });
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
