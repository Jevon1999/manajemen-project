<?php

require_once __DIR__ . '/bootstrap/app.php';

use App\Models\User;

$adminCount = User::where('role', 'admin')->count();
echo "Admin users: $adminCount\n";

echo "Test completed.\n";