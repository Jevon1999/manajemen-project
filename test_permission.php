<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TEST TASK PERMISSION ===\n\n";

// Simulate user login as leader ID 5
$userId = 5;
$projectId = 2;

$user = \App\Models\User::find($userId);
$project = \App\Models\Project::find($projectId);

echo "User Info:\n";
echo "  ID: {$user->user_id}\n";
echo "  Name: {$user->name}\n";
echo "  Role: {$user->role}\n\n";

echo "Project Info:\n";
echo "  ID: {$project->project_id}\n";
echo "  Name: {$project->project_name}\n";
echo "  Leader ID: {$project->leader_id}\n\n";

echo "Permission Check:\n";
echo "  Is user the leader? " . ($project->leader_id === $userId ? 'YES' : 'NO') . "\n";
echo "  User has leader role? " . ($user->role === 'leader' ? 'YES' : 'NO') . "\n";

$canManage = $project->leader_id === $userId && $user->role === 'leader';
echo "  Can manage tasks? " . ($canManage ? 'YES ✓' : 'NO ✗') . "\n";

echo "\n=== END TEST ===\n";
