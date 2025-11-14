<?php

namespace App\Http\Controllers;

use App\Models\TaskComment;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskCommentController extends Controller
{
    /**
     * Get all comments for a task
     */
    public function index($taskId)
    {
        $task = Task::findOrFail($taskId);
        
        $comments = $task->comments()
            ->with('user')
            ->get()
            ->map(function($comment) {
                return [
                    'comment_id' => $comment->comment_id,
                    'comment' => $comment->comment,
                    'created_at' => $comment->created_at->format('Y-m-d H:i:s'),
                    'created_at_human' => $comment->created_at->diffForHumans(),
                    'is_owner' => $comment->user_id === Auth::id(),
                    'user' => [
                        'name' => $comment->user->full_name ?? $comment->user->username,
                        'initials' => strtoupper(substr($comment->user->full_name ?? $comment->user->username, 0, 2))
                    ]
                ];
            });

        return response()->json([
            'success' => true,
            'comments' => $comments,
            'total' => $comments->count()
        ]);
    }

    /**
     * Store a new comment
     */
    public function store(Request $request, $taskId)
    {
        $request->validate([
            'comment' => 'required|string|max:1000'
        ]);

        $task = Task::findOrFail($taskId);

        // Check permission: only task members can comment
        $user = Auth::user();
        $isMember = $task->project->members()
            ->where('user_id', $user->user_id)
            ->exists();

        if (!$isMember) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $comment = TaskComment::create([
            'task_id' => $taskId,
            'user_id' => $user->user_id,
            'comment' => $request->comment
        ]);

        // Load user relationship
        $comment->load('user');

        return response()->json([
            'success' => true,
            'comment' => [
                'comment_id' => $comment->comment_id,
                'comment' => $comment->comment,
                'created_at' => $comment->created_at->format('Y-m-d H:i:s'),
                'created_at_human' => $comment->created_at->diffForHumans(),
                'is_owner' => true,
                'user' => [
                    'name' => $comment->user->full_name ?? $comment->user->username,
                    'initials' => strtoupper(substr($comment->user->full_name ?? $comment->user->username, 0, 2))
                ]
            ]
        ]);
    }

    /**
     * Delete a comment
     */
    public function destroy($taskId, $commentId)
    {
        $comment = TaskComment::findOrFail($commentId);

        // Check permission: only comment owner can delete
        if ($comment->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $comment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Comment deleted successfully'
        ]);
    }
}
