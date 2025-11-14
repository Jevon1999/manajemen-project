<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CommentController extends Controller
{
    /**
     * Get all comments for a task
     */
    public function index($taskId)
    {
        try {
            $task = Task::findOrFail($taskId);
            
            // Check if user has access to this task (project member or admin)
            if (!$this->canAccessTask($task)) {
                return response()->json(['error' => 'Akses ditolak'], 403);
            }
            
            $comments = Comment::where('task_id', $taskId)
                ->with('user:user_id,name,email')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($comment) {
                    return [
                        'comment_id' => $comment->comment_id,
                        'comment' => $comment->comment,
                        'user' => [
                            'user_id' => $comment->user->user_id,
                            'name' => $comment->user->name,
                            'email' => $comment->user->email,
                            'initials' => $this->getInitials($comment->user->name),
                        ],
                        'created_at' => $comment->created_at->format('d M Y H:i'),
                        'created_at_human' => $comment->created_at->diffForHumans(),
                        'is_owner' => $comment->user_id === Auth::id(),
                    ];
                });
            
            return response()->json([
                'success' => true,
                'comments' => $comments,
                'total' => $comments->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching comments', [
                'task_id' => $taskId,
                'error' => $e->getMessage()
            ]);
            
            return response()->json(['error' => 'Gagal mengambil komentar'], 500);
        }
    }
    
    /**
     * Store a new comment
     */
    public function store(Request $request, $taskId)
    {
        try {
            $task = Task::findOrFail($taskId);
            
            // Check if user has access to this task
            if (!$this->canAccessTask($task)) {
                return response()->json(['error' => 'Akses ditolak'], 403);
            }
            
            // Validate request
            $validated = $request->validate([
                'comment' => 'required|string|max:5000',
            ]);
            
            // Create comment
            $comment = Comment::create([
                'task_id' => $taskId,
                'user_id' => Auth::id(),
                'comment' => $validated['comment'],
            ]);
            
            // Load user relationship
            $comment->load('user:user_id,name,email');
            
            Log::info('Comment created', [
                'comment_id' => $comment->comment_id,
                'task_id' => $taskId,
                'user_id' => Auth::id()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Komentar berhasil ditambahkan',
                'comment' => [
                    'comment_id' => $comment->comment_id,
                    'comment' => $comment->comment,
                    'user' => [
                        'user_id' => $comment->user->user_id,
                        'name' => $comment->user->name,
                        'email' => $comment->user->email,
                        'initials' => $this->getInitials($comment->user->name),
                    ],
                    'created_at' => $comment->created_at->format('d M Y H:i'),
                    'created_at_human' => $comment->created_at->diffForHumans(),
                    'is_owner' => true,
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validasi gagal',
                'messages' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error creating comment', [
                'task_id' => $taskId,
                'error' => $e->getMessage()
            ]);
            
            return response()->json(['error' => 'Gagal menambahkan komentar'], 500);
        }
    }
    
    /**
     * Delete a comment
     */
    public function destroy($taskId, $commentId)
    {
        try {
            $comment = Comment::where('comment_id', $commentId)
                ->where('task_id', $taskId)
                ->firstOrFail();
            
            // Check if user owns this comment or is admin
            if ($comment->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
                return response()->json(['error' => 'Anda tidak memiliki izin untuk menghapus komentar ini'], 403);
            }
            
            $comment->delete();
            
            Log::info('Comment deleted', [
                'comment_id' => $commentId,
                'task_id' => $taskId,
                'user_id' => Auth::id()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Komentar berhasil dihapus',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Komentar tidak ditemukan'], 404);
        } catch (\Exception $e) {
            Log::error('Error deleting comment', [
                'comment_id' => $commentId,
                'task_id' => $taskId,
                'error' => $e->getMessage()
            ]);
            
            return response()->json(['error' => 'Gagal menghapus komentar'], 500);
        }
    }
    
    /**
     * Check if user can access task (is project member or admin)
     */
    private function canAccessTask($task)
    {
        $user = Auth::user();
        
        // Admin can access all tasks
        if ($user->isAdmin()) {
            return true;
        }
        
        // Check if user is project member
        return $task->project->members()
            ->where('user_id', $user->user_id)
            ->exists();
    }
    
    /**
     * Get user initials for avatar
     */
    private function getInitials($name)
    {
        $words = explode(' ', $name);
        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
        }
        return strtoupper(substr($name, 0, 2));
    }
}
