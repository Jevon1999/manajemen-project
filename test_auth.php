<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$app->boot();

use Illuminate\Support\Facades\Auth;

// Simulate login as leader (user ID 5)
$user = App\Models\User::find(5); // Leader of project 12
if (!$user) {
    echo "User not found\n";
    exit;
}

// Create request
$request = Illuminate\Http\Request::create('/admin/projects/12/tasks', 'GET');
$app->instance('request', $request);

// Set authenticated user
Auth::setUser($user);
echo "Authenticated as: " . $user->email . " (ID: " . $user->user_id . ")\n";

// Test the authorization logic
$project = App\Models\Project::find(12);
if (!$project) {
    echo "Project not found\n";
    exit;
}

echo "Project: " . $project->name . " (ID: " . $project->project_id . ")\n";
echo "Project Leader ID: " . $project->leader_id . "\n";

// Test leader check
$isLeader = $project->leader_id === $user->user_id;
echo "Is Leader: " . ($isLeader ? 'YES' : 'NO') . "\n";

// Test member check  
$isMember = $project->members()->where('user_id', $user->user_id)->exists();
echo "Is Member: " . ($isMember ? 'YES' : 'NO') . "\n";

// Test admin check
$isAdmin = $user->role === 'admin';
echo "Is Admin: " . ($isAdmin ? 'YES' : 'NO') . "\n";

// Final result
$canView = $isLeader || $isMember || $isAdmin;
echo "Can View Project: " . ($canView ? 'YES' : 'NO') . "\n";

// Show members
echo "\nProject Members:\n";
$members = $project->members()->with('user')->get();
foreach ($members as $member) {
    echo "- User ID: " . $member->user_id . ", Role: " . $member->role . "\n";
}