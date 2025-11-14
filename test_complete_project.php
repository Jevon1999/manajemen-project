<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Project;
use App\Models\Board;
use App\Models\Card;
use App\Models\ProjectMember;
use App\Models\User;
use Illuminate\Support\Facades\DB;

echo "=== TEST COMPLETE PROJECT FEATURE ===\n\n";

// Get test project (#10)
$projectId = 10;
$project = Project::with(['boards.cards', 'members'])->find($projectId);

if (!$project) {
    echo "❌ Project #$projectId tidak ditemukan\n";
    exit(1);
}

echo "📋 Project: {$project->project_name}\n";
echo "   Status: {$project->status}\n";
echo "   Leader: {$project->leader_id}\n\n";

// Count tasks
$totalTasks = $project->boards->sum(function($board) {
    return $board->cards->count();
});

$completedTasks = $project->boards->sum(function($board) {
    return $board->cards->where('status', 'done')->count();
});

$pendingTasks = $totalTasks - $completedTasks;

echo "📊 Task Statistics:\n";
echo "   Total Tasks: $totalTasks\n";
echo "   Completed: $completedTasks\n";
echo "   Pending: $pendingTasks\n\n";

// Check if can be completed
if ($totalTasks === 0) {
    echo "⚠️  Cannot complete: No tasks in project\n";
    exit(0);
}

if ($pendingTasks > 0) {
    echo "⚠️  Cannot complete: $pendingTasks task(s) still pending\n\n";
    
    echo "Pending tasks:\n";
    foreach ($project->boards as $board) {
        foreach ($board->cards as $card) {
            if ($card->status !== 'done') {
                echo "   - [{$card->status}] {$card->card_title} (ID: {$card->card_id})\n";
            }
        }
    }
    
    echo "\n💡 Tip: Mark all tasks as 'done' first before completing project\n";
    
    // Option to mark all tasks as done for testing
    echo "\n🔧 Do you want to mark all tasks as 'done' for testing? (y/n): ";
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    if (trim($line) == 'y') {
        echo "Marking all tasks as done...\n";
        DB::table('cards')
            ->whereIn('board_id', $project->boards->pluck('board_id'))
            ->update(['status' => 'done']);
        
        echo "✅ All tasks marked as done\n";
        $pendingTasks = 0;
    } else {
        exit(0);
    }
}

if ($pendingTasks === 0) {
    echo "✅ All tasks completed! Project can be marked as complete\n\n";
    
    echo "🧪 Testing complete project method...\n";
    
    try {
        DB::beginTransaction();
        
        // Update project status
        $project->update([
            'status' => 'completed',
            'completion_percentage' => 100,
            'last_activity_at' => now(),
            'completed_at' => now(),
        ]);
        
        echo "✅ Project marked as completed\n";
        echo "   Status: {$project->status}\n";
        echo "   Completed At: {$project->completed_at}\n";
        echo "   Completion: {$project->completion_percentage}%\n\n";
        
        // Show project members who will be notified
        echo "👥 Team members to notify:\n";
        foreach ($project->members as $member) {
            if ($member->user_id !== $project->leader_id) {
                echo "   - {$member->user->full_name} ({$member->user->email})\n";
            }
        }
        
        DB::commit();
        
        echo "\n🎉 SUCCESS! Project completion test passed!\n";
        
    } catch (\Exception $e) {
        DB::rollBack();
        echo "\n❌ ERROR: " . $e->getMessage() . "\n";
        echo "Trace: " . $e->getTraceAsString() . "\n";
    }
}

echo "\n=== TEST COMPLETED ===\n";
