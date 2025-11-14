<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ReportController;

echo "=== Testing Reports Routes ===\n\n";

// Login as admin
$admin = User::where('role', 'admin')->first();
if (!$admin) {
    echo "❌ No admin user found!\n";
    exit(1);
}

Auth::login($admin);
echo "✅ Logged in as: {$admin->full_name}\n\n";

// Test index route
echo "--- Testing General Dashboard ---\n";
try {
    $controller = new ReportController();
    $request = Request::create('/admin/reports', 'GET', ['type' => 'general']);
    
    $response = $controller->index($request);
    echo "✅ General dashboard loaded successfully\n";
    echo "View: " . $response->name() . "\n";
    
    $data = $response->getData();
    echo "Data keys: " . implode(', ', array_keys($data)) . "\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}

// Test monthly report
echo "\n--- Testing Monthly Report ---\n";
try {
    $request = Request::create('/admin/reports', 'GET', ['type' => 'monthly']);
    $response = $controller->index($request);
    echo "✅ Monthly report loaded successfully\n";
    
    $data = $response->getData();
    if (isset($data['monthlyData'])) {
        echo "Monthly data count: " . count($data['monthlyData']) . " months\n";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

// Test yearly report
echo "\n--- Testing Yearly Report ---\n";
try {
    $request = Request::create('/admin/reports', 'GET', ['type' => 'yearly']);
    $response = $controller->index($request);
    echo "✅ Yearly report loaded successfully\n";
    
    $data = $response->getData();
    if (isset($data['yearlyData'])) {
        echo "Yearly data count: " . count($data['yearlyData']) . " years\n";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

// Test project report
echo "\n--- Testing Project Report ---\n";
try {
    $request = Request::create('/admin/reports', 'GET', ['type' => 'project']);
    $response = $controller->index($request);
    echo "✅ Project report loaded successfully\n";
    
    $data = $response->getData();
    if (isset($data['projectData'])) {
        echo "Project data count: " . count($data['projectData']) . " projects\n";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n✅ All route tests completed!\n";
echo "\n📝 Now you can access:\n";
echo "   - http://localhost:8000/admin/reports (General Dashboard)\n";
echo "   - http://localhost:8000/admin/reports?type=monthly (Monthly Reports)\n";
echo "   - http://localhost:8000/admin/reports?type=yearly (Yearly Reports)\n";
echo "   - http://localhost:8000/admin/reports?type=project (Project Reports)\n";
