<?php

// Manual test notification
require_once 'bootstrap/app.php';

use App\Models\User;
use App\Models\Project;
use App\Services\NotificationService;

try {
    $app = new \Illuminate\Foundation\Application(
        $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__)
    );
    
    $project = Project::first();
    $leader = User::where('role', 'leader')->first();
    
    if ($project && $leader) {
        echo "Creating test notification...\n";
        echo "Project: {$project->name} (ID: {$project->project_id})\n";
        echo "Leader: {$leader->name} (ID: {$leader->user_id})\n\n";
        
        $service = new NotificationService();
        $service->notifyProjectCompletion($project, $leader);
        
        echo "✅ Notification created successfully!\n";
        echo "Now check the notification dropdown in browser.\n";
    } else {
        echo "❌ Missing project or leader data\n";
    }
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}