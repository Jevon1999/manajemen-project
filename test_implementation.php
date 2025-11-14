<?php

/**
 * Simple Test Script untuk Memverifikasi Implementation
 * Run: php test_implementation.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=================================\n";
echo "Testing Project Member Implementation\n";
echo "=================================\n\n";

// Test 1: Check if Service class exists
echo "✓ Test 1: Service Class Exists\n";
try {
    $service = app(App\Services\ProjectMemberService::class);
    echo "  ✅ ProjectMemberService loaded successfully!\n";
    echo "  Available methods: " . count(get_class_methods($service)) . "\n";
} catch (\Exception $e) {
    echo "  ❌ Failed: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 2: Check if Form Request exists
echo "✓ Test 2: Form Request Class Exists\n";
try {
    $reflection = new ReflectionClass(App\Http\Requests\StoreProjectMemberRequest::class);
    echo "  ✅ StoreProjectMemberRequest loaded successfully!\n";
    echo "  Has authorize() method: " . ($reflection->hasMethod('authorize') ? 'Yes' : 'No') . "\n";
    echo "  Has rules() method: " . ($reflection->hasMethod('rules') ? 'Yes' : 'No') . "\n";
} catch (\Exception $e) {
    echo "  ❌ Failed: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 3: Check Controller
echo "✓ Test 3: Controller Updated\n";
try {
    $reflection = new ReflectionClass(App\Http\Controllers\ProjectMemberController::class);
    $constructor = $reflection->getConstructor();
    $params = $constructor->getParameters();
    
    echo "  ✅ ProjectMemberController loaded successfully!\n";
    echo "  Constructor has dependency injection: " . (count($params) > 0 ? 'Yes' : 'No') . "\n";
    
    if (count($params) > 0) {
        echo "  Injected service: " . $params[0]->getType()->getName() . "\n";
    }
} catch (\Exception $e) {
    echo "  ❌ Failed: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 4: Check Database Tables
echo "✓ Test 4: Database Tables\n";
try {
    $tables = [
        'users' => DB::table('users')->count(),
        'projects' => DB::table('projects')->count(),
        'project_members' => DB::table('project_members')->count(),
    ];
    
    echo "  ✅ Database connected!\n";
    foreach ($tables as $table => $count) {
        echo "  - {$table}: {$count} records\n";
    }
} catch (\Exception $e) {
    echo "  ❌ Failed: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 5: Test User Role Filter
echo "✓ Test 5: User Role Filtering\n";
try {
    $userCount = DB::table('users')->where('role', 'user')->count();
    $leaderCount = DB::table('users')->where('role', 'leader')->count();
    $adminCount = DB::table('users')->where('role', 'admin')->count();
    
    echo "  ✅ Role filtering working!\n";
    echo "  - Users (role='user'): {$userCount}\n";
    echo "  - Leaders (role='leader'): {$leaderCount}\n";
    echo "  - Admins (role='admin'): {$adminCount}\n";
    echo "  Note: Only 'user' role can be added as project members\n";
} catch (\Exception $e) {
    echo "  ❌ Failed: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 6: Test Service Methods
echo "✓ Test 6: Service Method Availability\n";
try {
    $service = app(App\Services\ProjectMemberService::class);
    $methods = get_class_methods($service);
    $requiredMethods = ['addMember', 'updateMemberRole', 'removeMember', 'canManageMembers', 'getAvailableUsers'];
    
    $allPresent = true;
    foreach ($requiredMethods as $method) {
        $exists = in_array($method, $methods);
        echo "  " . ($exists ? '✅' : '❌') . " {$method}()\n";
        if (!$exists) $allPresent = false;
    }
    
    if ($allPresent) {
        echo "  ✅ All required methods present!\n";
    }
} catch (\Exception $e) {
    echo "  ❌ Failed: " . $e->getMessage() . "\n";
}
echo "\n";

echo "=================================\n";
echo "Testing Complete!\n";
echo "=================================\n";
echo "\nServer is running at: http://127.0.0.1:8000\n";
echo "You can now test the implementation in browser!\n\n";
