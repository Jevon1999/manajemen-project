<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\WorkSession;
use App\Models\ProjectMember;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TaskManagementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buat user admin
        $admin = User::create([
            'username' => 'admin',
            'full_name' => 'Administrator',
            'name' => 'Administrator',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);
        
        // Buat leader
        $leader = User::create([
            'username' => 'leader',
            'full_name' => 'Leader One',
            'name' => 'Leader One',
            'email' => 'leader@example.com',
            'password' => Hash::make('password'),
            'role' => 'leader',
        ]);
        
        // Buat beberapa developer
        $dev1 = User::create([
            'username' => 'johndoe',
            'full_name' => 'John Doe',
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password'),
            'role' => 'developer',
            'specialty' => 'developer',
        ]);
        
        $dev2 = User::create([
            'username' => 'janesmith',
            'full_name' => 'Jane Smith',
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'password' => Hash::make('password'),
            'role' => 'developer',
            'specialty' => 'developer',
        ]);
        
        // Buat designer
        $designer = User::create([
            'username' => 'bobwilson',
            'full_name' => 'Bob Wilson',
            'name' => 'Bob Wilson',
            'email' => 'bob@example.com',
            'password' => Hash::make('password'),
            'role' => 'designer',
            'specialty' => 'designer',
        ]);
        
        // Buat user biasa
        $user1 = User::create([
            'username' => 'alicejohnson',
            'full_name' => 'Alice Johnson',
            'name' => 'Alice Johnson',
            'email' => 'alice@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);
        
        // Buat proyek 1
        $project1 = Project::create([
            'project_name' => 'Website E-Commerce',
            'description' => 'Membuat website e-commerce untuk toko XYZ dengan fitur lengkap',
            'leader_id' => $leader->user_id,
            'created_by' => $leader->user_id,
            'deadline' => Carbon::now()->addMonths(2),
            'status' => 'active',
        ]);
        
        // Tambahkan anggota proyek 1
        ProjectMember::create([
            'project_id' => $project1->project_id,
            'user_id' => $dev1->user_id,
            'role' => 'developer',
        ]);
        
        ProjectMember::create([
            'project_id' => $project1->project_id,
            'user_id' => $dev2->user_id,
            'role' => 'developer',
        ]);
        
        ProjectMember::create([
            'project_id' => $project1->project_id,
            'user_id' => $designer->user_id,
            'role' => 'designer',
        ]);
        
        // Buat tasks untuk project 1
        $task1 = Task::create([
            'project_id' => $project1->project_id,
            'title' => 'Setup Database Schema',
            'description' => 'Membuat database schema untuk e-commerce',
            'assigned_to' => $dev1->user_id,
            'created_by' => $leader->user_id,
            'status' => 'done',
            'priority' => 'high',
            'deadline' => Carbon::now()->subDays(20),
            'completed_at' => Carbon::now()->subDays(18),
        ]);
        
        $task2 = Task::create([
            'project_id' => $project1->project_id,
            'title' => 'Design Homepage UI',
            'description' => 'Membuat design mockup untuk homepage',
            'assigned_to' => $designer->user_id,
            'created_by' => $leader->user_id,
            'status' => 'done',
            'priority' => 'high',
            'deadline' => Carbon::now()->subDays(15),
            'completed_at' => Carbon::now()->subDays(12),
        ]);
        
        $task3 = Task::create([
            'project_id' => $project1->project_id,
            'title' => 'Implement User Authentication',
            'description' => 'Membuat sistem login, register, dan forgot password',
            'assigned_to' => $dev1->user_id,
            'created_by' => $leader->user_id,
            'status' => 'done',
            'priority' => 'high',
            'deadline' => Carbon::now()->subDays(10),
            'completed_at' => Carbon::now()->subDays(8),
        ]);
        
        $task4 = Task::create([
            'project_id' => $project1->project_id,
            'title' => 'Create Product Catalog',
            'description' => 'Membuat halaman katalog produk dengan filter dan search',
            'assigned_to' => $dev2->user_id,
            'created_by' => $leader->user_id,
            'status' => 'in_progress',
            'priority' => 'high',
            'deadline' => Carbon::now()->addDays(7),
        ]);
        
        $task5 = Task::create([
            'project_id' => $project1->project_id,
            'title' => 'Design Product Detail Page',
            'description' => 'Membuat design untuk halaman detail produk',
            'assigned_to' => $designer->user_id,
            'created_by' => $leader->user_id,
            'status' => 'review',
            'priority' => 'medium',
            'deadline' => Carbon::now()->addDays(5),
        ]);
        
        $task6 = Task::create([
            'project_id' => $project1->project_id,
            'title' => 'Implement Shopping Cart',
            'description' => 'Membuat fitur shopping cart dan checkout',
            'assigned_to' => $dev1->user_id,
            'created_by' => $leader->user_id,
            'status' => 'todo',
            'priority' => 'high',
            'deadline' => Carbon::now()->addDays(14),
        ]);
        
        // Buat work sessions untuk tasks yang sudah done
        WorkSession::create([
            'user_id' => $dev1->user_id,
            'task_id' => $task1->task_id,
            'work_date' => Carbon::now()->subDays(20),
            'started_at' => Carbon::now()->subDays(20)->setTime(9, 0, 0),
            'stopped_at' => Carbon::now()->subDays(20)->setTime(12, 30, 0),
            'duration_seconds' => 12600, // 3.5 jam
            'status' => 'completed',
        ]);
        
        WorkSession::create([
            'user_id' => $dev1->user_id,
            'task_id' => $task1->task_id,
            'work_date' => Carbon::now()->subDays(19),
            'started_at' => Carbon::now()->subDays(19)->setTime(13, 0, 0),
            'stopped_at' => Carbon::now()->subDays(19)->setTime(17, 0, 0),
            'duration_seconds' => 14400, // 4 jam
            'status' => 'completed',
        ]);
        
        WorkSession::create([
            'user_id' => $designer->user_id,
            'task_id' => $task2->task_id,
            'work_date' => Carbon::now()->subDays(15),
            'started_at' => Carbon::now()->subDays(15)->setTime(10, 0, 0),
            'stopped_at' => Carbon::now()->subDays(15)->setTime(15, 0, 0),
            'duration_seconds' => 18000, // 5 jam
            'status' => 'completed',
        ]);
        
        WorkSession::create([
            'user_id' => $designer->user_id,
            'task_id' => $task2->task_id,
            'work_date' => Carbon::now()->subDays(14),
            'started_at' => Carbon::now()->subDays(14)->setTime(9, 0, 0),
            'stopped_at' => Carbon::now()->subDays(14)->setTime(12, 0, 0),
            'duration_seconds' => 10800, // 3 jam
            'status' => 'completed',
        ]);
        
        WorkSession::create([
            'user_id' => $dev1->user_id,
            'task_id' => $task3->task_id,
            'work_date' => Carbon::now()->subDays(10),
            'started_at' => Carbon::now()->subDays(10)->setTime(8, 30, 0),
            'stopped_at' => Carbon::now()->subDays(10)->setTime(13, 0, 0),
            'duration_seconds' => 16200, // 4.5 jam
            'status' => 'completed',
        ]);
        
        WorkSession::create([
            'user_id' => $dev1->user_id,
            'task_id' => $task3->task_id,
            'work_date' => Carbon::now()->subDays(9),
            'started_at' => Carbon::now()->subDays(9)->setTime(14, 0, 0),
            'stopped_at' => Carbon::now()->subDays(9)->setTime(18, 30, 0),
            'duration_seconds' => 16200, // 4.5 jam
            'status' => 'completed',
        ]);
        
        // Work sessions untuk task yang sedang in progress
        WorkSession::create([
            'user_id' => $dev2->user_id,
            'task_id' => $task4->task_id,
            'work_date' => Carbon::now()->subDays(2),
            'started_at' => Carbon::now()->subDays(2)->setTime(9, 0, 0),
            'stopped_at' => Carbon::now()->subDays(2)->setTime(17, 0, 0),
            'duration_seconds' => 28800, // 8 jam
            'status' => 'completed',
        ]);
        
        WorkSession::create([
            'user_id' => $dev2->user_id,
            'task_id' => $task4->task_id,
            'work_date' => Carbon::now()->subDays(1),
            'started_at' => Carbon::now()->subDays(1)->setTime(9, 0, 0),
            'stopped_at' => Carbon::now()->subDays(1)->setTime(14, 0, 0),
            'duration_seconds' => 18000, // 5 jam
            'status' => 'completed',
        ]);
        
        // Work sessions untuk task yang di review
        WorkSession::create([
            'user_id' => $designer->user_id,
            'task_id' => $task5->task_id,
            'work_date' => Carbon::now()->subDays(3),
            'started_at' => Carbon::now()->subDays(3)->setTime(10, 0, 0),
            'stopped_at' => Carbon::now()->subDays(3)->setTime(16, 0, 0),
            'duration_seconds' => 21600, // 6 jam
            'status' => 'completed',
        ]);
        
        // Buat proyek 2
        $project2 = Project::create([
            'project_name' => 'Aplikasi Mobile Inventaris',
            'description' => 'Aplikasi mobile untuk manajemen inventaris gudang',
            'leader_id' => $leader->user_id,
            'created_by' => $leader->user_id,
            'deadline' => Carbon::now()->addMonths(1),
            'status' => 'active',
        ]);
        
        // Tambahkan anggota proyek 2
        ProjectMember::create([
            'project_id' => $project2->project_id,
            'user_id' => $dev2->user_id,
            'role' => 'developer',
        ]);
        
        ProjectMember::create([
            'project_id' => $project2->project_id,
            'user_id' => $designer->user_id,
            'role' => 'designer',
        ]);
        
        // Buat tasks untuk project 2
        $task7 = Task::create([
            'project_id' => $project2->project_id,
            'title' => 'Setup Flutter Project',
            'description' => 'Inisialisasi project Flutter dengan struktur folder',
            'assigned_to' => $dev2->user_id,
            'created_by' => $leader->user_id,
            'status' => 'done',
            'priority' => 'high',
            'deadline' => Carbon::now()->subDays(12),
            'completed_at' => Carbon::now()->subDays(10),
        ]);
        
        $task8 = Task::create([
            'project_id' => $project2->project_id,
            'title' => 'Design App Screens',
            'description' => 'Membuat design untuk semua screen aplikasi',
            'assigned_to' => $designer->user_id,
            'created_by' => $leader->user_id,
            'status' => 'in_progress',
            'priority' => 'high',
            'deadline' => Carbon::now()->addDays(3),
        ]);
        
        $task9 = Task::create([
            'project_id' => $project2->project_id,
            'title' => 'Implement API Integration',
            'description' => 'Integrasi dengan backend API untuk data inventaris',
            'assigned_to' => $dev2->user_id,
            'created_by' => $leader->user_id,
            'status' => 'todo',
            'priority' => 'medium',
            'deadline' => Carbon::now()->addDays(10),
        ]);
        
        // Work sessions untuk project 2
        WorkSession::create([
            'user_id' => $dev2->user_id,
            'task_id' => $task7->task_id,
            'work_date' => Carbon::now()->subDays(12),
            'started_at' => Carbon::now()->subDays(12)->setTime(9, 0, 0),
            'stopped_at' => Carbon::now()->subDays(12)->setTime(11, 30, 0),
            'duration_seconds' => 9000, // 2.5 jam
            'status' => 'completed',
        ]);
        
        WorkSession::create([
            'user_id' => $designer->user_id,
            'task_id' => $task8->task_id,
            'work_date' => Carbon::now()->subDays(1),
            'started_at' => Carbon::now()->subDays(1)->setTime(13, 0, 0),
            'stopped_at' => Carbon::now()->subDays(1)->setTime(18, 0, 0),
            'duration_seconds' => 18000, // 5 jam
            'status' => 'completed',
        ]);
        
        // Work sessions untuk today (untuk testing)
        WorkSession::create([
            'user_id' => $dev1->user_id,
            'task_id' => $task6->task_id,
            'work_date' => Carbon::today(),
            'started_at' => Carbon::today()->setTime(9, 0, 0),
            'stopped_at' => Carbon::today()->setTime(12, 0, 0),
            'duration_seconds' => 10800, // 3 jam
            'status' => 'completed',
        ]);
        
        WorkSession::create([
            'user_id' => $dev2->user_id,
            'task_id' => $task4->task_id,
            'work_date' => Carbon::today(),
            'started_at' => Carbon::today()->setTime(10, 0, 0),
            'stopped_at' => Carbon::today()->setTime(15, 30, 0),
            'duration_seconds' => 19800, // 5.5 jam
            'status' => 'completed',
        ]);
    }
}
