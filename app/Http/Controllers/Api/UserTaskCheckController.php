<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Card;
use Illuminate\Http\Request;

class UserTaskCheckController extends Controller
{
    /**
     * Get user's active task count
     *
     * @param int $userId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getActiveTasksCount($userId)
    {
        try {
            $user = User::findOrFail($userId);
            
            // Count active tasks (not done)
            $activeTasksCount = Card::where('assigned_to', $userId)
                ->whereIn('status', ['todo', 'in_progress', 'review'])
                ->count();
                
            $isAvailable = $activeTasksCount === 0;
            
            return response()->json([
                'success' => true,
                'data' => [
                    'user_id' => $userId,
                    'full_name' => $user->full_name,
                    'email' => $user->email,
                    'active_tasks_count' => $activeTasksCount,
                    'is_available' => $isAvailable,
                    'status_text' => $isAvailable ? 'Available' : "Has {$activeTasksCount} active task(s)"
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }
    }
}