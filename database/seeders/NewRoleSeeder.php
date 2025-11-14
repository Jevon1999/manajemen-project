<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Project;
use App\Models\ProjectMember;
use Illuminate\Support\Facades\Hash;

class NewRoleSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Update existing users dengan role baru
        
        // Admin (user_id = 1)
        User::where('user_id', 1)->update(['role' => 'admin']);
        
        // Leader (user_id = 2, 3)
        User::whereIn('user_id', [2, 3])->update(['role' => 'leader']);
        
        // Regular users (sisanya)
        User::where('user_id', '>', 3)->update(['role' => 'user']);
        
        // Tambah leader baru
        $leader1 = User::create([
            'username' => 'teamlead1',
            'email' => 'leader1@example.com',
            'full_name' => 'Team Leader 1',
            'password' => Hash::make('password'),
            'role' => 'leader',
            'status' => 'active',
        ]);
        
        $leader2 = User::create([
            'username' => 'teamlead2',
            'email' => 'leader2@example.com',
            'full_name' => 'Team Leader 2',
            'password' => Hash::make('password'),
            'role' => 'leader',
            'status' => 'active',
        ]);
        
        // Tambah developers dan designers
        $developer1 = User::create([
            'username' => 'dev1',
            'email' => 'dev1@example.com',
            'full_name' => 'Developer 1',
            'password' => Hash::make('password'),
            'role' => 'user',
            'status' => 'active',
        ]);
        
        $designer1 = User::create([
            'username' => 'designer1',
            'email' => 'designer1@example.com',
            'full_name' => 'UI/UX Designer 1',
            'password' => Hash::make('password'),
            'role' => 'user',
            'status' => 'active',
        ]);
        
        // Update project members dengan role baru
        // Project 1 - assign leader1 sebagai project manager
        ProjectMember::where('project_id', 1)->where('user_id', 2)->update(['role' => 'project_manager']);
        
        // Tambah members baru ke project
        ProjectMember::create([
            'project_id' => 1,
            'user_id' => $developer1->user_id,
            'role' => 'developer',
            'joined_at' => now(),
        ]);
        
        ProjectMember::create([
            'project_id' => 1,
            'user_id' => $designer1->user_id,
            'role' => 'designer',
            'joined_at' => now(),
        ]);
        
        // Project 2 - assign leader2 sebagai project manager
        if (Project::where('project_id', 2)->exists()) {
            ProjectMember::create([
                'project_id' => 2,
                'user_id' => $leader2->user_id,
                'role' => 'project_manager',
                'joined_at' => now(),
            ]);
        }
        
        echo "✅ Role system updated successfully!\n";
        echo "Admin: admin@example.com\n";
        echo "Leaders: leader1@example.com, leader2@example.com\n";
        echo "Users: dev1@example.com, designer1@example.com\n";
        echo "Password untuk semua: password\n";
    }
}
