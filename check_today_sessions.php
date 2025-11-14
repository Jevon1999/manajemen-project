<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\WorkSession;
use Carbon\Carbon;

$today = Carbon::now('Asia/Jakarta')->startOfDay();

echo "=== Today's Work Sessions (" . $today->toDateString() . ") ===\n\n";

$sessions = WorkSession::whereDate('work_date', $today)->get();

echo "Active Sessions Today: " . $sessions->count() . "\n\n";

if ($sessions->count() > 0) {
    foreach ($sessions as $session) {
        echo "  - Session #{$session->session_id}\n";
        echo "    User ID: {$session->user_id}\n";
        echo "    Status: {$session->status}\n";
        echo "    Duration: " . gmdate('H:i:s', $session->duration_seconds) . "\n";
        echo "    Started: {$session->started_at}\n";
        echo "\n";
    }
} else {
    echo "  No sessions found for today.\n\n";
}

$totalSeconds = $sessions->where('status', 'completed')->sum('duration_seconds');
echo "Total Completed Work Today: " . gmdate('H:i:s', $totalSeconds) . " (" . round($totalSeconds/3600, 2) . "h)\n";

echo "\n=== All Users' Remaining Time ===\n\n";

$users = \App\Models\User::all();
foreach ($users as $user) {
    $todayTotal = WorkSession::where('user_id', $user->user_id)
        ->whereDate('work_date', $today)
        ->where('status', 'completed')
        ->sum('duration_seconds');
    
    $remaining = max(0, 28800 - $todayTotal); // 8 hours = 28800 seconds
    $remainingFormatted = gmdate('H:i:s', $remaining);
    $usedFormatted = gmdate('H:i:s', $todayTotal);
    
    echo "User: {$user->name} (ID: {$user->user_id})\n";
    echo "  Used: {$usedFormatted} | Remaining: {$remainingFormatted}\n\n";
}
