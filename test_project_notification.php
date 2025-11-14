<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Project;
use App\Models\User;
use App\Models\Notification;
use App\Helpers\NotificationHelper;

echo "=== Testing Project Leader Notification ===" . PHP_EOL . PHP_EOL;

// Get a leader user
echo "1. Finding a leader: ";
$leader = User::where('role', 'leader')->first();
if ($leader) {
    echo "✅ Found: {$leader->full_name}" . PHP_EOL;
} else {
    echo "❌ No leader found" . PHP_EOL;
    exit(1);
}

// Get an admin user
echo "2. Finding an admin: ";
$admin = User::where('role', 'admin')->first();
if ($admin) {
    echo "✅ Found: {$admin->full_name}" . PHP_EOL;
} else {
    echo "❌ No admin found" . PHP_EOL;
    exit(1);
}

// Create a test project
echo "3. Creating test project: ";
try {
    $project = new Project();
    $project->project_name = 'Test Project - ' . time();
    $project->description = 'Test project for notification';
    $project->status = 'planning';
    $project->priority = 'medium';
    $project->leader_id = $leader->user_id;
    $project->created_by = $admin->user_id;
    $project->last_activity_at = now();
    $project->save();
    
    echo "✅ Created: {$project->project_name}" . PHP_EOL;
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . PHP_EOL;
    exit(1);
}

// Check notification count before
echo "4. Notifications before test: ";
$beforeCount = Notification::where('user_id', $leader->user_id)->count();
echo "{$beforeCount}" . PHP_EOL;

// Send notification
echo "5. Sending project leader notification: ";
try {
    NotificationHelper::projectLeaderAssigned($project, $leader, $admin);
    echo "✅ Notification sent" . PHP_EOL;
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . PHP_EOL;
    exit(1);
}

// Check notification count after
echo "6. Notifications after test: ";
$afterCount = Notification::where('user_id', $leader->user_id)->count();
echo "{$afterCount}" . PHP_EOL;

// Verify notification was created
if ($afterCount > $beforeCount) {
    echo "7. Verification: ✅ SUCCESS - Project notification sent!" . PHP_EOL;
    
    // Show the latest notification
    $latestNotification = Notification::where('user_id', $leader->user_id)
        ->orderBy('created_at', 'desc')
        ->first();
    
    echo PHP_EOL . "Latest Notification:" . PHP_EOL;
    echo "   - Type: {$latestNotification->type}" . PHP_EOL;
    echo "   - Title: {$latestNotification->title}" . PHP_EOL;
    echo "   - Message: {$latestNotification->message}" . PHP_EOL;
    echo "   - User: {$leader->full_name}" . PHP_EOL;
    
    if ($latestNotification->data) {
        echo "   - Project ID: {$latestNotification->data['project_id']}" . PHP_EOL;
        echo "   - Project Name: {$latestNotification->data['project_name']}" . PHP_EOL;
        echo "   - Assigned By: {$latestNotification->data['assigned_by']}" . PHP_EOL;
    }
} else {
    echo "7. Verification: ❌ FAILED - Notification was not created" . PHP_EOL;
}

// Cleanup
echo PHP_EOL . "8. Cleaning up test data: ";
try {
    $project->delete();
    echo "✅ Test project deleted" . PHP_EOL;
} catch (Exception $e) {
    echo "⚠️  Warning: " . $e->getMessage() . PHP_EOL;
}

echo PHP_EOL . "=== Test Complete ===" . PHP_EOL;
