<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\WorkSession;
use Carbon\Carbon;

$today = Carbon::now('Asia/Jakarta')->startOfDay();

echo "=== Resetting Today's Work Sessions ===\n\n";

// Get all sessions for today
$sessions = WorkSession::whereDate('work_date', $today)->get();

echo "Found {$sessions->count()} session(s) for today ({$today->toDateString()}).\n\n";

if ($sessions->count() > 0) {
    $deleted = 0;
    $cancelled = 0;
    
    foreach ($sessions as $session) {
        if ($session->status === 'active') {
            // Cancel active sessions
            $session->status = 'cancelled';
            $session->stopped_at = Carbon::now('Asia/Jakarta');
            $session->notes = 'Cancelled by admin reset';
            $session->save();
            $cancelled++;
            echo "  ✓ Cancelled active session #{$session->session_id} (User {$session->user_id})\n";
        } else {
            // Delete completed sessions
            $session->delete();
            $deleted++;
            echo "  ✓ Deleted session #{$session->session_id} (User {$session->user_id})\n";
        }
    }
    
    echo "\n=== Summary ===\n";
    echo "Cancelled: {$cancelled} active session(s)\n";
    echo "Deleted: {$deleted} completed session(s)\n";
    echo "\n✅ All users now have 08:00:00 remaining time for today.\n";
} else {
    echo "✅ No sessions to reset. All users already have full 08:00:00 remaining.\n";
}
