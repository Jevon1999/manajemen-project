<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing User One Project Validation\n";
echo "=====================================\n\n";

// Get users with role 'user'
$users = \App\Models\User::where('role', 'user')->get();

echo "Users with role 'user':\n";
foreach ($users as $user) {
    $projectCount = \App\Models\ProjectMember::where('user_id', $user->user_id)
        ->whereHas('project', function($query) {
            $query->whereNull('deleted_at');
        })
        ->count();
    
    echo "- {$user->full_name} (ID: {$user->user_id}): {$projectCount} project(s)\n";
    
    if ($projectCount > 0) {
        $memberships = \App\Models\ProjectMember::where('user_id', $user->user_id)
            ->with('project')
            ->get();
        
        foreach ($memberships as $membership) {
            if ($membership->project) {
                echo "  └─ {$membership->project->project_name} (Role: {$membership->role})\n";
            }
        }
    }
}

echo "\n";
echo "Users available for new projects (not in any project):\n";
$availableUsers = \App\Models\User::where('role', 'user')
    ->where('status', 'active')
    ->whereDoesntHave('projectMemberships', function($query) {
        $query->whereHas('project', function($q) {
            $q->whereNull('deleted_at');
        });
    })
    ->get();

if ($availableUsers->count() > 0) {
    foreach ($availableUsers as $user) {
        echo "- {$user->full_name} (ID: {$user->user_id})\n";
    }
} else {
    echo "No available users. All users are already assigned to projects.\n";
}
