<?php

/**
 * Quick Diagnostic Tool - Check Extension to Review Flow Health
 * 
 * Usage: php check_extension_flow_health.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Task;
use App\Models\ExtensionRequest;
use Illuminate\Support\Facades\DB;

echo "\n";
echo "╔══════════════════════════════════════════════════════════╗\n";
echo "║     EXTENSION TO REVIEW FLOW - HEALTH CHECK             ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n\n";

// Check 1: Recently approved extensions
echo "📊 Check 1: Recently Approved Extensions (Last 7 days)\n";
echo "───────────────────────────────────────────────────────────\n";

$recentApprovals = ExtensionRequest::where('status', 'approved')
    ->where('entity_type', 'task')
    ->where('reviewed_at', '>', now()->subDays(7))
    ->count();

echo "Total approved: {$recentApprovals}\n";

if ($recentApprovals > 0) {
    // Check how many are still blocked
    $stillBlocked = ExtensionRequest::where('status', 'approved')
        ->where('entity_type', 'task')
        ->where('reviewed_at', '>', now()->subDays(7))
        ->whereHas('task', function($q) {
            $q->where('is_blocked', true);
        })
        ->count();
    
    $percentage = $stillBlocked > 0 ? round(($stillBlocked / $recentApprovals) * 100, 1) : 0;
    
    if ($stillBlocked > 0) {
        echo "⚠️  Still blocked: {$stillBlocked} ({$percentage}%)\n";
        echo "   → THIS IS THE BUG - Should be 0!\n";
    } else {
        echo "✅ Still blocked: 0 (0%)\n";
        echo "   → All approved extensions properly unblocked!\n";
    }
} else {
    echo "ℹ️  No recent approvals to check\n";
}

echo "\n";

// Check 2: In-progress tasks with approved extensions
echo "📊 Check 2: In-Progress Tasks with Approved Extensions\n";
echo "───────────────────────────────────────────────────────────\n";

$inProgressWithExtension = Task::where('status', 'in_progress')
    ->whereHas('extensionRequests', function($q) {
        $q->where('status', 'approved');
    })
    ->get();

echo "Total: " . $inProgressWithExtension->count() . "\n";

if ($inProgressWithExtension->count() > 0) {
    $blocked = $inProgressWithExtension->where('is_blocked', true)->count();
    $unblocked = $inProgressWithExtension->where('is_blocked', false)->count();
    
    echo "├─ ✅ Unblocked (ready to complete): {$unblocked}\n";
    echo "└─ ⚠️  Still blocked (BUG!): {$blocked}\n";
    
    if ($blocked > 0) {
        echo "\n⚠️  WARNING: Found {$blocked} task(s) that should be unblocked!\n";
        echo "   Task IDs:\n";
        foreach ($inProgressWithExtension->where('is_blocked', true) as $task) {
            echo "   - {$task->task_id}: {$task->title}\n";
            echo "     Block reason: {$task->block_reason}\n";
        }
    }
} else {
    echo "ℹ️  No in-progress tasks with approved extensions\n";
}

echo "\n";

// Check 3: Tasks stuck at review stage
echo "📊 Check 3: Review Transition Success Rate (Last 24h)\n";
echo "───────────────────────────────────────────────────────────\n";

// Count tasks that reached review in last 24h
$reachedReview = Task::where('status', 'review')
    ->where('completed_at', '>', now()->subDay())
    ->count();

// Count tasks that should be at review but are stuck
$shouldBeReview = Task::where('status', 'in_progress')
    ->where('is_blocked', false)
    ->whereHas('extensionRequests', function($q) {
        $q->where('status', 'approved')
          ->where('reviewed_at', '>', now()->subDay());
    })
    ->count();

echo "Successfully reached review: {$reachedReview}\n";
echo "Potentially stuck (in_progress but ready): {$shouldBeReview}\n";

if ($shouldBeReview > 0) {
    echo "⚠️  Warning: {$shouldBeReview} task(s) might be stuck\n";
} else {
    echo "✅ No tasks appear to be stuck\n";
}

echo "\n";

// Check 4: Database consistency
echo "📊 Check 4: Database Consistency\n";
echo "───────────────────────────────────────────────────────────\n";

// Tasks that are blocked but don't have pending extension
$invalidBlocked = Task::where('is_blocked', true)
    ->where('status', 'in_progress')
    ->whereDoesntHave('extensionRequests', function($q) {
        $q->where('status', 'pending');
    })
    ->count();

if ($invalidBlocked > 0) {
    echo "⚠️  Invalid blocked tasks: {$invalidBlocked}\n";
    echo "   These tasks are blocked but have no pending extension\n";
    echo "   Might be leftover from the bug\n";
} else {
    echo "✅ No invalid blocked tasks found\n";
}

echo "\n";

// Overall Health Score
echo "╔══════════════════════════════════════════════════════════╗\n";
echo "║                    HEALTH SCORE                          ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n\n";

$issues = 0;
$checks = 0;

// Issue 1: Blocked after approval
if ($recentApprovals > 0) {
    $checks++;
    $stillBlocked = ExtensionRequest::where('status', 'approved')
        ->where('entity_type', 'task')
        ->where('reviewed_at', '>', now()->subDays(7))
        ->whereHas('task', function($q) {
            $q->where('is_blocked', true);
        })
        ->count();
    
    if ($stillBlocked > 0) {
        $issues++;
        echo "❌ Issue: Tasks blocked after extension approval\n";
    }
}

// Issue 2: Invalid blocked state
$checks++;
if ($invalidBlocked > 0) {
    $issues++;
    echo "❌ Issue: Invalid blocked task states\n";
}

// Issue 3: Stuck tasks
$checks++;
if ($shouldBeReview > 0) {
    $issues++;
    echo "❌ Issue: Tasks potentially stuck at in_progress\n";
}

if ($issues === 0) {
    echo "✅ ALL SYSTEMS OPERATIONAL\n";
    echo "   No issues detected!\n";
} else {
    echo "\n⚠️  ISSUES DETECTED: {$issues} / {$checks} checks failed\n";
    echo "\nRecommended Actions:\n";
    echo "1. Check logs: tail -f storage/logs/laravel.log | grep Extension\n";
    echo "2. Run detailed test: php test_extension_to_review.php\n";
    echo "3. Review documentation: EXTENSION_TO_REVIEW_FIX.md\n";
}

echo "\n";
echo "Last checked: " . now()->format('Y-m-d H:i:s') . "\n";
echo "\n";
