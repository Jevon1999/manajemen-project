<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WorkSession;
use Carbon\Carbon;

class CleanupStaleSessions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'work:cleanup-stale-sessions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto-cancel work sessions that are older than 24 hours and still active';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for stale work sessions...');
        
        // Find active sessions older than 24 hours
        $threshold = Carbon::now('Asia/Jakarta')->subHours(24);
        
        $staleSessions = WorkSession::where('status', 'active')
            ->where('started_at', '<', $threshold)
            ->get();
        
        if ($staleSessions->isEmpty()) {
            $this->info('No stale sessions found.');
            return 0;
        }
        
        $count = 0;
        
        foreach ($staleSessions as $session) {
            // Calculate age
            $ageInHours = Carbon::parse($session->started_at)->diffInHours(Carbon::now('Asia/Jakarta'));
            
            // Auto-cancel the session
            $session->stopped_at = Carbon::now('Asia/Jakarta');
            $session->duration_seconds = 0; // Invalid session
            $session->status = 'cancelled';
            $session->notes = "Auto-cancelled: Session was active for {$ageInHours} hours (stale session cleanup)";
            $session->save();
            
            $count++;
            
            $this->line("Cancelled session #{$session->session_id} (User ID: {$session->user_id}, Age: {$ageInHours}h)");
        }
        
        $this->info("\n✓ Successfully cancelled {$count} stale session(s).");
        
        return 0;
    }
}

