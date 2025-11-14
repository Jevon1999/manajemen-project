<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ReportController;

$admin = User::where('role', 'admin')->first();
Auth::login($admin);

$controller = new ReportController();
$reflection = new ReflectionClass($controller);
$method = $reflection->getMethod('getMonthlyData');
$method->setAccessible(true);

echo "=== Monthly Data Details ===\n\n";
$data = $method->invoke($controller);

foreach($data as $month) {
    echo sprintf(
        "%-12s | Tasks: %-3d | Completed: %-3d | Rate: %-6s | Hours: %-8s\n",
        $month['month'],
        $month['total_tasks'],
        $month['completed_tasks'],
        $month['completion_rate'] . '%',
        $month['work_hours'] . 'h'
    );
}
