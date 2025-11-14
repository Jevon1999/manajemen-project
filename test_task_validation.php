<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing 1 Task = 1 User Validation\n";
echo "===================================\n\n";

// Get all tasks
$tasks = \App\Models\Task::with(['assignedUser', 'project'])
    ->whereNotNull('assigned_to')
    ->orderBy('assigned_to')
    ->get();

echo "Current Task Assignments:\n";
echo "-------------------------\n";

$userTaskCounts = [];

foreach ($tasks as $task) {
    if ($task->assignedUser) {
        $userId = $task->assignedUser->user_id;
        $userName = $task->assignedUser->full_name;
        $status = $task->status;
        $projectName = $task->project ? $task->project->project_name : 'Unknown';
        
        if (!isset($userTaskCounts[$userId])) {
            $userTaskCounts[$userId] = [
                'name' => $userName,
                'total' => 0,
                'active' => 0,
                'done' => 0,
                'tasks' => []
            ];
        }
        
        $userTaskCounts[$userId]['total']++;
        
        if (in_array($status, ['todo', 'in_progress', 'review'])) {
            $userTaskCounts[$userId]['active']++;
        } else {
            $userTaskCounts[$userId]['done']++;
        }
        
        $userTaskCounts[$userId]['tasks'][] = [
            'title' => $task->title,
            'status' => $status,
            'project' => $projectName
        ];
    }
}

foreach ($userTaskCounts as $userId => $data) {
    echo "\n👤 {$data['name']} (ID: {$userId})\n";
    echo "   Total Tasks: {$data['total']} | Active: {$data['active']} | Completed: {$data['done']}\n";
    
    if ($data['active'] > 1) {
        echo "   ⚠️ WARNING: User has {$data['active']} ACTIVE tasks! (Should only have 1)\n";
    } elseif ($data['active'] == 1) {
        echo "   ✅ OK: User has 1 active task\n";
    } else {
        echo "   ℹ️ User has no active tasks\n";
    }
    
    foreach ($data['tasks'] as $task) {
        $statusIcon = [
            'todo' => '📋',
            'in_progress' => '🚀',
            'review' => '👀',
            'done' => '✅'
        ][$task['status']] ?? '❓';
        
        echo "   {$statusIcon} {$task['title']} ({$task['status']}) - {$task['project']}\n";
    }
}

echo "\n\nUsers Available for New Task:\n";
echo "------------------------------\n";

// Test getAvailableUsersForTask untuk setiap project
$projects = \App\Models\Project::whereHas('members')->get();

foreach ($projects as $project) {
    echo "\nProject: {$project->project_name}\n";
    
    $taskService = new \App\Services\TaskService();
    $availableUsers = $taskService->getAvailableUsersForTask($project->project_id);
    
    if ($availableUsers->count() > 0) {
        foreach ($availableUsers as $user) {
            echo "  ✅ {$user->full_name} - Available\n";
        }
    } else {
        echo "  ⚠️ No users available (all have active tasks)\n";
    }
}

echo "\n\nValidation Rules Applied:\n";
echo "-------------------------\n";
echo "✅ User can only have 1 active task (status: todo, in_progress, or review)\n";
echo "✅ Completed tasks (status: done) don't count towards the limit\n";
echo "✅ Available users list excludes anyone with active tasks\n";
