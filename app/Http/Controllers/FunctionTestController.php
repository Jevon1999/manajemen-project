<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FunctionTestController extends Controller
{
    /**
     * Menguji fungsi database untuk menghitung tingkat penyelesaian proyek
     */
    public function testCompletionRate($projectId)
    {
        $result = DB::select(
            'SELECT calculate_project_completion_rate(?) as completion_rate',
            [$projectId]
        );
        
        return response()->json([
            'project_id' => $projectId,
            'completion_rate' => $result[0]->completion_rate ?? 0
        ]);
    }
    
    /**
     * Menguji fungsi database untuk menghitung total jam proyek
     */
    public function testTotalHours($projectId)
    {
        $result = DB::select(
            'SELECT calculate_total_project_hours(?) as total_hours',
            [$projectId]
        );
        
        return response()->json([
            'project_id' => $projectId,
            'total_hours' => $result[0]->total_hours ?? 0
        ]);
    }
    
    /**
     * Menguji trigger dengan melakukan insert ke card_assignments
     */
    public function testTriggerAssignment(Request $request)
    {
        $request->validate([
            'card_id' => 'required|exists:cards,card_id',
            'user_id' => 'required|exists:users,user_id',
        ]);
        
        // Ambil status pengguna sebelum trigger
        $beforeStatus = DB::table('users')
            ->where('user_id', $request->user_id)
            ->value('current_task_status');
        
        // Insert assignment baru untuk menjalankan trigger
        DB::table('card_assignments')->insert([
            'card_id' => $request->card_id,
            'user_id' => $request->user_id,
            'assigned_at' => now(),
            'assignment_status' => 'assigned',
        ]);
        
        // Ambil status pengguna setelah trigger
        $afterStatus = DB::table('users')
            ->where('user_id', $request->user_id)
            ->value('current_task_status');
        
        return response()->json([
            'message' => 'Trigger dijalankan',
            'before_status' => $beforeStatus,
            'after_status' => $afterStatus,
            'trigger_worked' => $afterStatus === 'working'
        ]);
    }
    
    /**
     * Menguji semua function dan join
     */
    public function testAllFunctions()
    {
        // Statistik semua proyek dengan fungsi dan join
        $projectStats = DB::table('projects')
            ->select(
                'projects.project_id',
                'projects.project_name',
                'users.username as creator_name',
                DB::raw('(SELECT calculate_project_completion_rate(projects.project_id)) as completion_rate'),
                DB::raw('(SELECT calculate_total_project_hours(projects.project_id)) as total_hours'),
                DB::raw('(SELECT COUNT(*) FROM project_members WHERE project_members.project_id = projects.project_id) as member_count'),
                DB::raw('(SELECT COUNT(*) FROM boards WHERE boards.project_id = projects.project_id) as board_count'),
                DB::raw('(SELECT COUNT(*) FROM cards JOIN boards ON cards.board_id = boards.board_id WHERE boards.project_id = projects.project_id) as card_count')
            )
            ->join('users', 'projects.created_by', '=', 'users.user_id')
            ->get();
        
        return response()->json([
            'project_statistics' => $projectStats
        ]);
    }
}
