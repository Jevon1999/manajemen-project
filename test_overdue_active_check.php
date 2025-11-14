<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Card;
use App\Models\Task;

echo "=== Testing Overdue Task as Active Task ===\n\n";

// Find worker1 (user_id = 5)
$user = User::find(5);
if (!$user) {
    echo "User not found!\n";
    exit;
}

echo "User: {$user->full_name} (ID: {$user->user_id})\n";
echo "----------------------------------------\n\n";

// Check if user has active task
$hasActiveTask = Card::userHasActiveTask($user->user_id);
echo "Has Active Task (Card): " . ($hasActiveTask ? "✅ YES" : "❌ NO") . "\n";

// Get active task
$activeTask = Card::getActiveTaskForUser($user->user_id);
if ($activeTask) {
    echo "Active Card: {$activeTask->card_title}\n";
    echo "Status: {$activeTask->status}\n";
    echo "Is Active: " . ($activeTask->is_active ? "Yes" : "No") . "\n";
    echo "Deadline: {$activeTask->deadline}\n";
}

echo "\n--- Task Model Check ---\n";
$taskActive = Task::where('assigned_to', $user->user_id)
    ->whereIn('status', ['todo', 'in_progress', 'overdue'])
    ->first();

if ($taskActive) {
    echo "Active Task: {$taskActive->title}\n";
    echo "Status: {$taskActive->status}\n";
    echo "Deadline: {$taskActive->deadline}\n";
} else {
    echo "No active task found\n";
}

echo "\n--- Can User Take New Task? ---\n";
$canTakeNew = Card::canUserTakeNewTask($user->user_id);
echo "Can Take New Task: " . ($canTakeNew ? "✅ YES" : "❌ NO (Has Active Task)") . "\n";

echo "\n=== Test Complete ===\n";
