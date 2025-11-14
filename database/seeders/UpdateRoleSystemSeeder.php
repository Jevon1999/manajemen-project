<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UpdateRoleSystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tambah leader baru jika belum ada
        $leader1 = User::firstOrCreate(
            ['email' => 'leader1@example.com'],
            [
                'username' => 'teamlead1',
                'full_name' => 'Team Leader 1',
                'password' => Hash::make('password'),
                'role' => 'leader',
            ]
        );
        
        $leader2 = User::firstOrCreate(
            ['email' => 'leader2@example.com'],
            [
                'username' => 'teamlead2',
                'full_name' => 'Team Leader 2',
                'password' => Hash::make('password'),
                'role' => 'leader',
            ]
        );
        
        // Tambah developers dan designers
        $developer1 = User::firstOrCreate(
            ['email' => 'dev1@example.com'],
            [
                'username' => 'dev1',
                'full_name' => 'Developer 1',
                'password' => Hash::make('password'),
                'role' => 'user',
            ]
        );
        
        $designer1 = User::firstOrCreate(
            ['email' => 'designer1@example.com'],
            [
                'username' => 'designer1',
                'full_name' => 'UI/UX Designer 1',
                'password' => Hash::make('password'),
                'role' => 'user',
            ]
        );
        
        echo "✅ Role system updated successfully!\n";
        echo "Admin: admin@example.com\n";
        echo "Leaders: leader1@example.com, leader2@example.com\n";
        echo "Users: dev1@example.com, designer1@example.com\n";
        echo "Password untuk semua: password\n";
    }
}
