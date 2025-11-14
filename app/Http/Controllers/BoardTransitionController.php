<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Services\BoardTransitionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BoardTransitionController extends Controller
{
    protected $boardService;
    
    public function __construct(BoardTransitionService $boardService)
    {
        $this->boardService = $boardService;
    }
    
    /**
     * Mark task as complete (transition to review)
     * User sends task for leader approval
     */
    public function markComplete(Request $request, $taskId)
    {
        $task = Task::findOrFail($taskId);
        
        $result = $this->boardService->transitionToReview($task, Auth::id());
        
        if ($result['success']) {
            return response()->json($result);
        }
        
        return response()->json($result, 400);
    }
    
    /**
     * Approve task (transition to done)
     * Leader approves the completed task
     */
    public function approve(Request $request, $taskId)
    {
        $task = Task::findOrFail($taskId);
        
        $result = $this->boardService->transitionToDone($task, Auth::id());
        
        if ($result['success']) {
            return response()->json($result);
        }
        
        return response()->json($result, 403);
    }
    
    /**
     * Reject task (send back to in_progress)
     * Leader rejects and sends back for revision
     */
    public function reject(Request $request, $taskId)
    {
        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);
        
        $task = Task::findOrFail($taskId);
        
        $result = $this->boardService->rejectTask(
            $task,
            Auth::id(),
            $request->reason
        );
        
        if ($result['success']) {
            return response()->json($result);
        }
        
        return response()->json($result, 403);
    }
    
    /**
     * Manual status change
     * Admin/Leader can manually change status if needed
     */
    public function changeStatus(Request $request, $taskId)
    {
        $request->validate([
            'status' => 'required|in:todo,in_progress,review,done',
        ]);
        
        $task = Task::findOrFail($taskId);
        
        $result = $this->boardService->changeStatus(
            $task,
            $request->status,
            Auth::id()
        );
        
        if ($result['success']) {
            return response()->json($result);
        }
        
        return response()->json($result, 403);
    }
    
    /**
     * Get available transitions for current user
     */
    public function getAvailableTransitions($taskId)
    {
        $task = Task::findOrFail($taskId);
        
        $transitions = $this->boardService->getAvailableTransitions($task, Auth::id());
        
        return response()->json([
            'success' => true,
            'current_status' => $task->status,
            'transitions' => $transitions,
        ]);
    }
}
