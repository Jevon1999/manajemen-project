<?php

namespace App\Http\Controllers;

use App\Models\WorkSession;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class WorkSessionController extends Controller
{
    /**
     * Start a new work session
     * 
     * @param Request $request HTTP request
     * @return \Illuminate\Http\JsonResponse
     */
    public function startWork(Request $request)
    {
        /** @var Request $request */
        
        try {
            $user = Auth::user();
            // Use current date in Asia/Jakarta timezone
            $today = Carbon::now('Asia/Jakarta')->startOfDay();
            
            // Check if user already has an active session
            $activeSession = WorkSession::where('user_id', $user->user_id)
                ->where('status', 'active')
                ->first();
            
            if ($activeSession) {
                return response()->json([
                    'success' => false,
                    'message' => 'You already have an active work session',
                    'session' => $activeSession
                ], 400);
            }
            
            // Check today's total work time (8 hours = 28800 seconds limit)
            $todayTotal = WorkSession::where('user_id', $user->user_id)
                ->whereDate('work_date', $today)
                ->where('status', 'completed')
                ->sum('duration_seconds');
            
            if ($todayTotal >= 28800) { // 8 hours
                return response()->json([
                    'success' => false,
                    'message' => 'You have reached the daily work limit of 8 hours',
                    'today_total' => $todayTotal
                ], 400);
            }
            
            // Create new session
            $session = WorkSession::create([
                'user_id' => $user->user_id,
                'task_id' => $request->task_id,
                'started_at' => Carbon::now('Asia/Jakarta'),
                'work_date' => $today,
                'status' => 'active',
                'duration_seconds' => 0
            ]);
            
            // Update task status to in_progress if task_id is provided
            // Hanya ubah dari todo → in_progress (saat pertama kali start)
            // Jika sudah in_progress, biarkan (untuk resume setelah pause)
            if ($request->task_id) {
                $task = Task::find($request->task_id);
                if ($task && $task->assigned_to === $user->user_id) {
                    if ($task->status === 'todo') {
                        $task->status = 'in_progress';
                        $task->save();
                    }
                    // Jika sudah in_progress, tidak perlu diubah (resume work)
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Work session started',
                'session' => $session,
                'today_total' => $todayTotal
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to start work session: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Stop/pause current work session
     * 
     * @param Request $request HTTP request
     * @return \Illuminate\Http\JsonResponse
     */
    public function stopWork(Request $request)
    {
        /** @var Request $request */
        
        try {
            $request->validate([
                'session_id' => 'required|integer',
                'duration_seconds' => 'required|integer|min:0',
                'action' => 'required|in:pause,stop' // New: distinguish pause vs stop
            ]);
            
            $user = Auth::user();
            
            // Find the session
            $session = WorkSession::where('session_id', $request->session_id)
                ->where('user_id', $user->user_id)
                ->where('status', 'active')
                ->first();
                
            if (!$session) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active work session found. Please start a new session.',
                    'action' => 'reset' // Tell frontend to reset
                ], 404);
            }
            
            // Check for stale sessions (> 24 hours old)
            $sessionAge = Carbon::parse($session->started_at)->diffInHours(Carbon::now('Asia/Jakarta'));
            if ($sessionAge >= 24) {
                // Auto-stop stale session
                $session->stopped_at = Carbon::now('Asia/Jakarta');
                $session->duration_seconds = 0; // Invalid session
                $session->status = 'cancelled';
                $session->notes = 'Auto-cancelled: Session older than 24 hours (stale session)';
                $session->save();
                
                return response()->json([
                    'success' => false,
                    'message' => 'Your work session has expired (older than 24 hours). Please start a new session.',
                    'action' => 'reset',
                    'stale_session' => true
                ], 410); // 410 Gone
            }
            
            if ($request->action === 'pause') {
                // PAUSE: Keep session active but mark as paused
                $session->paused_at = Carbon::now('Asia/Jakarta');
                $session->duration_seconds = $request->duration_seconds;
                // Status tetap 'active' tapi ada paused_at
                $session->save();
                
                $message = 'Work session paused';
            } else {
                // STOP: Complete the session
                $session->stopped_at = Carbon::now('Asia/Jakarta');
                $session->duration_seconds = $request->duration_seconds;
                $session->status = 'completed';
                $session->paused_at = null; // Clear pause if any
                
                if ($request->has('notes')) {
                    $session->notes = $request->notes;
                }
                
                $session->save();
                
                $message = 'Work session completed';
            }
            
            // CATATAN: Task TIDAK diubah kembali ke todo saat stop work
            // Task tetap in_progress sampai user klik "Selesaikan Task" (review)
            // Ini memungkinkan user pause/resume work tanpa mengubah status task
            
            // Get today's total after update
            $today = Carbon::now('Asia/Jakarta')->startOfDay();
            $todayTotal = WorkSession::where('user_id', $user->user_id)
                ->whereDate('work_date', $today)
                ->where('status', 'completed')
                ->sum('duration_seconds');
            
            return response()->json([
                'success' => true,
                'message' => $message,
                'session' => $session,
                'today_total' => $todayTotal,
                'formatted_duration' => $session->formatted_duration,
                'action' => $request->action
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to stop work session: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Resume paused work session
     * 
     * @param Request $request HTTP request
     * @return \Illuminate\Http\JsonResponse
     */
    public function resumeWork(Request $request)
    {
        /** @var Request $request */
        
        try {
            $user = Auth::user();
            
            // Find paused session
            $session = WorkSession::where('user_id', $user->user_id)
                ->where('status', 'active')
                ->whereNotNull('paused_at')
                ->first();
            
            if (!$session) {
                return response()->json([
                    'success' => false,
                    'message' => 'No paused session found'
                ], 404);
            }
            
            // Check for stale sessions (> 24 hours old)
            $sessionAge = Carbon::parse($session->started_at)->diffInHours(Carbon::now('Asia/Jakarta'));
            if ($sessionAge >= 24) {
                // Auto-stop stale session
                $session->stopped_at = Carbon::now('Asia/Jakarta');
                $session->duration_seconds = 0;
                $session->status = 'cancelled';
                $session->notes = 'Auto-cancelled: Session older than 24 hours (stale session)';
                $session->save();
                
                return response()->json([
                    'success' => false,
                    'message' => 'Your work session has expired (older than 24 hours). Please start a new session.',
                    'action' => 'reset',
                    'stale_session' => true
                ], 410); // 410 Gone
            }
            
            // Calculate pause duration
            $pauseDuration = Carbon::parse($session->paused_at)->diffInSeconds(Carbon::now('Asia/Jakarta'));
            
            // Update session
            $session->pause_duration += $pauseDuration;
            $session->paused_at = null; // Clear pause flag
            $session->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Work session resumed',
                'session' => $session,
                'pause_duration' => $pauseDuration
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to resume work session: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get active work session for current user
     */
    public function getActiveSession()
    {
        try {
            $user = Auth::user();
            
            $session = WorkSession::where('user_id', $user->user_id)
                ->where('status', 'active')
                ->with('task')
                ->first();
            
            if ($session) {
                // Check for stale sessions (> 24 hours old)
                $sessionAge = Carbon::parse($session->started_at)->diffInHours(Carbon::now('Asia/Jakarta'));
                if ($sessionAge >= 24) {
                    // Auto-cancel stale session
                    $session->stopped_at = Carbon::now('Asia/Jakarta');
                    $session->duration_seconds = 0;
                    $session->status = 'cancelled';
                    $session->notes = 'Auto-cancelled: Session older than 24 hours (stale session)';
                    $session->save();
                    
                    return response()->json([
                        'success' => true,
                        'session' => null,
                        'stale_session_cancelled' => true,
                        'message' => 'Previous session was cancelled due to being older than 24 hours'
                    ]);
                }
            }
            
            return response()->json([
                'success' => true,
                'session' => $session
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get active session: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get today's total work time
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTodayTotal()
    {
        /** @var \App\Models\User $user */
        
        try {
            $user = Auth::user();
            // Use current date in Asia/Jakarta timezone
            $today = Carbon::now('Asia/Jakarta')->startOfDay();
            
            // Get today's total (completed sessions only)
            $todayTotal = WorkSession::where('user_id', $user->user_id)
                ->whereDate('work_date', $today)
                ->where('status', 'completed')
                ->sum('duration_seconds');
            
            // If there's an active session, add its elapsed time
            $activeSession = WorkSession::where('user_id', $user->user_id)
                ->where('status', 'active')
                ->first();
            
            if ($activeSession) {
                $elapsedSeconds = Carbon::parse($activeSession->started_at)
                    ->diffInSeconds(Carbon::now('Asia/Jakarta'));
                $todayTotal += $elapsedSeconds;
            }
            
            // Format as HH:MM:SS
            $hours = floor($todayTotal / 3600);
            $minutes = floor(($todayTotal % 3600) / 60);
            $seconds = $todayTotal % 60;
            $formatted = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
            
            // Calculate remaining time (8 hours = 28800 seconds)
            $remaining = max(0, 28800 - $todayTotal);
            $remainingHours = floor($remaining / 3600);
            $remainingMinutes = floor(($remaining % 3600) / 60);
            $formattedRemaining = sprintf('%02d:%02d', $remainingHours, $remainingMinutes);
            
            return response()->json([
                'success' => true,
                'today_total' => $todayTotal,
                'formatted' => $formatted,
                'remaining_seconds' => $remaining,
                'formatted_remaining' => $formattedRemaining,
                'limit_reached' => $todayTotal >= 28800,
                'work_date' => $today->toDateString(),
                'current_time' => Carbon::now('Asia/Jakarta')->toDateTimeString()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get today\'s total: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get work history for current user
     * 
     * @param Request $request HTTP request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getHistory(Request $request)
    {
        /** @var Request $request */
        /** @var \App\Models\User $user */
        
        try {
            $user = Auth::user();
            $days = $request->input('days', 7); // Default last 7 days
            $today = Carbon::now('Asia/Jakarta')->startOfDay();
            
            $sessions = WorkSession::where('user_id', $user->user_id)
                ->where('work_date', '>=', $today->copy()->subDays($days))
                ->with('task')
                ->orderBy('started_at', 'desc')
                ->get();
            
            // Group by date
            $grouped = $sessions->groupBy(function($session) {
                return $session->work_date->format('Y-m-d');
            })->map(function($daySessions) {
                return [
                    'total_seconds' => $daySessions->sum('duration_seconds'),
                    'sessions' => $daySessions
                ];
            });
            
            return response()->json([
                'success' => true,
                'history' => $grouped,
                'total_sessions' => $sessions->count()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get work history: ' . $e->getMessage()
            ], 500);
        }
    }
}
