<?php

namespace Database\Seeders;

use App\Models\Board;
use App\Models\Card;
use App\Models\CardAssignment;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\Subtask;
use App\Models\TimeLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ProjectManagementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buat user admin
        $admin = User::create([
            'name' => 'Administrator',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);
        
        // Buat leader
        $leader = User::create([
            'name' => 'Leader One',
            'email' => 'leader@example.com',
            'password' => Hash::make('password'),
            'role' => 'leader',
        ]);
        
        // Buat beberapa developer
        $dev1 = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password'),
            'role' => 'developer',
            'specialty' => 'Backend',
        ]);
        
        $dev2 = User::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => Hash::make('password'),
            'role' => 'developer',
            'specialty' => 'Frontend',
        ]);
        
        // Buat designer
        $designer = User::create([
            'name' => 'Bob Smith',
            'email' => 'bob@example.com',
            'password' => Hash::make('password'),
            'role' => 'designer',
            'specialty' => 'UI/UX',
        ]);
        
        // Buat user biasa
        $user1 = User::create([
            'name' => 'Alice Johnson',
            'email' => 'alice@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);
        
        // Buat proyek
        $project1 = Project::create([
            'name' => 'Website E-Commerce',
            'description' => 'Membuat website e-commerce untuk toko XYZ',
            'leader_id' => $leader->user_id,
            'start_date' => Carbon::now(),
            'end_date' => Carbon::now()->addMonths(3),
            'status' => 'active',
        ]);
        
        // Tambahkan anggota proyek
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
        
        // Buat boards untuk proyek
        $board1 = Board::create([
            'project_id' => $project1->project_id,
            'board_name' => 'To Do',
            'description' => 'Tugas yang akan dikerjakan',
            'position' => 1,
        ]);
        
        $board2 = Board::create([
            'project_id' => $project1->project_id,
            'board_name' => 'In Progress',
            'description' => 'Tugas yang sedang dikerjakan',
            'position' => 2,
        ]);
        
        $board3 = Board::create([
            'project_id' => $project1->project_id,
            'board_name' => 'Review',
            'description' => 'Tugas yang perlu ditinjau',
            'position' => 3,
        ]);
        
        $board4 = Board::create([
            'project_id' => $project1->project_id,
            'board_name' => 'Done',
            'description' => 'Tugas yang telah selesai',
            'position' => 4,
        ]);
        
        // Buat cards untuk board
        $card1 = Card::create([
            'board_id' => $board1->board_id,
            'card_title' => 'Setup Database',
            'description' => 'Membuat database dan migrasi untuk aplikasi',
            'created_by' => $user1->user_id,
            'due_date' => Carbon::now()->addDays(5),
            'status' => 'todo',
            'priority' => 'high',
            'estimated_hours' => 4.5,
        ]);
        
        $card2 = Card::create([
            'board_id' => $board2->board_id,
            'card_title' => 'Design Homepage',
            'description' => 'Membuat desain untuk halaman utama website',
            'created_by' => $user1->user_id,
            'due_date' => Carbon::now()->addDays(10),
            'status' => 'in_progress',
            'priority' => 'medium',
            'estimated_hours' => 8,
            'actual_hours' => 4.5,
        ]);
        
        $card3 = Card::create([
            'board_id' => $board3->board_id,
            'card_title' => 'User Authentication',
            'description' => 'Implementasi sistem login dan registrasi',
            'created_by' => $user1->user_id,
            'due_date' => Carbon::now()->addDays(3),
            'status' => 'review',
            'priority' => 'high',
            'estimated_hours' => 6,
            'actual_hours' => 5.5,
        ]);
        
        $card4 = Card::create([
            'board_id' => $board4->board_id,
            'card_title' => 'Setup Project',
            'description' => 'Inisialisasi proyek dan setup environment',
            'created_by' => $user1->user_id,
            'due_date' => Carbon::now()->subDays(2),
            'status' => 'done',
            'priority' => 'medium',
            'estimated_hours' => 2,
            'actual_hours' => 1.5,
        ]);
        
        // Buat subtasks
        Subtask::create([
            'card_id' => $card1->card_id,
            'subtaks_title' => 'Membuat skema database',
            'description' => 'Membuat skema relasi entitas',
            'status' => 'todo',
            'estimated_hours' => 2,
            'position' => 1,
        ]);
        
        Subtask::create([
            'card_id' => $card1->card_id,
            'subtaks_title' => 'Membuat migrasi',
            'description' => 'Implementasi migrasi database di Laravel',
            'status' => 'todo',
            'estimated_hours' => 2.5,
            'position' => 2,
        ]);
        
        // Assign tugas
        CardAssignment::create([
            'card_id' => $card1->card_id,
            'user_id' => $user2->user_id,
            'assignment_status' => 'assigned',
        ]);
        
        CardAssignment::create([
            'card_id' => $card2->card_id,
            'user_id' => $user3->user_id,
            'assignment_status' => 'in_progress',
            'started_at' => Carbon::now()->subDay(),
        ]);
        
        CardAssignment::create([
            'card_id' => $card3->card_id,
            'user_id' => $user2->user_id,
            'assignment_status' => 'completed',
            'started_at' => Carbon::now()->subDays(2),
            'completed_at' => Carbon::now()->subHours(5),
        ]);
        
        // Buat log waktu
        TimeLog::create([
            'card_id' => $card2->card_id,
            'user_id' => $user3->user_id,
            'start_time' => Carbon::now()->subDays(1)->subHours(4),
            'end_time' => Carbon::now()->subDays(1)->subHours(2),
            'duration_minutes' => 120,
            'description' => 'Mulai mengerjakan wireframe homepage',
        ]);
        
        TimeLog::create([
            'card_id' => $card2->card_id,
            'user_id' => $user3->user_id,
            'start_time' => Carbon::now()->subHours(5),
            'end_time' => Carbon::now()->subHours(2),
            'duration_minutes' => 180,
            'description' => 'Melanjutkan desain dengan Figma',
        ]);
        
        // Buat proyek kedua
        $project2 = Project::create([
            'name' => 'Aplikasi Mobile',
            'description' => 'Membuat aplikasi mobile untuk manajemen inventaris',
            'leader_id' => $leader->user_id,
            'start_date' => Carbon::now(),
            'end_date' => Carbon::now()->addMonths(2),
            'status' => 'active',
        ]);
        
        // Tambahkan anggota proyek kedua
        ProjectMember::create([
            'project_id' => $project2->project_id,
            'user_id' => $dev1->user_id,
            'role' => 'developer',
        ]);
        
        ProjectMember::create([
            'project_id' => $project2->project_id,
            'user_id' => $designer->user_id,
            'role' => 'designer',
        ]);
        
        // Buat boards untuk proyek kedua
        $boardA = Board::create([
            'project_id' => $project2->project_id,
            'board_name' => 'Backlog',
            'description' => 'Semua tugas yang perlu dikerjakan',
            'position' => 1,
        ]);
        
        $boardB = Board::create([
            'project_id' => $project2->project_id,
            'board_name' => 'Sprint',
            'description' => 'Tugas sprint saat ini',
            'position' => 2,
        ]);
        
        $boardC = Board::create([
            'project_id' => $project2->project_id,
            'board_name' => 'Completed',
            'description' => 'Tugas yang telah selesai',
            'position' => 3,
        ]);
    }
}
