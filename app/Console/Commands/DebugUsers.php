<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class DebugUsers extends Command
{
    protected $signature = 'debug:users';
    protected $description = 'Debug users information';

    public function handle()
    {
        $users = User::all();
        
        $this->info("All Users:");
        foreach ($users as $user) {
            $this->info("ID: {$user->user_id}, Name: {$user->name}, Email: {$user->email}, Role: {$user->role}");
        }
    }
}