<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "All Users in Database:\n";
echo "=====================================\n";

$users = \App\Models\User::select('user_id', 'full_name', 'email', 'role')->get();

foreach ($users as $user) {
    echo "ID: {$user->user_id} | Name: {$user->full_name} | Role: {$user->role}\n";
}

echo "\n";
echo "Users with 'leader' role: " . \App\Models\User::where('role', 'leader')->count() . "\n";
echo "Users with 'team_lead' role: " . \App\Models\User::where('role', 'team_lead')->count() . "\n";
echo "Users with 'project_manager' role: " . \App\Models\User::where('role', 'project_manager')->count() . "\n";
