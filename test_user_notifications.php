<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\User;
use App\Models\Notification;

// Initialize Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Test untuk user role notification access
echo "Testing User Role Notification Access\n";
echo "=====================================\n";

try {
    // Find a user with 'user' role (tidak admin/leader)
    $user = User::where('role', 'user')->first();
    
    if (!$user) {
        echo "❌ No user with role 'user' found\n";
        echo "Creating test user...\n";
        
        $user = User::create([
            'user_id' => 'USR' . time(),
            'full_name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
            'join_date' => now()
        ]);
        echo "✅ Test user created: {$user->full_name} ({$user->email})\n";
    } else {
        echo "✅ Found user: {$user->full_name} ({$user->email}) with role: {$user->role}\n";
    }
    
    // Check if user has any notifications
    $notifications = Notification::where('user_id', $user->user_id)->get();
    echo "📩 User has " . $notifications->count() . " total notifications\n";
    
    // Create a test notification if none exists
    if ($notifications->count() == 0) {
        $testNotification = Notification::create([
            'user_id' => $user->user_id,
            'type' => 'task_assigned',
            'title' => 'Test Notification',
            'message' => 'This is a test notification for user role',
            'data' => ['test' => true],
            'read_at' => null
        ]);
        echo "✅ Created test notification: {$testNotification->title}\n";
    }
    
    // Check unread count
    $unreadCount = Notification::where('user_id', $user->user_id)->unread()->count();
    echo "🔔 User has {$unreadCount} unread notifications\n";
    
    // Test recent notifications (like the endpoint does)
    $recentNotifications = Notification::where('user_id', $user->user_id)
        ->orderBy('created_at', 'desc')
        ->limit(10)
        ->get();
    
    echo "📋 Recent notifications for user:\n";
    foreach ($recentNotifications as $notification) {
        $status = $notification->isRead() ? '✓ Read' : '● Unread';
        echo "  - {$notification->title} ({$status}) - {$notification->created_at->diffForHumans()}\n";
    }
    
    // Test user permissions
    echo "\n👤 User Role Information:\n";
    echo "  - Role: {$user->role}\n";
    echo "  - Is User: " . ($user->role === 'user' ? 'Yes' : 'No') . "\n";
    echo "  - Is Developer: " . (method_exists($user, 'isDeveloper') && $user->isDeveloper() ? 'Yes' : 'No') . "\n";
    echo "  - Is Leader: " . (method_exists($user, 'isLeader') && $user->isLeader() ? 'Yes' : 'No') . "\n";
    echo "  - Is Admin: " . (method_exists($user, 'isAdmin') && $user->isAdmin() ? 'Yes' : 'No') . "\n";
    
    echo "\n✅ User role notification access test completed successfully\n";
    
} catch (Exception $e) {
    echo "❌ Error testing user notifications: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== End Test ===\n";