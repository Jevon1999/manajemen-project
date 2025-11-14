<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Task;
use App\Models\ExtensionRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;

echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║   EXTENSION → REVIEW FLOW TEST                           ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n\n";

// Test Case 1: Find tasks that should be testable
echo "🔍 Searching for test cases...\n\n";

$testCases = [];

// Case 1: Recently approved extensions
$recentApproved = ExtensionRequest::where('status', 'approved')
    ->where('entity_type', 'task')
    ->whereNotNull('task_id')
    ->with('task')
    ->where('reviewed_at', '>', now()->subHours(24))
    ->get();

foreach ($recentApproved as $ext) {
    if ($ext->task) {
        $testCases[] = [
            'type' => 'recently_approved_extension',
            'task' => $ext->task,
            'extension' => $ext,
        ];
    }
}

// Case 2: In-progress tasks that were previously blocked
$previouslyBlocked = Task::where('status', 'in_progress')
    ->whereHas('extensionRequests', function($q) {
        $q->where('status', 'approved');
    })
    ->get();

foreach ($previouslyBlocked as $task) {
    $testCases[] = [
        'type' => 'previously_blocked_now_in_progress',
        'task' => $task,
        'extension' => $task->extensionRequests()->where('status', 'approved')->latest()->first(),
    ];
}

// Case 3: Any in-progress task (control group)
$normalInProgress = Task::where('status', 'in_progress')
    ->where('is_blocked', false)
    ->whereHas('assignedUser')
    ->first();

if ($normalInProgress) {
    $testCases[] = [
        'type' => 'normal_in_progress_control',
        'task' => $normalInProgress,
        'extension' => null,
    ];
}

if (empty($testCases)) {
    echo "❌ No test cases found. Please:\n";
    echo "   1. Create a task with overdue deadline\n";
    echo "   2. Request extension\n";
    echo "   3. Have leader approve it\n";
    echo "   4. Run this test again\n";
    exit(1);
}

echo "✅ Found " . count($testCases) . " test case(s)\n\n";

// Test each case
foreach ($testCases as $index => $testCase) {
    $task = $testCase['task'];
    $extension = $testCase['extension'];
    
    echo "═══════════════════════════════════════════════════════════\n";
    echo "TEST CASE #" . ($index + 1) . ": {$testCase['type']}\n";
    echo "═══════════════════════════════════════════════════════════\n\n";
    
    echo "📋 Task Information:\n";
    echo "   ID: {$task->task_id}\n";
    echo "   Title: " . substr($task->title, 0, 50) . "\n";
    echo "   Status: {$task->status}\n";
    echo "   Is Blocked: " . ($task->is_blocked ? '🚫 YES' : '✅ NO') . "\n";
    if ($task->is_blocked) {
        echo "   Block Reason: {$task->block_reason}\n";
    }
    echo "   Assigned To: {$task->assigned_to}\n";
    echo "   Deadline: {$task->deadline}\n\n";
    
    if ($extension) {
        echo "📝 Extension Request:\n";
        echo "   Status: {$extension->status}\n";
        echo "   Old Deadline: {$extension->old_deadline}\n";
        echo "   New Deadline: {$extension->requested_deadline}\n";
        echo "   Reviewed At: {$extension->reviewed_at}\n\n";
    }
    
    // Check if there's a running timer
    $runningTimer = DB::table('time_logs')
        ->where('task_id', $task->task_id)
        ->where('user_id', $task->assigned_to)
        ->whereNull('end_time')
        ->first();
    
    echo "⏱️ Running Timer: " . ($runningTimer ? '⚠️ YES' : '✅ NO') . "\n";
    if ($runningTimer) {
        echo "   Started: {$runningTimer->start_time}\n";
        echo "   ⚠️ WARNING: Timer must be stopped before completing task\n";
    }
    echo "\n";
    
    // Pre-flight checks
    echo "🔍 Pre-flight Checks:\n";
    $checks = [
        'Status is in_progress' => $task->status === 'in_progress',
        'Task is not blocked' => !$task->is_blocked,
        'User is assigned' => !is_null($task->assigned_to),
        'No running timer' => is_null($runningTimer),
    ];
    
    $allChecksPassed = true;
    foreach ($checks as $check => $passed) {
        echo "   " . ($passed ? '✅' : '❌') . " {$check}\n";
        if (!$passed) {
            $allChecksPassed = false;
        }
    }
    echo "\n";
    
    if (!$allChecksPassed) {
        echo "⚠️ SKIPPING: Pre-flight checks failed\n\n";
        continue;
    }
    
    // Attempt transition to review
    echo "🚀 Attempting Transition to Review...\n\n";
    
    $boardService = new \App\Services\BoardTransitionService();
    $result = $boardService->transitionToReview($task, $task->assigned_to);
    
    echo "📊 Result:\n";
    echo "   Success: " . ($result['success'] ? '✅ YES' : '❌ NO') . "\n";
    echo "   Message: {$result['message']}\n";
    
    if (isset($result['new_status'])) {
        echo "   New Status: {$result['new_status']}\n";
    }
    echo "\n";
    
    // Reload task to verify
    $task->refresh();
    echo "📋 Final Task State:\n";
    echo "   Status: {$task->status}\n";
    echo "   Is Blocked: " . ($task->is_blocked ? '🚫 YES' : '✅ NO') . "\n";
    echo "   Completed At: " . ($task->completed_at ?? 'N/A') . "\n";
    
    // Final verdict
    echo "\n";
    if ($result['success'] && $task->status === 'review') {
        echo "✅ TEST PASSED: Task successfully transitioned to review\n";
    } else {
        echo "❌ TEST FAILED: Task did not transition to review\n";
        echo "   Debug info:\n";
        echo "   - Check logs: storage/logs/laravel.log\n";
        echo "   - Search for: 'TransitionToReview attempt' and task_id={$task->task_id}\n";
    }
    echo "\n";
}

echo "═══════════════════════════════════════════════════════════\n";
echo "TEST SUMMARY\n";
echo "═══════════════════════════════════════════════════════════\n";
echo "Total test cases: " . count($testCases) . "\n";
echo "\n";
echo "💡 Tips:\n";
echo "   - Check Laravel logs for detailed debugging\n";
echo "   - Run: tail -f storage/logs/laravel.log\n";
echo "   - Look for 'TransitionToReview attempt' entries\n";
echo "\n";
