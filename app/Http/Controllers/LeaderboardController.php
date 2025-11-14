<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Task;
use App\Models\WorkSession;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class LeaderboardController extends Controller
{
    /**
     * Display the leaderboard page
     */
    public function index(Request $request)
    {
        $month = $request->get('month', now()->format('Y-m'));
        
        $leaderboard = $this->calculateLeaderboard($month);
        $myStats = null;
        
        if (Auth::check()) {
            $myStats = $this->myStats($month);
        }
        
        return view('leaderboard.index', compact('leaderboard', 'myStats', 'month'));
    }

    /**
     * Widget API for dashboard - Top 5 users (cached for 5 minutes)
     */
    public function widget(Request $request)
    {
        $month = $request->get('month', now()->format('Y-m'));
        
        // Cache key based on month
        $cacheKey = "leaderboard_top5_{$month}";
        
        // Cache for 5 minutes (300 seconds)
        $leaderboard = Cache::remember($cacheKey, 300, function() use ($month) {
            return $this->calculateLeaderboard($month, 5);
        });
        
        return response()->json([
            'success' => true,
            'leaderboard' => $leaderboard,
            'month' => $month,
        ]);
    }

    /**
     * Calculate leaderboard with real-time scoring
     * 
     * Scoring System:
     * - Task Completed: 50 points
     * - Priority Bonus: High +20, Medium +10, Low +5
     * - Work Hours: 2 points per hour
     * - On-Time Completion: +25 bonus
     * 
     * @param string|null $month Month in YYYY-MM format
     * @param int|null $limit Limit number of results
     * @return array Leaderboard data
     */
    public function calculateLeaderboard($month = null, $limit = null)
    {
        /** @var string|null $month */
        /** @var int|null $limit */
        
        // Parse month parameter (format: YYYY-MM)
        $month = $month ?? now()->format('Y-m');
        $startDate = Carbon::parse($month . '-01')->startOfMonth();
        $endDate = Carbon::parse($month . '-01')->endOfMonth();
        
        // OPTIMIZED: Single query to get all users with their tasks and work sessions
        $users = User::where('role', 'user')
            ->with([
                'assignedTasks' => function($query) use ($startDate, $endDate) {
                    $query->where('status', Task::STATUS_DONE)
                          ->whereBetween('completed_at', [$startDate, $endDate]);
                }
            ])
            ->get();
        
        // OPTIMIZED: Get all work sessions in one query
        $workSessions = WorkSession::whereIn('user_id', $users->pluck('user_id'))
            ->whereBetween('work_date', [$startDate, $endDate])
            ->select('user_id', DB::raw('SUM(duration_seconds) as total_seconds'))
            ->groupBy('user_id')
            ->pluck('total_seconds', 'user_id');
        
        $leaderboardData = [];
        
        foreach ($users as $user) {
            $tasks = $user->assignedTasks;
            $tasksCompleted = $tasks->count();
            
            // Calculate task points (50 per task)
            $taskPoints = $tasksCompleted * 50;
            
            // Calculate priority bonus
            $priorityBonus = $tasks->sum(function($task) {
                return match($task->priority) {
                    'high' => 20,
                    'medium' => 10,
                    'low' => 5,
                    default => 0,
                };
            });
            
            // Calculate on-time bonus
            $onTimeBonus = $tasks->filter(function($task) {
                return $task->completed_at && $task->deadline && 
                       Carbon::parse($task->completed_at)->lte(Carbon::parse($task->deadline));
            })->count() * 25;
            
            // Get work hours from pre-fetched data
            $totalWorkSeconds = $workSessions[$user->user_id] ?? 0;
            $totalWorkHours = floor($totalWorkSeconds / 3600);
            $workPoints = $totalWorkHours * 2;
            
            // Total score
            $totalScore = $taskPoints + $priorityBonus + $workPoints + $onTimeBonus;
            
            $leaderboardData[] = [
                'user_id' => $user->user_id,
                'name' => $user->display_name,
                'username' => $user->username,
                'role' => $user->role,
                'specialty' => $user->specialty,
                'tasks_completed' => $tasksCompleted,
                'work_hours' => $totalWorkHours,
                'total_score' => $totalScore,
                'task_points' => $taskPoints,
                'priority_bonus' => $priorityBonus,
                'work_points' => $workPoints,
                'on_time_bonus' => $onTimeBonus,
            ];
        }
        
        // Filter out users with no activity (optional - comment out if you want to show all users)
        // $leaderboardData = array_filter($leaderboardData, function($data) {
        //     return $data['total_score'] > 0;
        // });
        
        // Sort by total_score descending
        usort($leaderboardData, function($a, $b) {
            return $b['total_score'] - $a['total_score'];
        });
        
        // Add rank
        foreach ($leaderboardData as $index => &$data) {
            $data['rank'] = $index + 1;
        }
        
        // Apply limit if specified (for widget)
        if ($limit) {
            $leaderboardData = array_slice($leaderboardData, 0, $limit);
        }
        
        return $leaderboardData;
    }

    /**
     * Get current user's stats and rank
     * 
     * @param string|null $month Month in YYYY-MM format
     * @return array|null User stats or null if not authenticated
     */
    public function myStats($month = null)
    {
        /** @var string|null $month */
        
        if (!Auth::check()) {
            return null;
        }
        
        $month = $month ?? now()->format('Y-m');
        $leaderboard = $this->calculateLeaderboard($month);
        
        // Find current user in leaderboard
        $userId = Auth::id();
        $userStats = collect($leaderboard)->firstWhere('user_id', $userId);
        
        if (!$userStats) {
            // User has no activity this month
            return [
                'rank' => null,
                'total_users' => count($leaderboard),
                'tasks_completed' => 0,
                'work_hours' => 0,
                'total_score' => 0,
            ];
        }
        
        $userStats['total_users'] = count($leaderboard);
        
        return $userStats;
    }

    /**
     * Format duration for display
     * 
     * @param int $seconds Duration in seconds
     * @return string Formatted duration string
     */
    private function formatDuration($seconds)
    {
        /** @var int $seconds */
        
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        
        if ($hours > 0) {
            return "{$hours}h {$minutes}m";
        }
        return "{$minutes}m";
    }
}
