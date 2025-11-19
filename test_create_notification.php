<?php

// Simple test untuk create notification langsung di database
// Jangan jalankan jika sudah ada notifikasi!

$host = 'localhost';
$database = 'your_database_name'; // Ubah sesuai database
$username = 'your_username';       // Ubah sesuai username
$password = 'your_password';       // Ubah sesuai password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Testing User Role Notifications\n";
    echo "===============================\n";
    
    // Get a user with role 'user'
    $stmt = $pdo->prepare("SELECT user_id, full_name, email, role FROM users WHERE role = 'user' LIMIT 1");
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo "❌ No user found with role 'user'\n";
        echo "Please create a user with role 'user' first\n";
        exit(1);
    }
    
    echo "✅ Found user: {$user['full_name']} ({$user['email']}) with role: {$user['role']}\n";
    
    // Check existing notifications
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = ?");
    $stmt->execute([$user['user_id']]);
    $existingCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    echo "📩 User has {$existingCount} total notifications\n";
    
    // Create a test notification for user role
    $stmt = $pdo->prepare("
        INSERT INTO notifications (user_id, type, title, message, data, read_at, created_at, updated_at) 
        VALUES (?, 'task_assigned', 'Test Notification for User Role', 'This is a test notification to verify user role can see notifications', '{}', NULL, NOW(), NOW())
    ");
    
    if ($stmt->execute([$user['user_id']])) {
        echo "✅ Test notification created successfully\n";
    } else {
        echo "❌ Failed to create test notification\n";
    }
    
    // Check unread count
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND read_at IS NULL");
    $stmt->execute([$user['user_id']]);
    $unreadCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    echo "🔔 User now has {$unreadCount} unread notifications\n";
    
    // Get recent notifications
    $stmt = $pdo->prepare("
        SELECT id, type, title, message, read_at, created_at 
        FROM notifications 
        WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT 5
    ");
    $stmt->execute([$user['user_id']]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "📋 Recent notifications:\n";
    foreach ($notifications as $notif) {
        $status = $notif['read_at'] ? '✓ Read' : '● Unread';
        echo "  - {$notif['title']} ({$status})\n";
    }
    
    echo "\n✅ Test completed. User should now see notifications in the app.\n";
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    echo "Please update database connection details in this file.\n";
}