<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Task;
use App\Models\WorkSession;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

$admin = User::where('role', 'admin')->first();
Auth::login($admin);

echo "=== Database Statistics ===\n\n";

// Projects
$totalProjects = Project::count();
$activeProjects = Project::where('status', 'active')->count();
$completedProjects = Project::where('status', 'completed')->count();

echo "Projects:\n";
echo "  Total: $totalProjects\n";
echo "  Active: $activeProjects\n";
echo "  Completed: $completedProjects\n\n";

// Tasks
$totalTasks = Task::count();
$todoTasks = Task::where('status', 'todo')->count();
$inProgressTasks = Task::where('status', 'in_progress')->count();
$reviewTasks = Task::where('status', 'review')->count();
$doneTasks = Task::where('status', 'done')->count();

echo "Tasks:\n";
echo "  Total: $totalTasks\n";
echo "  To Do: $todoTasks\n";
echo "  In Progress: $inProgressTasks\n";
echo "  Review: $reviewTasks\n";
echo "  Done: $doneTasks\n\n";

// Work Sessions
$totalSessions = WorkSession::count();
$totalSeconds = WorkSession::sum('duration_seconds') ?? 0;
$totalHours = round($totalSeconds / 3600, 2);

echo "Work Sessions:\n";
echo "  Total Sessions: $totalSessions\n";
echo "  Total Hours: {$totalHours}h\n\n";

// November 2025 specific
$novStart = Carbon::create(2025, 11, 1)->startOfMonth();
$novEnd = Carbon::create(2025, 11, 30)->endOfMonth();

$novTasks = Task::whereBetween('created_at', [$novStart, $novEnd])->count();
$novSessions = WorkSession::whereBetween('work_date', [$novStart->format('Y-m-d'), $novEnd->format('Y-m-d')])->count();
$novSeconds = WorkSession::whereBetween('work_date', [$novStart->format('Y-m-d'), $novEnd->format('Y-m-d')])->sum('duration_seconds') ?? 0;
$novHours = round($novSeconds / 3600, 2);

echo "November 2025:\n";
echo "  Tasks Created: $novTasks\n";
echo "  Work Sessions: $novSessions\n";
echo "  Work Hours: {$novHours}h\n\n";

// Task details
echo "Task Details:\n";
$tasks = Task::with('project')->orderBy('created_at', 'desc')->take(5)->get();
foreach($tasks as $task) {
    $created = $task->created_at->format('Y-m-d H:i');
    echo "  - [{$task->task_id}] {$task->title} ({$task->status}) - Created: $created\n";
}

echo "\n✅ Summary Complete!\n";
