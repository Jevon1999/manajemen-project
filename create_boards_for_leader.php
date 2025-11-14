<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Project;
use App\Models\Board;
use App\Models\User;
use App\Models\ProjectMember;
use Illuminate\Support\Facades\DB;

echo "=== CREATE BOARD FOR LEADER'S PROJECT ===\n\n";

// Find leader
$leader = User::where('role', 'leader')->first();
echo "Leader: {$leader->full_name}\n";

// Find project #10
$project = Project::find(10);

if (!$project) {
    echo "❌ Project #10 not found\n";
    exit(1);
}

echo "Project: #{$project->project_id} - {$project->project_name}\n";

// Check current boards
$existingBoards = Board::where('project_id', $project->project_id)->count();
echo "Existing boards: {$existingBoards}\n\n";

if ($existingBoards == 0) {
    echo "🔧 Creating default boards for project...\n\n";
    
    $boardNames = ['To Do', 'In Progress', 'Review', 'Done'];
    $positions = [1, 2, 3, 4];
    
    foreach ($boardNames as $index => $boardName) {
        $board = Board::create([
            'board_name' => $boardName,
            'project_id' => $project->project_id,
            'position' => $positions[$index],
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        echo "✅ Created board: {$board->board_name} (ID: {$board->board_id})\n";
    }
    
    echo "\n✅ All default boards created!\n";
} else {
    echo "✅ Project already has boards.\n";
}

echo "\n📊 Final board count: " . Board::where('project_id', $project->project_id)->count() . "\n";
echo "\n🎯 Leader can now create tasks at:\n";
echo "   http://localhost:8000/leader/projects/{$project->project_id}/tasks/create\n";
