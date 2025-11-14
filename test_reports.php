<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;

echo "=== Testing Reports System ===\n\n";

// Login as admin
$admin = User::where('role', 'admin')->first();
if (!$admin) {
    echo "❌ No admin user found!\n";
    exit(1);
}

Auth::login($admin);
echo "✅ Logged in as: {$admin->full_name} ({$admin->role})\n\n";

// Test statistics
echo "--- Statistics ---\n";
$stats = [
    'total_projects' => Project::count(),
    'active_projects' => Project::where('status', 'active')->count(),
    'total_tasks' => Task::count(),
    'completed_tasks' => Task::where('status', 'done')->count(),
];

foreach ($stats as $key => $value) {
    echo "$key: $value\n";
}

echo "\n--- Testing Monthly Data ---\n";
try {
    $controller = new App\Http\Controllers\ReportController();
    $reflection = new ReflectionClass($controller);
    $method = $reflection->getMethod('getMonthlyData');
    $method->setAccessible(true);
    
    $monthlyData = $method->invoke($controller);
    echo "✅ Monthly data retrieved: " . count($monthlyData) . " months\n";
    
    if (count($monthlyData) > 0) {
        $latest = $monthlyData[count($monthlyData) - 1];
        echo "Latest month: {$latest['month']}\n";
        echo "  - Total tasks: {$latest['total_tasks']}\n";
        echo "  - Completed: {$latest['completed_tasks']}\n";
        echo "  - Rate: {$latest['completion_rate']}%\n";
        echo "  - Work hours: {$latest['work_hours']}h\n";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}

echo "\n--- Testing Yearly Data ---\n";
try {
    $method = $reflection->getMethod('getYearlyData');
    $method->setAccessible(true);
    
    $yearlyData = $method->invoke($controller);
    echo "✅ Yearly data retrieved: " . count($yearlyData) . " years\n";
    
    if (count($yearlyData) > 0) {
        $latest = $yearlyData[count($yearlyData) - 1];
        echo "Latest year: {$latest['year']}\n";
        echo "  - Total projects: {$latest['total_projects']}\n";
        echo "  - Completed projects: {$latest['completed_projects']}\n";
        echo "  - Total tasks: {$latest['total_tasks']}\n";
        echo "  - Completed tasks: {$latest['completed_tasks']}\n";
        echo "  - Rate: {$latest['completion_rate']}%\n";
        echo "  - Work hours: {$latest['work_hours']}h\n";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}

echo "\n--- Testing Project Data ---\n";
try {
    $method = $reflection->getMethod('getProjectData');
    $method->setAccessible(true);
    
    $projectData = $method->invoke($controller);
    echo "✅ Project data retrieved: " . count($projectData) . " projects\n";
    
    if (count($projectData) > 0) {
        $first = $projectData[0];
        echo "First project: {$first['project_name']}\n";
        echo "  - Leader: {$first['leader_name']}\n";
        echo "  - Status: {$first['status']}\n";
        echo "  - Total tasks: {$first['total_tasks']}\n";
        echo "  - Completed: {$first['completed_tasks']}\n";
        echo "  - Rate: {$first['completion_rate']}%\n";
        echo "  - Team: {$first['team_members']} members\n";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}

echo "\n✅ All tests completed!\n";
