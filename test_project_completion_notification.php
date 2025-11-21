<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/bootstrap/app.php';

use App\Models\Project;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;

// Test the notification service
try {
    // Get a test project (first available project)
    $project = Project::with('members')->first();
    
    if (!$project) {
        echo "❌ No projects found in database\n";
        exit(1);
    }
    
    // Get a test leader (first user with leader role)
    $leader = User::where('role', 'leader')->first();
    
    if (!$leader) {
        echo "❌ No leader found in database\n";
        exit(1);
    }
    
    echo "🧪 Testing project completion notification\n";
    echo "Project: {$project->name} (ID: {$project->project_id})\n";
    echo "Leader: {$leader->name} (ID: {$leader->user_id})\n\n";
    
    // Test the notification service
    $notificationService = new NotificationService();
    
    echo "📤 Sending notification...\n";
    $notificationService->notifyProjectCompletion($project, $leader);
    
    // Check if notifications were created
    $adminNotifications = DB::table('notifications')
        ->where('type', 'project_completed')
        ->where('data', 'like', '%"project_id":"' . $project->project_id . '"%')
        ->orderBy('created_at', 'desc')
        ->limit(5)
        ->get();
    
    echo "\n✅ Results:\n";
    echo "Notifications created: " . $adminNotifications->count() . "\n";
    
    foreach ($adminNotifications as $notification) {
        $data = json_decode($notification->data, true);
        echo "- Admin User ID: {$notification->user_id}\n";
        echo "  Title: {$notification->title}\n";
        echo "  Message: {$notification->message}\n";
        echo "  Created: {$notification->created_at}\n\n";
    }
    
    if ($adminNotifications->count() > 0) {
        echo "🎉 Test PASSED! Admin notifications are working.\n";
    } else {
        echo "❌ Test FAILED! No notifications were created.\n";
        
        // Check if there are admin users
        $adminCount = User::where('role', 'admin')->count();
        echo "Debug: Admin users in database: {$adminCount}\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}