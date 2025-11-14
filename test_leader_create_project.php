<?php

/**
 * Test Script - Project Creation by Leader
 * Verifies that leaders can successfully create projects
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Project;
use App\Models\ProjectMember;
use Illuminate\Support\Facades\DB;

echo "╔══════════════════════════════════════════════════════════╗\n";
echo "║   TEST: Leader Project Creation                         ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n\n";

// Find a leader user
$leader = User::where('role', 'leader')->first();

if (!$leader) {
    echo "❌ No leader users found in the system.\n";
    echo "   Please create a leader user first.\n";
    exit(1);
}

echo "✅ Found leader: {$leader->full_name} (ID: {$leader->user_id})\n\n";

// Test data
$testProjectData = [
    'project_name' => 'Test Project - ' . date('Y-m-d H:i:s'),
    'description' => 'This is a test project created by leader',
    'deadline' => now()->addDays(30)->format('Y-m-d'),
    'status' => 'planning',
    'category' => 'web_development', // Fix: use valid enum value
    'priority' => 'high',
    'created_by' => $leader->user_id,
    'leader_id' => $leader->user_id,
    'last_activity_at' => now(),
];

echo "📋 Test Project Data:\n";
foreach ($testProjectData as $key => $value) {
    echo "   {$key}: {$value}\n";
}
echo "\n";

// Try creating project
echo "🚀 Attempting to create project...\n";

try {
    DB::beginTransaction();
    
    $project = Project::create($testProjectData);
    
    echo "✅ Project created successfully!\n";
    echo "   Project ID: {$project->project_id}\n";
    echo "   Project Name: {$project->project_name}\n\n";
    
    // Add leader as project manager
    echo "👤 Adding leader as project manager...\n";
    
    $member = ProjectMember::create([
        'project_id' => $project->project_id,
        'user_id' => $leader->user_id,
        'role' => 'project_manager',
        'joined_at' => now(),
    ]);
    
    echo "✅ Leader added as project manager\n\n";
    
    DB::commit();
    
    // Verify
    echo "🔍 Verification:\n";
    $createdProject = Project::with(['creator', 'leader', 'members'])->find($project->project_id);
    
    echo "   Project Name: {$createdProject->project_name} ✅\n";
    echo "   Description: " . substr($createdProject->description, 0, 50) . "... ✅\n";
    echo "   Status: {$createdProject->status} ✅\n";
    echo "   Created By: {$createdProject->creator->full_name} ✅\n";
    echo "   Leader: {$createdProject->leader->full_name} ✅\n";
    echo "   Members: {$createdProject->members->count()} ✅\n";
    
    echo "\n✅ TEST PASSED: Leader can create projects successfully!\n\n";
    
    // Cleanup
    echo "🧹 Cleaning up test data...\n";
    $member->delete();
    $project->delete();
    echo "✅ Test data cleaned up\n";
    
} catch (\Exception $e) {
    DB::rollBack();
    
    echo "\n❌ TEST FAILED: " . $e->getMessage() . "\n\n";
    
    echo "Debug Info:\n";
    echo "   Exception: " . get_class($e) . "\n";
    echo "   Message: {$e->getMessage()}\n";
    echo "   File: {$e->getFile()}:{$e->getLine()}\n\n";
    
    echo "Possible causes:\n";
    echo "   1. Database field mismatch (check fillable fields in Project model)\n";
    echo "   2. Validation error (check validation rules)\n";
    echo "   3. Foreign key constraint (check if leader_id exists)\n";
    echo "   4. Missing required fields\n\n";
    
    echo "Project Model Fillable Fields:\n";
    $project = new Project();
    foreach ($project->getFillable() as $field) {
        echo "   - {$field}\n";
    }
    
    exit(1);
}

echo "═══════════════════════════════════════════════════════════\n";
echo "\n💡 If this test passes, leaders can successfully:\n";
echo "   ✅ Create new projects\n";
echo "   ✅ Be automatically assigned as project leader\n";
echo "   ✅ Be added as project manager member\n";
echo "\n";
