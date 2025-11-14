<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

echo "=== LEADER PROJECT CREATE UI TEST ===\n\n";

// Find leader user
$leader = User::where('role', 'leader')->first();

if (!$leader) {
    echo "❌ ERROR: No leader found in database\n";
    exit(1);
}

echo "✅ Leader found: {$leader->full_name} (ID: {$leader->user_id})\n\n";

// Login as leader
Auth::login($leader);
echo "✅ Logged in as leader\n\n";

// Check route exists
echo "📍 Checking routes:\n";
$routes = collect(Route::getRoutes())->filter(function ($route) {
    $name = $route->getName();
    return $name && str_contains($name, 'leader.projects');
})->map(function($route) {
    return $route->getName() . ' => ' . $route->uri();
});

foreach ($routes as $route) {
    echo "   - $route\n";
}
echo "\n";

// Check view file exists
$viewPath = resource_path('views/leader/projects/create.blade.php');
echo "📁 View file path: $viewPath\n";

if (file_exists($viewPath)) {
    echo "✅ View file EXISTS\n";
    echo "   File size: " . filesize($viewPath) . " bytes\n\n";
    
    // Try to compile the view
    echo "🔧 Attempting to compile view...\n";
    try {
        $view = view('leader.projects.create');
        echo "✅ View compiled successfully!\n\n";
        
        // Try to render
        echo "🎨 Attempting to render view...\n";
        $html = $view->render();
        echo "✅ View rendered successfully!\n";
        echo "   HTML length: " . strlen($html) . " characters\n\n";
        
        // Check for key elements
        echo "🔍 Checking key form elements:\n";
        $checks = [
            'form action' => str_contains($html, 'action='),
            'name input' => str_contains($html, 'name="name"'),
            'description textarea' => str_contains($html, 'name="description"'),
            'deadline input' => str_contains($html, 'name="deadline"'),
            'category select' => str_contains($html, 'name="category"'),
            'submit button' => str_contains($html, 'type="submit"'),
        ];
        
        foreach ($checks as $element => $exists) {
            echo "   " . ($exists ? '✅' : '❌') . " $element\n";
        }
        
        echo "\n✅ TEST PASSED: Leader can access project create form!\n";
        
    } catch (\Exception $e) {
        echo "❌ ERROR compiling/rendering view:\n";
        echo "   " . $e->getMessage() . "\n";
        echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
        exit(1);
    }
    
} else {
    echo "❌ ERROR: View file does NOT exist!\n";
    exit(1);
}
