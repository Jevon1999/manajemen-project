<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProjectCommentController extends Controller
{
    /**
     * Get all comments for a project
     */
    public function index($projectId)
    {
        $project = Project::findOrFail($projectId);
        
        // Enhanced permission check - allow all project members to comment
        $user = Auth::user();
        $hasAccess = false;
        
        // Admin always has access
        if ($user->role === 'admin') {
            $hasAccess = true;
        }
        // Project leader has access
        elseif ($project->leader_id === $user->user_id) {
            $hasAccess = true;
        }
        // Check if user is project member (developer, designer, project_manager)
        elseif ($project->members()->where('user_id', $user->user_id)->exists()) {
            $hasAccess = true;
        }
        
        if (!$hasAccess) {
            return response()->json(['error' => 'Unauthorized - Only project members can participate in project discussions'], 403);
        }

        $comments = DB::table('project_comments')
            ->join('users', 'project_comments.user_id', '=', 'users.user_id')
            ->where('project_comments.project_id', $projectId)
            ->select(
                'project_comments.*',
                'users.full_name',
                'users.username',
                'users.role'
            )
            ->orderBy('project_comments.created_at', 'desc')
            ->get()
            ->map(function($comment) {
                return [
                    'comment_id' => $comment->comment_id,
                    'comment' => $comment->comment,
                    'created_at' => $comment->created_at,
                    'created_at_human' => \Carbon\Carbon::parse($comment->created_at)->diffForHumans(),
                    'is_owner' => $comment->user_id === Auth::id(),
                    'user' => [
                        'name' => $comment->full_name ?? $comment->username,
                        'initials' => strtoupper(substr($comment->full_name ?? $comment->username, 0, 2)),
                        'role' => $comment->role
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
    public function store(Request $request, $projectId)
    {
        $request->validate([
            'comment' => 'required|string|max:1000'
        ]);

        $project = Project::findOrFail($projectId);

        // Enhanced permission check - allow all project members to comment
        $user = Auth::user();
        $hasAccess = false;
        
        // Admin always has access
        if ($user->role === 'admin') {
            $hasAccess = true;
        }
        // Project leader has access
        elseif ($project->leader_id === $user->user_id) {
            $hasAccess = true;
        }
        // Check if user is project member (developer, designer, project_manager)
        elseif ($project->members()->where('user_id', $user->user_id)->exists()) {
            $hasAccess = true;
        }
        
        if (!$hasAccess) {
            return response()->json(['error' => 'Unauthorized - Only project members can participate in project discussions'], 403);
        }

        $commentId = DB::table('project_comments')->insertGetId([
            'project_id' => $projectId,
            'user_id' => $user->user_id,
            'comment' => $request->comment,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $comment = DB::table('project_comments')
            ->join('users', 'project_comments.user_id', '=', 'users.user_id')
            ->where('project_comments.comment_id', $commentId)
            ->select(
                'project_comments.*',
                'users.full_name',
                'users.username',
                'users.role'
            )
            ->first();

        return response()->json([
            'success' => true,
            'comment' => [
                'comment_id' => $comment->comment_id,
                'comment' => $comment->comment,
                'created_at' => $comment->created_at,
                'created_at_human' => \Carbon\Carbon::parse($comment->created_at)->diffForHumans(),
                'is_owner' => true,
                'user' => [
                    'name' => $comment->full_name ?? $comment->username,
                    'initials' => strtoupper(substr($comment->full_name ?? $comment->username, 0, 2)),
                    'role' => $comment->role
                ]
            ]
        ]);
    }

    /**
     * Delete a comment
     */
    public function destroy($projectId, $commentId)
    {
        $comment = DB::table('project_comments')
            ->where('comment_id', $commentId)
            ->where('project_id', $projectId)
            ->first();

        if (!$comment) {
            return response()->json(['error' => 'Comment not found'], 404);
        }

        // Check permission: only comment owner or admin can delete
        if ($comment->user_id !== Auth::id() && Auth::user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        DB::table('project_comments')
            ->where('comment_id', $commentId)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Comment deleted successfully'
        ]);
    }
}
