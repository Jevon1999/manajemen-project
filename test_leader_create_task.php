<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Project;
use App\Models\ProjectMember;
use Illuminate\Support\Facades\Auth;

echo "=== LEADER CREATE TASK TEST ===\n\n";

// Find leader user
$leader = User::where('role', 'leader')->first();

if (!$leader) {
    echo "❌ ERROR: No leader found in database\n";
    exit(1);
}

echo "✅ Leader found: {$leader->full_name} (ID: {$leader->user_id})\n";

// Find projects where leader is project_manager
$projects = ProjectMember::where('user_id', $leader->user_id)
    ->where('role', 'project_manager')
    ->with('project')
    ->get();

echo "📊 Leader is project_manager in " . $projects->count() . " project(s)\n\n";

if ($projects->isEmpty()) {
    echo "⚠️  WARNING: Leader is NOT assigned as project_manager to any project!\n";
    echo "   This is why they can't create tasks.\n\n";
    
    // Check if leader is in any projects
    $anyProjects = ProjectMember::where('user_id', $leader->user_id)
        ->with('project')
        ->get();
    
    if ($anyProjects->isNotEmpty()) {
        echo "📋 Leader IS a member of these projects (but not as project_manager):\n";
        foreach ($anyProjects as $member) {
            echo "   - Project #{$member->project_id}: {$member->project->project_name} (Role: {$member->role})\n";
        }
        echo "\n";
        echo "🔧 SOLUTION: Change leader's role to 'project_manager' in one of these projects\n";
        
        // Try to fix automatically
        $firstProject = $anyProjects->first();
        echo "\n🔄 Attempting to fix automatically...\n";
        
        try {
            $firstProject->update(['role' => 'project_manager']);
            echo "✅ Fixed! Leader is now project_manager for Project #{$firstProject->project_id}\n";
            $projects = ProjectMember::where('user_id', $leader->user_id)
                ->where('role', 'project_manager')
                ->with('project')
                ->get();
        } catch (\Exception $e) {
            echo "❌ Failed to update: " . $e->getMessage() . "\n";
        }
    } else {
        echo "❌ ERROR: Leader is not a member of ANY project!\n";
        echo "   Admin needs to add this leader to a project first.\n\n";
        exit(1);
    }
}

// Now check if leader can access task creation
$validProjects = 0;
foreach ($projects as $member) {
    $project = $member->project;
    
    if (!$project) {
        echo "⚠️  Skipping deleted project (ID: {$member->project_id})\n";
        continue;
    }
    
    $validProjects++;
    echo "✅ Project #{$project->project_id}: {$project->project_name}\n";
    echo "   Role: {$member->role}\n";
    echo "   URL: http://localhost:8000/leader/projects/{$project->project_id}/tasks/create\n";
    
    // Check if project has boards
    $boards = $project->boards()->count();
    echo "   Boards: {$boards}\n";
    
    if ($boards == 0) {
        echo "   ⚠️  WARNING: Project has no boards! Leader needs boards to create tasks.\n";
    }
    
    echo "\n";
}

if ($validProjects == 0) {
    echo "❌ ERROR: Leader has no valid projects to manage!\n";
    exit(1);
}

echo "\n=== TEST COMPLETE ===\n";
echo "✅ Leader CAN create tasks for " . $projects->count() . " project(s)\n";
