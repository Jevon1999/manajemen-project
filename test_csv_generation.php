<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ReportController;

echo "=== Testing CSV Generation ===\n\n";

// Login as admin
$admin = User::where('role', 'admin')->first();
if (!$admin) {
    echo "❌ No admin user found!\n";
    exit(1);
}

Auth::login($admin);
echo "✅ Logged in as: {$admin->full_name}\n\n";

// Test custom report generation
echo "--- Testing Custom Report Generation ---\n";
try {
    $controller = new ReportController();
    
    // Create a mock request
    $requestData = [
        'date_from' => now()->subMonth()->format('Y-m-d'),
        'date_to' => now()->format('Y-m-d'),
        'project_id' => null,
        'user_id' => null,
        'status' => null,
    ];
    
    $request = Request::create('/admin/reports/generate', 'POST', $requestData);
    
    echo "Request parameters:\n";
    echo "  - Date from: {$requestData['date_from']}\n";
    echo "  - Date to: {$requestData['date_to']}\n";
    echo "  - Project: All\n";
    echo "  - User: All\n";
    echo "  - Status: All\n\n";
    
    $response = $controller->generate($request);
    
    if ($response->getStatusCode() === 200) {
        echo "✅ CSV report generated successfully\n";
        echo "Content-Type: " . $response->headers->get('Content-Type') . "\n";
        echo "Content-Disposition: " . $response->headers->get('Content-Disposition') . "\n";
        
        // Get first few lines of CSV
        ob_start();
        $response->sendContent();
        $content = ob_get_clean();
        
        $lines = explode("\n", $content);
        echo "\nFirst 5 lines of CSV:\n";
        for ($i = 0; $i < min(5, count($lines)); $i++) {
            echo ($i + 1) . ": " . substr($lines[$i], 0, 100) . "...\n";
        }
        
        echo "\nTotal lines: " . count($lines) . "\n";
    } else {
        echo "❌ Failed: HTTP " . $response->getStatusCode() . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n✅ CSV generation test completed!\n";
