<?php

require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$app->boot();

$project = App\Models\Project::find(12);

if ($project) {
    echo "Project ID: " . $project->project_id . "\n";
    echo "Project Name: " . $project->name . "\n";
    echo "Leader ID: " . $project->leader_id . "\n";
    
    $leader = $project->leader;
    if ($leader) {
        echo "Leader Name: " . $leader->name . "\n";
        echo "Leader Role: " . $leader->role . "\n";
    }
    
    // Check members
    echo "\nProject Members:\n";
    $members = $project->members()->with('user')->get();
    foreach ($members as $member) {
        echo "- User ID: " . $member->user_id . ", Name: " . $member->user->name . ", Role: " . $member->role . "\n";
    }
} else {
    echo "Project with ID 12 not found\n";
}