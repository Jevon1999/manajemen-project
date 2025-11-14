<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Card;
use App\Models\TaskComment;
use App\Models\TaskAttachment;
use App\Models\TimeLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DeveloperTaskController extends Controller
{
    /**
     * Display tasks for developer/designer
     */
    public function index()
    {
        $user = Auth::user();
        $userRole = $user->role;

        // Get tasks assigned to this user
        $myTasks = Card::with(['board.project', 'creator', 'assignments.user', 'timeLogs', 'activeTimeLog'])
            ->assignedTo($user->user_id)
            ->orderBy('priority', 'desc')
            ->orderBy('due_date', 'asc')
            ->get();

        // Check if user has active task
        $hasActiveTask = Card::userHasActiveTask($user->user_id);

        // Get statistics
        $stats = [
            'total' => $myTasks->count(),
            'in_progress' => $myTasks->where('status', 'in_progress')->count(),
            'todo' => $myTasks->where('status', 'todo')->count(),
            'review' => $myTasks->where('status', 'review')->count(),
            'completed' => $myTasks->where('status', 'done')->count(),
        ];

        // Calculate total time logged
        $totalTimeLogged = TimeLog::whereIn('task_id', $myTasks->pluck('task_id'))
            ->where('user_id', $user->user_id)
            ->whereNotNull('duration_seconds')
            ->sum('duration_seconds') / 60; // Convert to minutes

        return view('developer.tasks.index', compact('myTasks', 'hasActiveTask', 'stats', 'totalTimeLogged', 'userRole'));
    }

    /**
     * Get task detail with comments and attachments
     */
    public function show($id)
    {
        $user = Auth::user();
        
        $task = Card::with([
            'board.project',
            'creator',
            'assignments.user',
            'taskComments.user',
            'taskComments.replies.user',
            'attachments.user',
            'timeLogs' => function($query) use ($user) {
                $query->where('user_id', $user->user_id)
                    ->orderBy('start_time', 'desc');
            },
            'activeTimeLog'
        ])
        ->assignedTo($user->user_id)
        ->findOrFail($id);

        // Calculate time spent
        $timeSpent = $task->timeLogs->where('user_id', $user->user_id)->sum('duration_seconds') / 60; // Convert to minutes

        return response()->json([
            'success' => true,
            'task' => $task,
            'timeSpent' => $timeSpent,
            'isTimerRunning' => $task->activeTimeLog && $task->activeTimeLog->user_id == $user->user_id
        ]);
    }

    /**
     * Update task progress
     */
    public function updateProgress(Request $request, $id)
    {
        $user = Auth::user();
        
        $request->validate([
            'status' => 'required|in:todo,in_progress,review,done',
            'description' => 'nullable|string|max:1000'
        ]);

        $task = Card::assignedTo($user->user_id)->findOrFail($id);

        // Check if user has another active task (only for in_progress status)
        if ($request->status === 'in_progress') {
            $hasOtherActiveTask = Card::assignedTo($user->user_id)
                ->where('card_id', '!=', $id)
                ->where('status', 'in_progress')
                ->exists();

            if ($hasOtherActiveTask) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda sudah memiliki tugas aktif lain. Selesaikan dulu tugas tersebut.'
                ], 422);
            }
        }

        $oldStatus = $task->status;
        $task->status = $request->status;
        $task->save();

        // Add system comment
        TaskComment::create([
            'card_id' => $task->card_id,
            'user_id' => $user->user_id,
            'comment' => $request->description ?? "Status diubah dari '{$oldStatus}' ke '{$request->status}'",
            'type' => 'system'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Progress berhasil diupdate!',
            'task' => $task->fresh(['activeTimeLog'])
        ]);
    }

    /**
     * Add comment to task
     */
    public function addComment(Request $request, $id)
    {
        $user = Auth::user();

        $request->validate([
            'comment' => 'required|string|max:2000',
            'parent_id' => 'nullable|exists:task_comments,comment_id'
        ]);

        $task = Card::assignedTo($user->user_id)->findOrFail($id);

        $comment = TaskComment::create([
            'card_id' => $task->card_id,
            'user_id' => $user->user_id,
            'comment' => $request->comment,
            'type' => 'text',
            'parent_id' => $request->parent_id
        ]);

        $comment->load('user', 'replies.user');

        return response()->json([
            'success' => true,
            'message' => 'Komentar berhasil ditambahkan!',
            'comment' => $comment
        ]);
    }

    /**
     * Upload file attachment
     */
    public function uploadFile(Request $request, $id)
    {
        $user = Auth::user();

        $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
            'description' => 'nullable|string|max:500'
        ]);

        $task = Card::assignedTo($user->user_id)->findOrFail($id);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $filename = time() . '_' . $originalName;
        $mimeType = $file->getMimeType();
        
        // Determine file type
        $fileType = 'document';
        if (str_starts_with($mimeType, 'image/')) {
            $fileType = 'image';
        } elseif (str_starts_with($mimeType, 'video/')) {
            $fileType = 'video';
        } elseif (in_array($file->getClientOriginalExtension(), ['fig', 'sketch', 'xd', 'ai', 'psd'])) {
            $fileType = 'design';
        } elseif (in_array($file->getClientOriginalExtension(), ['php', 'js', 'py', 'java', 'cpp', 'html', 'css'])) {
            $fileType = 'code';
        }

        // Store file
        $filePath = $file->storeAs('task-attachments/' . $task->card_id, $filename, 'public');

        $attachment = TaskAttachment::create([
            'card_id' => $task->card_id,
            'user_id' => $user->user_id,
            'filename' => $filename,
            'original_filename' => $originalName,
            'file_path' => $filePath,
            'file_type' => $fileType,
            'mime_type' => $mimeType,
            'file_size' => $file->getSize(),
            'description' => $request->description
        ]);

        $attachment->load('user');

        return response()->json([
            'success' => true,
            'message' => 'File berhasil diupload!',
            'attachment' => $attachment
        ]);
    }

    /**
     * Start time tracking
     */
    public function startTimer(Request $request, $id)
    {
        $user = Auth::user();
        $task = Card::assignedTo($user->user_id)->findOrFail($id);

        // Check if there's already a running timer
        $runningTimer = TimeLog::where('user_id', $user->user_id)
            ->whereNull('end_time')
            ->first();

        if ($runningTimer) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah memiliki timer yang berjalan. Stop timer tersebut terlebih dahulu.'
            ], 422);
        }

        // Auto set status to in_progress if still todo
        if ($task->status === 'todo') {
            $task->status = 'in_progress';
            $task->save();
        }

        $timeLog = TimeLog::create([
            'card_id' => $task->card_id,
            'user_id' => $user->user_id,
            'start_time' => Carbon::now(),
            'description' => $request->description ?? 'Working on task'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Timer dimulai!',
            'timeLog' => $timeLog
        ]);
    }

    /**
     * Stop time tracking
     */
    public function stopTimer(Request $request, $id)
    {
        $user = Auth::user();
        $task = Card::assignedTo($user->user_id)->findOrFail($id);

        $timeLog = TimeLog::where('card_id', $task->card_id)
            ->where('user_id', $user->user_id)
            ->whereNull('end_time')
            ->latest()
            ->first();

        if (!$timeLog) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada timer yang berjalan.'
            ], 422);
        }

        $endTime = Carbon::now();
        $durationMinutes = $endTime->diffInMinutes($timeLog->start_time);

        $timeLog->update([
            'end_time' => $endTime,
            'duration_minutes' => $durationMinutes,
            'description' => $request->description ?? $timeLog->description
        ]);

        // Update actual hours on card
        $totalMinutes = TimeLog::where('card_id', $task->card_id)
            ->where('user_id', $user->user_id)
            ->sum('duration_minutes');
        
        $task->actual_hours = round($totalMinutes / 60, 2);
        $task->save();

        return response()->json([
            'success' => true,
            'message' => 'Timer dihentikan! Durasi: ' . $durationMinutes . ' menit',
            'timeLog' => $timeLog,
            'totalHours' => $task->actual_hours
        ]);
    }

    /**
     * Delete attachment (owner only)
     */
    public function deleteAttachment($taskId, $attachmentId)
    {
        $user = Auth::user();
        
        $attachment = TaskAttachment::where('card_id', $taskId)
            ->where('attachment_id', $attachmentId)
            ->where('user_id', $user->user_id) // Only owner can delete
            ->firstOrFail();

        // Delete file from storage
        Storage::disk('public')->delete($attachment->file_path);

        $attachment->delete();

        return response()->json([
            'success' => true,
            'message' => 'File berhasil dihapus!'
        ]);
    }
}
