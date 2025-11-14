<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Project;
use App\Models\Task;
use App\Services\BoardTransitionService;

echo "=== Test Task Transitions ===\n\n";

// Get a leader user
$leader = User::where('role', 'leader')->first();
if (!$leader) {
    echo "❌ No leader user found\n";
    exit;
}
echo "✅ Leader found: {$leader->full_name} (ID: {$leader->user_id})\n";

// Get a project led by this leader
$project = Project::where('leader_id', $leader->user_id)->first();
if (!$project) {
    echo "❌ No project led by this leader\n";
    exit;
}
echo "✅ Project found: {$project->project_name} (ID: {$project->project_id})\n";

// Get a task in review status
$task = Task::where('project_id', $project->project_id)
           ->where('status', 'review')
           ->first();

if (!$task) {
    echo "❌ No task in review status in this project\n";
    exit;
}
echo "✅ Task found: {$task->title} (ID: {$task->task_id}, Status: {$task->status})\n";

// Test BoardTransitionService
$service = new BoardTransitionService();

echo "\n--- Testing isProjectLeader method ---\n";
$isLeader = $service->transitionToDone($task, $leader->user_id);
echo "Leader can approve task: " . ($isLeader['success'] ? "✅ YES" : "❌ NO - " . $isLeader['message']) . "\n";

echo "\n--- Testing reject functionality ---\n";
// Reset task to review if it was changed
$task->refresh();
if ($task->status !== 'review') {
    $task->status = 'review';
    $task->save();
    echo "🔄 Reset task to review status\n";
}

$rejectResult = $service->rejectTask($task, $leader->user_id, "Test rejection reason");
echo "Leader can reject task: " . ($rejectResult['success'] ? "✅ YES" : "❌ NO - " . $rejectResult['message']) . "\n";

echo "\n--- Testing available transitions ---\n";
$transitions = $service->getAvailableTransitions($task, $leader->user_id);
echo "Available transitions for leader:\n";
foreach ($transitions as $transition) {
    echo "  - {$transition['label']} ({$transition['action']})\n";
}

echo "\n✅ Test completed!\n";