<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Task;
use App\Models\User;
use App\Models\Notification;
use App\Helpers\NotificationHelper;

echo "=== Testing Notification System ===" . PHP_EOL . PHP_EOL;

// Test 1: Check if NotificationHelper exists
echo "1. NotificationHelper class: ";
if (class_exists('App\Helpers\NotificationHelper')) {
    echo "✅ Found" . PHP_EOL;
} else {
    echo "❌ Not Found" . PHP_EOL;
    exit(1);
}

// Test 2: Get a task
echo "2. Finding a task: ";
$task = Task::first();
if ($task) {
    echo "✅ Found task #{$task->task_id}: {$task->title}" . PHP_EOL;
} else {
    echo "❌ No tasks found" . PHP_EOL;
    exit(1);
}

// Test 3: Get assigned user
echo "3. Finding assigned user: ";
if ($task->assigned_to) {
    $user = User::find($task->assigned_to);
    if ($user) {
        echo "✅ Found user: {$user->full_name}" . PHP_EOL;
    } else {
        echo "❌ User not found" . PHP_EOL;
        exit(1);
    }
} else {
    echo "⚠️  Task not assigned to anyone" . PHP_EOL;
    // Get any user for testing
    $user = User::first();
    if ($user) {
        echo "   Using first user: {$user->full_name}" . PHP_EOL;
    } else {
        echo "❌ No users found" . PHP_EOL;
        exit(1);
    }
}

// Test 4: Check notification count before
echo "4. Notifications before test: ";
$beforeCount = Notification::count();
echo "{$beforeCount}" . PHP_EOL;

// Test 5: Create test notification
echo "5. Creating test notification: ";
try {
    NotificationHelper::taskApproved($task, $user, 'This is a test notification from test script');
    echo "✅ Notification created" . PHP_EOL;
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . PHP_EOL;
    exit(1);
}

// Test 6: Check notification count after
echo "6. Notifications after test: ";
$afterCount = Notification::count();
echo "{$afterCount}" . PHP_EOL;

// Test 7: Verify notification was created
if ($afterCount > $beforeCount) {
    echo "7. Verification: ✅ SUCCESS - Notification system is working!" . PHP_EOL;
    
    // Show the latest notification
    $latestNotification = Notification::orderBy('created_at', 'desc')->first();
    echo PHP_EOL . "Latest Notification:" . PHP_EOL;
    echo "   - Type: {$latestNotification->type}" . PHP_EOL;
    echo "   - Title: {$latestNotification->title}" . PHP_EOL;
    echo "   - Message: {$latestNotification->message}" . PHP_EOL;
    echo "   - User ID: {$latestNotification->user_id}" . PHP_EOL;
} else {
    echo "7. Verification: ❌ FAILED - Notification was not created" . PHP_EOL;
}

echo PHP_EOL . "=== Test Complete ===" . PHP_EOL;
