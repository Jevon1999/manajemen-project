<?php

namespace App\Http\Controllers;

use App\Models\Subtask;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SubtaskController extends Controller
{
    /**
     * Store a newly created subtask
     */
    public function store(Request $request, $taskId)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'nullable|in:low,medium,high',
        ]);
        
        $task = Task::findOrFail($taskId);
        
        // Validasi: User harus assigned ke task ini untuk membuat subtask
        if ($task->assigned_to !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda hanya dapat membuat subtask pada task yang di-assign ke Anda.'
            ], 403);
        }
        
        try {
            DB::beginTransaction();
            
            $subtask = Subtask::create([
                'task_id' => $taskId,
                'title' => $request->title,
                'description' => $request->description,
                'priority' => $request->priority ?? 'medium', // Default to medium if not provided
                'created_by' => Auth::id(),
                'is_completed' => false,
            ]);
            
            Log::info('Subtask created', [
                'subtask_id' => $subtask->subtask_id,
                'task_id' => $taskId,
                'created_by' => Auth::id(),
            ]);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Subtask berhasil ditambahkan.',
                'subtask' => $subtask->load('creator'),
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create subtask: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat subtask: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified subtask
     */
    public function update(Request $request, $taskId, $subtaskId)
    {
        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'sometimes|required|in:low,medium,high',
        ]);
        
        $subtask = Subtask::findOrFail($subtaskId);
        $task = $subtask->task;
        
        // Validasi: User harus assigned ke task ini
        if ($task->assigned_to !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk mengubah subtask ini.'
            ], 403);
        }
        
        // VALIDASI: Subtask yang sudah completed tidak bisa diubah
        if ($subtask->is_completed) {
            return response()->json([
                'success' => false,
                'message' => 'Subtask yang sudah selesai tidak dapat diubah.'
            ], 422);
        }
        
        try {
            $subtask->update($request->only(['title', 'description', 'priority']));
            
            return response()->json([
                'success' => true,
                'message' => 'Subtask berhasil diupdate.',
                'subtask' => $subtask->fresh(),
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal update subtask: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle subtask completion status
     */
    public function toggleComplete($taskId, $subtaskId)
    {
        $subtask = Subtask::findOrFail($subtaskId);
        $task = $subtask->task;
        
        // Validasi: User harus assigned ke task ini
        if ($task->assigned_to !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk mengubah subtask ini.'
            ], 403);
        }
        
        // VALIDASI: Subtask yang sudah completed tidak bisa diubah kembali menjadi incomplete
        if ($subtask->is_completed) {
            return response()->json([
                'success' => false,
                'message' => 'Subtask yang sudah selesai tidak dapat diubah kembali. Subtask bersifat final setelah diselesaikan.'
            ], 422);
        }
        
        try {
            // Hanya bisa mark as completed (tidak bisa toggle kembali)
            $subtask->markAsCompleted();
            $message = 'Subtask ditandai sebagai selesai!';
            
            // Get statistics
            $totalSubtasks = $task->subtasks()->count();
            $completedSubtasks = $task->subtasks()->completed()->count();
            $progress = $totalSubtasks > 0 ? round(($completedSubtasks / $totalSubtasks) * 100) : 0;
            
            return response()->json([
                'success' => true,
                'message' => $message,
                'subtask' => $subtask->fresh(),
                'statistics' => [
                    'total' => $totalSubtasks,
                    'completed' => $completedSubtasks,
                    'progress' => $progress,
                ],
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal update status subtask: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified subtask
     */
    public function destroy($taskId, $subtaskId)
    {
        $subtask = Subtask::findOrFail($subtaskId);
        $task = $subtask->task;
        
        // Validasi: User harus assigned ke task ini
        if ($task->assigned_to !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk menghapus subtask ini.'
            ], 403);
        }
        
        // VALIDASI: Subtask yang sudah completed tidak bisa dihapus
        if ($subtask->is_completed) {
            return response()->json([
                'success' => false,
                'message' => 'Subtask yang sudah selesai tidak dapat dihapus.'
            ], 422);
        }
        
        try {
            $subtask->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Subtask berhasil dihapus.',
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus subtask: ' . $e->getMessage()
            ], 500);
        }
    }
}
