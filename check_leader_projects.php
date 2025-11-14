<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Project Members with Leader Role:\n";
echo "=====================================\n";

$members = \App\Models\ProjectMember::with(['user', 'project'])
    ->whereIn('role', ['leader', 'project_manager'])
    ->get();

if ($members->count() > 0) {
    foreach ($members as $member) {
        if ($member->user && $member->project) {
            echo "User: {$member->user->full_name} ({$member->user->role})\n";
            echo "Project: {$member->project->project_name}\n";
            echo "Role in Project: {$member->role}\n";
            echo "-------------------------------------\n";
        } else {
            echo "Invalid member data: User ID {$member->user_id}, Project ID {$member->project_id}\n";
        }
    }
} else {
    echo "No leaders found in any projects!\n";
}

echo "\nAll project members:\n";
echo "=====================================\n";

$allMembers = \App\Models\ProjectMember::with(['user', 'project'])->get();
foreach ($allMembers as $member) {
    if ($member->user && $member->project) {
        echo "User: {$member->user->full_name} | Project: {$member->project->project_name} | Role: {$member->role}\n";
    } else {
        echo "Invalid: User ID {$member->user_id} | Project ID {$member->project_id} | Role: {$member->role}\n";
    }
}
