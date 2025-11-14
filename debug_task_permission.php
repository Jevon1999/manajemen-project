<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== DEBUG TASK PERMISSION ===\n\n";

// Check users
echo "Users in system:\n";
$users = \App\Models\User::select('user_id', 'name', 'email', 'role')->get();
foreach ($users as $user) {
    echo "ID: {$user->user_id} | Name: {$user->name} | Role: {$user->role}\n";
}

echo "\n";

// Check projects
echo "Projects:\n";
$projects = \App\Models\Project::select('project_id', 'project_name', 'leader_id', 'status')->get();
foreach ($projects as $project) {
    $leader = \App\Models\User::find($project->leader_id);
    echo "ID: {$project->project_id} | Name: {$project->project_name} | Leader: {$leader->name} (ID: {$project->leader_id}, Role: {$leader->role})\n";
}

echo "\n=== END DEBUG ===\n";
