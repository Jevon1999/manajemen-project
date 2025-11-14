<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\WorkSession;
use Carbon\Carbon;

echo "=== WORK SESSIONS CHECK ===\n\n";

$today = Carbon::today();
echo "Today: " . $today->toDateString() . "\n\n";

// Get all today's sessions
$sessions = WorkSession::whereDate('work_date', $today)->get();

echo "Total sessions today: " . $sessions->count() . "\n\n";

if ($sessions->count() > 0) {
    echo "Sessions Detail:\n";
    echo str_repeat("-", 80) . "\n";
    
    foreach ($sessions as $session) {
        echo "ID: {$session->session_id}\n";
        echo "  User: {$session->user_id}\n";
        echo "  Date: {$session->work_date}\n";
        echo "  Duration: {$session->duration_seconds}s (" . gmdate('H:i:s', $session->duration_seconds) . ")\n";
        echo "  Status: {$session->status}\n";
        echo "  Started: {$session->started_at}\n";
        echo "  Stopped: " . ($session->stopped_at ?? 'NULL') . "\n";
        echo str_repeat("-", 80) . "\n";
    }
    
    // Total per user
    echo "\nTotal per User:\n";
    $grouped = $sessions->groupBy('user_id');
    foreach ($grouped as $userId => $userSessions) {
        $total = $userSessions->sum('duration_seconds');
        $totalHours = round($total / 3600, 2);
        $remaining = max(0, 28800 - $total);
        $remainingFormatted = gmdate('H:i', $remaining);
        
        echo "User {$userId}:\n";
        echo "  Total: {$total}s ({$totalHours}h)\n";
        echo "  Remaining: {$remaining}s ({$remainingFormatted})\n";
        echo "  Sessions: {$userSessions->count()}\n\n";
    }
}

// Check yesterday
$yesterday = Carbon::yesterday();
$yesterdaySessions = WorkSession::whereDate('work_date', $yesterday)->get();
echo "\nYesterday ({$yesterday->toDateString()}): {$yesterdaySessions->count()} sessions\n";

if ($yesterdaySessions->count() > 0) {
    $grouped = $yesterdaySessions->groupBy('user_id');
    foreach ($grouped as $userId => $userSessions) {
        $total = $userSessions->sum('duration_seconds');
        $totalHours = round($total / 3600, 2);
        echo "  User {$userId}: {$total}s ({$totalHours}h)\n";
    }
}

echo "\n=== END ===\n";
