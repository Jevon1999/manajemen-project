<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BoardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,project_id'
        ]);
        
        // Ambil board berdasarkan project_id
        $boards = Board::where('project_id', $request->project_id)
            ->orderBy('position')
            ->with(['cards' => function($query) {
                $query->orderBy('priority', 'desc')
                    ->orderBy('due_date');
            }])
            ->get();
            
        return response()->json([
            'boards' => $boards
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Tidak diperlukan untuk API
        return response()->json(['message' => 'Method not supported'], 405);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,project_id',
            'board_name' => 'required|string|max:100',
            'description' => 'nullable|string',
        ]);
        
        // Cek apakah user memiliki akses ke project
        $project = Project::findOrFail($request->project_id);
        $userId = Auth::id();
        
        if (!$this->userCanModifyProject($userId, $project)) {
            return response()->json(['message' => 'Tidak memiliki akses ke proyek ini'], 403);
        }
        
        // Hitung posisi terbaru
        $maxPosition = Board::where('project_id', $request->project_id)->max('position') ?? 0;
        
        $board = new Board();
        $board->project_id = $request->project_id;
        $board->board_name = $request->board_name;
        $board->description = $request->description;
        $board->position = $maxPosition + 1;
        $board->save();
        
        return response()->json([
            'message' => 'Papan berhasil dibuat',
            'board' => $board
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $board = Board::with([
            'cards' => function($query) {
                $query->orderBy('status')
                    ->orderBy('priority', 'desc')
                    ->orderBy('due_date');
            },
            'cards.creator:user_id,username,full_name',
            'cards.assignments.user:user_id,username,full_name'
        ])->findOrFail($id);
        
        // Statistik kartu per status menggunakan grup
        $cardStats = DB::table('cards')
            ->where('board_id', $id)
            ->select(
                'status',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(estimated_hours) as total_estimated_hours'),
                DB::raw('SUM(actual_hours) as total_actual_hours')
            )
            ->groupBy('status')
            ->get();
        
        return response()->json([
            'board' => $board,
            'card_statistics' => $cardStats
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        // Tidak diperlukan untuk API
        return response()->json(['message' => 'Method not supported'], 405);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $board = Board::findOrFail($id);
        
        $request->validate([
            'board_name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'position' => 'nullable|integer|min:0',
        ]);
        
        // Cek apakah user memiliki akses ke project
        $project = Project::findOrFail($board->project_id);
        $userId = Auth::id();
        
        if (!$this->userCanModifyProject($userId, $project)) {
            return response()->json(['message' => 'Tidak memiliki akses ke proyek ini'], 403);
        }
        
        $board->board_name = $request->board_name;
        $board->description = $request->description;
        
        // Update posisi jika ada perubahan
        if ($request->has('position') && $request->position != $board->position) {
            $board->position = $request->position;
            
            // Reorganisasi posisi board lain jika diperlukan
            $this->reorderBoardPositions($board->project_id);
        }
        
        $board->save();
        
        return response()->json([
            'message' => 'Papan berhasil diperbarui',
            'board' => $board
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $board = Board::findOrFail($id);
        
        // Cek apakah user memiliki akses ke project
        $project = Project::findOrFail($board->project_id);
        $userId = Auth::id();
        
        if (!$this->userCanModifyProject($userId, $project)) {
            return response()->json(['message' => 'Tidak memiliki akses ke proyek ini'], 403);
        }
        
        // Hapus board (cards akan terhapus karena cascade)
        $board->delete();
        
        // Reorganisasi posisi board lain
        $this->reorderBoardPositions($board->project_id);
        
        return response()->json([
            'message' => 'Papan berhasil dihapus'
        ]);
    }
    
    /**
     * Reorder positions of boards for a project
     */
    private function reorderBoardPositions($projectId)
    {
        $boards = Board::where('project_id', $projectId)
            ->orderBy('position')
            ->get();
            
        $position = 1;
        foreach ($boards as $board) {
            $board->position = $position++;
            $board->save();
        }
    }
    
    /**
     * Check if user can modify the project
     */
    private function userCanModifyProject($userId, $project)
    {
        // Admin dapat mengakses semua
        if (Auth::user()->role === 'admin') {
            return true;
        }
        
        // Creator dapat mengakses
        if ($project->created_by === $userId) {
            return true;
        }
        
        // Team lead dapat mengakses
        $isMember = DB::table('project_members')
            ->where('project_id', $project->project_id)
            ->where('user_id', $userId)
            ->where('role', 'team_lead')
            ->exists();
            
        return $isMember;
    }
    
    /**
     * Update posisi beberapa board sekaligus (drag-drop reordering)
     */
    public function updatePositions(Request $request)
    {
        $request->validate([
            'boards' => 'required|array',
            'boards.*.board_id' => 'required|exists:boards,board_id',
            'boards.*.position' => 'required|integer|min:0',
        ]);
        
        // Ambil project_id dari salah satu board
        $firstBoardId = $request->boards[0]['board_id'];
        $board = Board::findOrFail($firstBoardId);
        $projectId = $board->project_id;
        
        // Cek akses
        $project = Project::findOrFail($projectId);
        $userId = Auth::id();
        
        if (!$this->userCanModifyProject($userId, $project)) {
            return response()->json(['message' => 'Tidak memiliki akses ke proyek ini'], 403);
        }
        
        // Update posisi semua board
        DB::transaction(function() use ($request) {
            foreach ($request->boards as $boardData) {
                Board::where('board_id', $boardData['board_id'])
                    ->update(['position' => $boardData['position']]);
            }
        });
        
        return response()->json([
            'message' => 'Posisi papan berhasil diperbarui'
        ]);
    }
}
