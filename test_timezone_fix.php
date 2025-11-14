<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Carbon\Carbon;

echo "=== TIMEZONE & DATE CHECK ===\n\n";

// Check config timezone
$configTimezone = config('app.timezone');
echo "Config Timezone: {$configTimezone}\n";

// Check current time
$now = Carbon::now($configTimezone);
echo "Current Time ({$configTimezone}): {$now->toDateTimeString()}\n";

// Check today
$today = Carbon::now($configTimezone)->startOfDay();
echo "Today (start of day): {$today->toDateTimeString()}\n";
echo "Today (date only): {$today->toDateString()}\n\n";

// Compare with old method
$oldToday = Carbon::today();
echo "Old Carbon::today(): {$oldToday->toDateString()}\n";
echo "Old timezone: {$oldToday->timezone->getName()}\n\n";

// Simulated user check
echo "=== SIMULATED API RESPONSE ===\n";

$user_id = 9; // Worker2
$todayTotal = \App\Models\WorkSession::where('user_id', $user_id)
    ->whereDate('work_date', $today)
    ->where('status', 'completed')
    ->sum('duration_seconds');

echo "User ID: {$user_id}\n";
echo "Today Total: {$todayTotal}s (" . gmdate('H:i:s', $todayTotal) . ")\n";

$remaining = max(0, 28800 - $todayTotal);
$remainingFormatted = sprintf('%02d:%02d', floor($remaining / 3600), floor(($remaining % 3600) / 60));

echo "Remaining: {$remaining}s ({$remainingFormatted})\n";
echo "Limit Reached: " . ($todayTotal >= 28800 ? 'YES' : 'NO') . "\n\n";

echo "✅ User should be able to start work: " . ($todayTotal < 28800 ? 'YES' : 'NO') . "\n";

echo "\n=== END ===\n";
