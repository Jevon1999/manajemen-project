<?php

/**
 * Quick Test - Extension Approval Status Change
 * Tests whether status properly changes to in_progress after approval
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Task;
use App\Models\ExtensionRequest;
use Illuminate\Support\Facades\DB;

echo "╔══════════════════════════════════════════════════════════╗\n";
echo "║   TEST: Extension Approval Status Change                ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n\n";

// Find pending extension requests
$pendingRequests = ExtensionRequest::where('status', 'pending')
    ->where('entity_type', 'task')
    ->whereNotNull('task_id')
    ->with('task')
    ->get();

if ($pendingRequests->isEmpty()) {
    echo "ℹ️  No pending extension requests found.\n";
    echo "\nLooking for recently approved requests to verify...\n\n";
    
    // Check recently approved (last hour)
    $recentApproved = ExtensionRequest::where('status', 'approved')
        ->where('entity_type', 'task')
        ->where('reviewed_at', '>', now()->subHour())
        ->with('task')
        ->get();
    
    if ($recentApproved->isEmpty()) {
        echo "❌ No recent approvals to verify.\n";
        echo "\nTo test this fix:\n";
        echo "1. Create a task with past deadline (it will become overdue)\n";
        echo "2. Request extension\n";
        echo "3. Approve the extension\n";
        echo "4. Check if status changes to in_progress\n";
        exit;
    }
    
    echo "✅ Found {$recentApproved->count()} recently approved extension(s)\n\n";
    
    foreach ($recentApproved as $ext) {
        $task = $ext->task;
        
        echo "─────────────────────────────────────────────────────────\n";
        echo "Extension ID: {$ext->id}\n";
        echo "Task ID: {$task->task_id}\n";
        echo "Task Title: " . substr($task->title, 0, 50) . "\n";
        echo "Approved At: {$ext->reviewed_at}\n\n";
        
        echo "Task State:\n";
        echo "  Status: {$task->status} ";
        if ($task->status === 'in_progress') {
            echo "✅\n";
        } else {
            echo "❌ (Expected: in_progress)\n";
        }
        
        echo "  Is Blocked: " . ($task->is_blocked ? '❌ YES' : '✅ NO') . "\n";
        echo "  Block Reason: " . ($task->block_reason ?? '✅ None') . "\n";
        echo "  Deadline: {$task->deadline}\n";
        
        // Verdict
        if ($task->status === 'in_progress' && !$task->is_blocked) {
            echo "\n✅ PASS: Task properly unblocked and status set to in_progress\n";
        } else {
            echo "\n❌ FAIL: Task not in expected state\n";
            if ($task->is_blocked) {
                echo "   Issue: Task is still blocked\n";
            }
            if ($task->status !== 'in_progress') {
                echo "   Issue: Status is '{$task->status}' instead of 'in_progress'\n";
            }
        }
        echo "\n";
    }
    
} else {
    echo "✅ Found {$pendingRequests->count()} pending extension request(s)\n\n";
    echo "These are waiting for approval. To test:\n";
    echo "1. Approve one of these extensions via the UI or API\n";
    echo "2. Re-run this script to verify status changed\n\n";
    
    foreach ($pendingRequests as $ext) {
        $task = $ext->task;
        echo "─────────────────────────────────────────────────────────\n";
        echo "Extension ID: {$ext->id}\n";
        echo "Task ID: {$task->task_id}\n";
        echo "Task Title: " . substr($task->title, 0, 50) . "\n";
        echo "Current Status: {$task->status}\n";
        echo "Is Blocked: " . ($task->is_blocked ? 'YES' : 'NO') . "\n";
        echo "Requested By: User ID {$ext->requested_by}\n";
        echo "Old Deadline: {$ext->old_deadline}\n";
        echo "Requested Deadline: {$ext->requested_deadline}\n";
        echo "\nTo approve via API:\n";
        echo "POST /extension-requests/{$ext->id}/approve\n";
        echo "\n";
    }
}

echo "═══════════════════════════════════════════════════════════\n";
echo "\n💡 Tip: Check logs after approval:\n";
echo "   tail -f storage/logs/laravel.log | grep 'Extension approved'\n";
echo "\n";
