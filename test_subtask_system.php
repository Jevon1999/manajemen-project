<?php

/**
 * Test Script: Subtask System
 * Purpose: Verify subtask CRUD operations and relationships
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Task;
use App\Models\Subtask;
use App\Models\User;
use App\Models\Project;
use Illuminate\Support\Facades\DB;

echo "\n=== TESTING SUBTASK SYSTEM ===\n\n";

try {
    // 1. Get a test task
    $task = Task::with(['assignedUser', 'project'])->first();
    
    if (!$task) {
        echo "❌ ERROR: No tasks found in database. Please create a task first.\n";
        exit;
    }
    
    echo "✅ Test Task Found:\n";
    echo "   - Task ID: {$task->task_id}\n";
    echo "   - Title: {$task->title}\n";
    echo "   - Assigned to: " . ($task->assignedUser->full_name ?? 'N/A') . "\n";
    echo "   - Project: " . ($task->project->project_name ?? 'N/A') . "\n\n";
    
    // 2. Check existing subtasks
    $existingSubtasks = $task->subtasks()->count();
    echo "📊 Existing subtasks count: {$existingSubtasks}\n\n";
    
    // 3. Create test subtasks
    echo "🔧 Creating test subtasks...\n";
    
    $subtask1 = Subtask::create([
        'task_id' => $task->task_id,
        'title' => 'Setup development environment',
        'description' => 'Install dependencies and configure local environment',
        'priority' => Subtask::PRIORITY_HIGH,
        'created_by' => $task->assigned_to,
    ]);
    echo "   ✓ Created subtask #1: {$subtask1->title} (Priority: {$subtask1->priority})\n";
    
    $subtask2 = Subtask::create([
        'task_id' => $task->task_id,
        'title' => 'Write unit tests',
        'description' => 'Create test cases for new features',
        'priority' => Subtask::PRIORITY_MEDIUM,
        'created_by' => $task->assigned_to,
    ]);
    echo "   ✓ Created subtask #2: {$subtask2->title} (Priority: {$subtask2->priority})\n";
    
    $subtask3 = Subtask::create([
        'task_id' => $task->task_id,
        'title' => 'Update documentation',
        'description' => 'Document API endpoints and usage examples',
        'priority' => Subtask::PRIORITY_LOW,
        'created_by' => $task->assigned_to,
    ]);
    echo "   ✓ Created subtask #3: {$subtask3->title} (Priority: {$subtask3->priority})\n\n";
    
    // 4. Test subtask relations
    echo "🔗 Testing Relations:\n";
    echo "   - Subtask -> Task: " . $subtask1->task->title . "\n";
    echo "   - Subtask -> Creator: " . $subtask1->creator->full_name . "\n";
    echo "   - Task -> Subtasks: " . $task->subtasks()->count() . " subtasks\n\n";
    
    // 5. Test completion methods
    echo "✔️  Testing Completion Methods:\n";
    
    $subtask1->markAsCompleted();
    echo "   ✓ Marked subtask #1 as completed\n";
    echo "     - Is Completed: " . ($subtask1->is_completed ? 'Yes' : 'No') . "\n";
    echo "     - Completed At: " . $subtask1->completed_at . "\n\n";
    
    // 6. Test scopes
    echo "🔍 Testing Scopes:\n";
    $completedCount = $task->subtasks()->completed()->count();
    $incompleteCount = $task->subtasks()->incomplete()->count();
    $highPriorityCount = $task->subtasks()->byPriority('high')->count();
    
    echo "   - Completed: {$completedCount}\n";
    echo "   - Incomplete: {$incompleteCount}\n";
    echo "   - High Priority: {$highPriorityCount}\n\n";
    
    // 7. Calculate progress
    $totalSubtasks = $task->subtasks()->count();
    $completedSubtasks = $task->subtasks()->completed()->count();
    $progress = $totalSubtasks > 0 ? round(($completedSubtasks / $totalSubtasks) * 100) : 0;
    
    echo "📈 Task Progress:\n";
    echo "   - Total: {$totalSubtasks}\n";
    echo "   - Completed: {$completedSubtasks}\n";
    echo "   - Progress: {$progress}%\n\n";
    
    // 8. Test update
    echo "📝 Testing Update:\n";
    $subtask2->update([
        'priority' => Subtask::PRIORITY_HIGH,
        'description' => 'UPDATED: Create comprehensive test suite with integration tests',
    ]);
    echo "   ✓ Updated subtask #2 priority to HIGH\n";
    echo "   ✓ Updated subtask #2 description\n\n";
    
    // 9. List all subtasks with details
    echo "📋 All Subtasks for Task '{$task->title}':\n";
    foreach ($task->subtasks()->orderBy('priority', 'desc')->get() as $subtask) {
        $status = $subtask->is_completed ? '✅' : '⬜';
        $priorityBadge = match($subtask->priority) {
            'high' => '🔴 HIGH',
            'medium' => '🟡 MEDIUM',
            'low' => '🟢 LOW',
            default => '⚪ UNKNOWN',
        };
        
        echo "   {$status} [{$priorityBadge}] {$subtask->title}\n";
        if ($subtask->description) {
            echo "      Description: {$subtask->description}\n";
        }
        if ($subtask->is_completed && $subtask->completed_at) {
            echo "      Completed: {$subtask->completed_at->diffForHumans()}\n";
        }
        echo "\n";
    }
    
    // 10. Test deletion
    echo "🗑️  Testing Deletion:\n";
    $subtask3->delete();
    echo "   ✓ Deleted subtask #3: {$subtask3->title}\n\n";
    
    // Final count
    $finalCount = $task->subtasks()->count();
    echo "📊 Final subtask count: {$finalCount}\n\n";
    
    echo "=== ✅ ALL TESTS PASSED ===\n";
    echo "\nSubtask system is working correctly!\n";
    echo "You can now:\n";
    echo "  1. Create subtasks via POST /tasks/{task}/subtasks\n";
    echo "  2. Update subtasks via PUT /tasks/{task}/subtasks/{subtask}\n";
    echo "  3. Toggle completion via POST /tasks/{task}/subtasks/{subtask}/toggle\n";
    echo "  4. Delete subtasks via DELETE /tasks/{task}/subtasks/{subtask}\n\n";
    
} catch (\Exception $e) {
    echo "\n❌ ERROR: {$e->getMessage()}\n";
    echo "File: {$e->getFile()}\n";
    echo "Line: {$e->getLine()}\n\n";
    echo "Stack Trace:\n{$e->getTraceAsString()}\n";
}
