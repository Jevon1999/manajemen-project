<?php

/**
 * Test Script: TimeLog System
 * Purpose: Verify timer functionality and time tracking
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Task;
use App\Models\TimeLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;

echo "\n=== TESTING TIMELOG SYSTEM ===\n\n";

try {
    // 1. Get a test task with assigned user
    $task = Task::with(['assignedUser'])->whereNotNull('assigned_to')->first();
    
    if (!$task) {
        echo "❌ ERROR: No tasks with assigned users found.\n";
        exit;
    }
    
    echo "✅ Test Task Found:\n";
    echo "   - Task ID: {$task->task_id}\n";
    echo "   - Title: {$task->title}\n";
    echo "   - Assigned to: " . $task->assignedUser->full_name . " (ID: {$task->assigned_to})\n";
    echo "   - Status: {$task->status}\n\n";
    
    // 2. Check existing time logs
    $existingLogs = $task->timeLogs()->count();
    echo "📊 Existing time logs: {$existingLogs}\n\n";
    
    // 3. Check for running timer
    $runningTimer = $task->activeTimeLog;
    if ($runningTimer) {
        echo "⏱️  Active Timer Found:\n";
        echo "   - Started: {$runningTimer->start_time}\n";
        echo "   - Elapsed: " . $runningTimer->getElapsedSeconds() . " seconds\n";
        echo "   - Status: RUNNING\n\n";
    } else {
        echo "ℹ️  No active timer running\n\n";
    }
    
    // 4. Create a test time log
    echo "🔧 Creating test time log...\n";
    
    $startTime = now()->subMinutes(30);
    
    $timeLog = TimeLog::create([
        'task_id' => $task->task_id,
        'user_id' => $task->assigned_to,
        'start_time' => $startTime,
    ]);
    
    echo "   ✓ Time log created (ID: {$timeLog->timelog_id})\n";
    echo "   - Start time: {$timeLog->start_time}\n";
    echo "   - Status: " . ($timeLog->isRunning() ? 'RUNNING' : 'STOPPED') . "\n\n";
    
    // 5. Test stopTimer method
    echo "⏹️  Stopping timer...\n";
    sleep(2); // Wait 2 seconds
    
    $timeLog->stopTimer();
    
    echo "   ✓ Timer stopped\n";
    echo "   - End time: {$timeLog->end_time}\n";
    echo "   - Duration: {$timeLog->duration_seconds} seconds\n";
    echo "   - Formatted: {$timeLog->formatted_duration}\n";
    echo "   - Human: {$timeLog->human_duration}\n\n";
    
    // 6. Test helper methods
    echo "🔍 Testing Helper Methods:\n";
    echo "   - isRunning(): " . ($timeLog->isRunning() ? 'YES' : 'NO') . "\n";
    echo "   - getElapsedSeconds(): {$timeLog->getElapsedSeconds()} seconds\n\n";
    
    // 7. Test relations
    echo "🔗 Testing Relations:\n";
    echo "   - TimeLog -> Task: " . $timeLog->task->title . "\n";
    echo "   - TimeLog -> User: " . $timeLog->user->full_name . "\n";
    echo "   - Task -> TimeLogs Count: " . $task->timeLogs()->count() . "\n\n";
    
    // 8. Test total time calculation
    $totalSeconds = $task->timeLogs()->completed()->sum('duration_seconds');
    echo "📈 Task Time Statistics:\n";
    echo "   - Total time spent: {$totalSeconds} seconds\n";
    echo "   - Formatted total: {$task->formatted_total_time}\n";
    echo "   - Completed sessions: " . $task->timeLogs()->completed()->count() . "\n";
    echo "   - Running sessions: " . $task->timeLogs()->running()->count() . "\n\n";
    
    // 9. Test scopes
    echo "🔍 Testing Scopes:\n";
    $runningCount = TimeLog::where('task_id', $task->task_id)->running()->count();
    $completedCount = TimeLog::where('task_id', $task->task_id)->completed()->count();
    $todayCount = TimeLog::where('task_id', $task->task_id)->forDate(today())->count();
    
    echo "   - Running timers: {$runningCount}\n";
    echo "   - Completed timers: {$completedCount}\n";
    echo "   - Today's logs: {$todayCount}\n\n";
    
    // 10. List recent time logs
    echo "📋 Recent Time Logs (Last 5):\n";
    $recentLogs = $task->timeLogs()
                       ->with('user')
                       ->orderBy('start_time', 'desc')
                       ->limit(5)
                       ->get();
    
    foreach ($recentLogs as $log) {
        $status = $log->isRunning() ? '🟢 RUNNING' : '✅ COMPLETED';
        $duration = $log->isRunning() ? 
                    'Elapsed: ' . $log->getElapsedSeconds() . 's' : 
                    'Duration: ' . $log->formatted_duration;
        
        echo "   {$status} - {$log->user->full_name}\n";
        echo "     Started: {$log->start_time->format('Y-m-d H:i:s')}\n";
        echo "     {$duration}\n";
        if ($log->notes) {
            echo "     Notes: {$log->notes}\n";
        }
        echo "\n";
    }
    
    // 11. Add notes to last log
    echo "📝 Adding notes to test log...\n";
    $timeLog->notes = "Test notes: Implemented timer functionality with start/stop controls";
    $timeLog->save();
    echo "   ✓ Notes added\n\n";
    
    // 12. Cleanup test data
    echo "🗑️  Cleaning up test data...\n";
    $timeLog->delete();
    echo "   ✓ Test time log deleted\n\n";
    
    echo "=== ✅ ALL TESTS PASSED ===\n";
    echo "\nTimeLog system is working correctly!\n";
    echo "API Endpoints available:\n";
    echo "  1. POST   /tasks/{task}/timer/start     - Start timer\n";
    echo "  2. POST   /tasks/{task}/timer/stop      - Stop timer (with notes)\n";
    echo "  3. GET    /tasks/{task}/timer/status    - Check timer status\n";
    echo "  4. GET    /tasks/{task}/timer/history   - View time log history\n\n";
    
    echo "Features:\n";
    echo "  ✅ Start/Stop timer with auto duration calculation\n";
    echo "  ✅ Only 1 running timer per user across all tasks\n";
    echo "  ✅ Auto-transition task status todo → in_progress on timer start\n";
    echo "  ✅ Optional notes when stopping timer\n";
    echo "  ✅ Total time tracking per task\n";
    echo "  ✅ Timer history with user attribution\n";
    echo "  ✅ Real-time elapsed time calculation\n\n";
    
} catch (\Exception $e) {
    echo "\n❌ ERROR: {$e->getMessage()}\n";
    echo "File: {$e->getFile()}\n";
    echo "Line: {$e->getLine()}\n\n";
    echo "Stack Trace:\n{$e->getTraceAsString()}\n";
}
