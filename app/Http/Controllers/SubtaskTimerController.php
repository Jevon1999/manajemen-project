<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Subtask;
use App\Models\TimeLog;
use App\Models\ProjectMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SubtaskTimerController extends Controller
{
    /**
     * Start timer for subtask
     */
    public function startTimer(Request $request, $taskId, $subtaskId)
    {
        try {
            $task = Task::findOrFail($taskId);
            $subtask = Subtask::findOrFail($subtaskId);
            $user = Auth::user();

            // Security checks
            if (!$this->canWorkOnSubtask($task, $subtask, $user)) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to work on this subtask'
                ], 403);
            }

            // Check if user already has an active timer
            $existingTimer = TimeLog::where('user_id', $user->user_id)
                ->whereNull('end_time')
                ->first();

            if ($existingTimer) {
                return response()->json([
                    'success' => false,
                    'error' => 'You already have an active timer running. Stop it first before starting a new one.'
                ], 400);
            }

            // Create new timer
            $timeLog = TimeLog::create([
                'task_id' => $task->task_id,
                'user_id' => $user->user_id,
                'start_time' => now(),
                'notes' => "Working on subtask: {$subtask->title}",
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Timer started successfully',
                'timer_id' => $timeLog->timelog_id,
                'start_time' => $timeLog->start_time->toISOString()
            ]);

        } catch (\Exception $e) {
            Log::error('Error starting subtask timer: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to start timer. Please try again.'
            ], 500);
        }
    }

    /**
     * Stop timer for subtask
     */
    public function stopTimer(Request $request, $taskId, $subtaskId)
    {
        try {
            $task = Task::findOrFail($taskId);
            $subtask = Subtask::findOrFail($subtaskId);
            $user = Auth::user();

            // Security checks
            if (!$this->canWorkOnSubtask($task, $subtask, $user)) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to work on this subtask'
                ], 403);
            }

            // Find active timer
            $activeTimer = TimeLog::where('task_id', $task->task_id)
                ->where('user_id', $user->user_id)
                ->whereNull('end_time')
                ->first();

            if (!$activeTimer) {
                return response()->json([
                    'success' => false,
                    'error' => 'No active timer found for this task'
                ], 400);
            }

            // Stop the timer
            $activeTimer->update([
                'end_time' => now(),
                'duration_seconds' => now()->diffInSeconds($activeTimer->start_time),
                'notes' => ($activeTimer->notes ?: '') . " | Completed work on subtask: {$subtask->title}"
            ]);

            // Calculate time spent
            $timeSpentMinutes = round($activeTimer->duration_seconds / 60, 1);
            $timeSpentFormatted = $this->formatDuration($activeTimer->duration_seconds);

            return response()->json([
                'success' => true,
                'message' => 'Timer stopped successfully',
                'duration_seconds' => $activeTimer->duration_seconds,
                'timeSpent' => $timeSpentFormatted,
                'timeSpentMinutes' => $timeSpentMinutes
            ]);

        } catch (\Exception $e) {
            Log::error('Error stopping subtask timer: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to stop timer. Please try again.'
            ], 500);
        }
    }

    /**
     * Check if user can work on this subtask
     */
    private function canWorkOnSubtask(Task $task, Subtask $subtask, $user)
    {
        // Check if subtask belongs to task
        if ($subtask->task_id !== $task->task_id) {
            return false;
        }

        // Check if user is assigned to this task
        if ($task->assigned_to !== $user->user_id) {
            return false;
        }

        // Check if user is a project member with designer/developer role
        $projectMember = ProjectMember::where('project_id', $task->project_id)
            ->where('user_id', $user->user_id)
            ->whereIn('role', ['designer', 'developer'])
            ->first();

        if (!$projectMember) {
            return false;
        }

        // Check if user role is 'user' (not admin/leader)
        if (!in_array($user->role, ['user'])) {
            return false;
        }

        return true;
    }

    /**
     * Format duration in seconds to human readable format
     */
    private function formatDuration($seconds)
    {
        if ($seconds < 60) {
            return $seconds . 's';
        } elseif ($seconds < 3600) {
            $minutes = round($seconds / 60, 1);
            return $minutes . 'm';
        } else {
            $hours = floor($seconds / 3600);
            $minutes = round(($seconds % 3600) / 60);
            return $hours . 'h ' . $minutes . 'm';
        }
    }
}