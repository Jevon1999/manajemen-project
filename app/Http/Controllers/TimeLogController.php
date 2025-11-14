<?php

namespace App\Http\Controllers;

use App\Models\TimeLog;
use App\Models\Task;
use App\Services\BoardTransitionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class TimeLogController extends Controller
{
    protected $boardService;

    public function __construct(BoardTransitionService $boardService)
    {
        $this->boardService = $boardService;
    }

    /**
     * Start timer for a task
     */
    public function start(Request $request, $taskId)
    {
        $task = Task::findOrFail($taskId);
        
        // Check if user is assigned to this task
        if ($task->assigned_to !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak di-assign ke task ini.'
            ], 403);
        }
        
        // Check if user already has a running timer on ANY task
        $runningTimer = TimeLog::where('user_id', Auth::id())
                               ->whereNull('end_time')
                               ->first();
        
        if ($runningTimer) {
            $runningTask = $runningTimer->task;
            return response()->json([
                'success' => false,
                'message' => "Anda masih memiliki timer yang berjalan di task: {$runningTask->title}. Hentikan timer tersebut terlebih dahulu.",
                'running_task_id' => $runningTask->task_id,
                'running_task_title' => $runningTask->title,
            ], 400);
        }
        
        try {
            DB::beginTransaction();
            
            // Create new time log
            $timeLog = TimeLog::create([
                'task_id' => $taskId,
                'user_id' => Auth::id(),
                'start_time' => now(),
            ]);
            
            // Auto-transition task status to 'in_progress' if it's 'todo'
            if ($task->status === Task::STATUS_TODO) {
                $transitionResult = $this->boardService->transitionToInProgress($task);
                if (!$transitionResult['success']) {
                    DB::rollBack();
                    return response()->json(['error' => $transitionResult['message']], 400);
                }
            }
            
            Log::info('Timer started', [
                'timelog_id' => $timeLog->timelog_id,
                'task_id' => $taskId,
                'user_id' => Auth::id(),
            ]);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Timer dimulai!',
                'timelog' => $timeLog,
                'task_status' => $task->status,
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to start timer: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal memulai timer: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Stop running timer for a task
     */
    public function stop(Request $request, $taskId)
    {
        $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);
        
        $task = Task::findOrFail($taskId);
        
        // Check if user is assigned to this task
        if ($task->assigned_to !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak di-assign ke task ini.'
            ], 403);
        }
        
        // Find running timer
        $timeLog = TimeLog::where('task_id', $taskId)
                          ->where('user_id', Auth::id())
                          ->whereNull('end_time')
                          ->latest('start_time')
                          ->first();
        
        if (!$timeLog) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada timer yang sedang berjalan untuk task ini.'
            ], 400);
        }
        
        try {
            // Stop the timer
            $timeLog->stopTimer();
            
            // Add notes if provided
            if ($request->notes) {
                $timeLog->notes = $request->notes;
                $timeLog->save();
            }
            
            // Calculate total time spent on task
            $totalTimeSpent = $task->timeLogs()->completed()->sum('duration_seconds');
            
            return response()->json([
                'success' => true,
                'message' => 'Timer dihentikan!',
                'timelog' => $timeLog->fresh(),
                'duration' => $timeLog->formatted_duration,
                'total_task_time' => $totalTimeSpent,
                'formatted_total_time' => $this->formatDuration($totalTimeSpent),
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to stop timer: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghentikan timer: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get current running timer status
     */
    public function status($taskId)
    {
        $task = Task::findOrFail($taskId);
        
        // Get running timer for this task by current user
        $timeLog = TimeLog::where('task_id', $taskId)
                          ->where('user_id', Auth::id())
                          ->whereNull('end_time')
                          ->latest('start_time')
                          ->first();
        
        if (!$timeLog) {
            return response()->json([
                'success' => true,
                'is_running' => false,
                'timelog' => null,
            ]);
        }
        
        return response()->json([
            'success' => true,
            'is_running' => true,
            'timelog' => $timeLog,
            'elapsed_seconds' => $timeLog->getElapsedSeconds(),
            'start_time' => $timeLog->start_time->toIso8601String(),
        ]);
    }
    
    /**
     * Get time logs history for a task
     */
    public function history($taskId)
    {
        $task = Task::findOrFail($taskId);
        
        // Only task owner or project leader can view history
        if ($task->assigned_to !== Auth::id() && !$this->isProjectLeader($task->project_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk melihat history ini.'
            ], 403);
        }
        
        $timeLogs = TimeLog::where('task_id', $taskId)
                           ->with('user:user_id,full_name')
                           ->completed()
                           ->orderBy('start_time', 'desc')
                           ->get();
        
        $totalSeconds = $timeLogs->sum('duration_seconds');
        
        return response()->json([
            'success' => true,
            'time_logs' => $timeLogs,
            'total_time_seconds' => $totalSeconds,
            'total_time_formatted' => $this->formatDuration($totalSeconds),
            'log_count' => $timeLogs->count(),
        ]);
    }
    
    /**
     * Helper: Format duration seconds to HH:MM:SS
     */
    private function formatDuration($seconds)
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;
        
        return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
    }
    
    /**
     * Helper: Check if current user is project leader
     */
    private function isProjectLeader($projectId)
    {
        return DB::table('project_members')
                 ->where('project_id', $projectId)
                 ->where('user_id', Auth::id())
                 ->where('role', 'leader')
                 ->exists();
    }
}
