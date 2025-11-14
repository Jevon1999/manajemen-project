<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Project;
use App\Models\Board;
use App\Models\ProjectMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

echo "=== TEST LEADER CREATE TASK POST ===\n\n";

// Find leader
$leader = User::where('role', 'leader')->first();
Auth::login($leader);

echo "✅ Logged in as: {$leader->full_name}\n";

// Find project
$project = Project::find(10);
echo "✅ Project: #{$project->project_id} - {$project->project_name}\n";

// Check if leader is project manager
$isMember = ProjectMember::where('user_id', $leader->user_id)
    ->where('project_id', $project->project_id)
    ->where('role', 'project_manager')
    ->exists();

if (!$isMember) {
    echo "❌ Leader is NOT project_manager for this project!\n";
    exit(1);
}

echo "✅ Leader is project_manager\n\n";

// Get boards
$boards = Board::where('project_id', $project->project_id)->get();
echo "📋 Available boards:\n";
foreach ($boards as $board) {
    echo "   - Board #{$board->board_id}: {$board->board_name}\n";
}
echo "\n";

// Get team members (developers/designers)
$members = ProjectMember::where('project_id', $project->project_id)
    ->whereIn('role', ['developer', 'designer'])
    ->with('user')
    ->get();

echo "👥 Available team members:\n";
if ($members->isEmpty()) {
    echo "   ❌ NO TEAM MEMBERS FOUND!\n";
    echo "   This is why task creation fails - no one to assign!\n\n";
    
    echo "🔧 Adding a developer to project...\n";
    
    // Find a developer
    $developer = User::where('role', 'user')->first();
    
    if ($developer) {
        ProjectMember::create([
            'project_id' => $project->project_id,
            'user_id' => $developer->user_id,
            'role' => 'developer',
            'joined_at' => now()
        ]);
        
        echo "✅ Added: {$developer->full_name} as developer\n\n";
        
        $members = ProjectMember::where('project_id', $project->project_id)
            ->whereIn('role', ['developer', 'designer'])
            ->with('user')
            ->get();
    } else {
        echo "❌ No users with role 'user' found in database!\n";
        exit(1);
    }
}

foreach ($members as $member) {
    echo "   - {$member->user->full_name} (ID: {$member->user_id}, Role: {$member->role})\n";
}

echo "\n";

// Simulate POST request
echo "🧪 Simulating task creation...\n\n";

$testData = [
    'board_id' => $boards->first()->board_id,
    'title' => 'Test Task from Leader',
    'description' => 'Testing leader task creation functionality',
    'priority' => 'medium',
    'status' => 'todo',
    'due_date' => date('Y-m-d', strtotime('+7 days')),
    'estimated_hours' => 5.5,
    'assigned_users' => [$members->first()->user_id]
];

echo "📤 POST Data:\n";
foreach ($testData as $key => $value) {
    if (is_array($value)) {
        echo "   - {$key}: [" . implode(', ', $value) . "]\n";
    } else {
        echo "   - {$key}: {$value}\n";
    }
}

echo "\n";

// Create request and call controller
try {
    $request = Request::create(
        "/leader/projects/{$project->project_id}/tasks",
        'POST',
        $testData
    );
    
    $controller = new App\Http\Controllers\LeaderTaskController();
    $response = $controller->store($request, $project->project_id);
    
    if ($response->getStatusCode() == 302) {
        echo "✅ Task created successfully!\n";
        echo "   Redirect to: " . $response->headers->get('Location') . "\n";
        
        // Check if task was created
        $lastTask = App\Models\Card::latest()->first();
        if ($lastTask) {
            echo "\n📝 Created task:\n";
            echo "   - ID: {$lastTask->card_id}\n";
            echo "   - Title: {$lastTask->card_title}\n";
            echo "   - Status: {$lastTask->status}\n";
            echo "   - Board ID: {$lastTask->board_id}\n";
            
            $assignments = App\Models\CardAssignment::where('card_id', $lastTask->card_id)->count();
            echo "   - Assigned to: {$assignments} user(s)\n";
        }
    } else {
        echo "❌ Unexpected response code: " . $response->getStatusCode() . "\n";
    }
    
} catch (\Illuminate\Validation\ValidationException $e) {
    echo "❌ VALIDATION ERROR:\n";
    foreach ($e->errors() as $field => $errors) {
        echo "   - {$field}: " . implode(', ', $errors) . "\n";
    }
} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n=== TEST COMPLETE ===\n";
